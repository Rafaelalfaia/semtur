<?php

namespace App\Http\Controllers\Coordenador;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;


class TecnicoController extends Controller
{
    public function index(Request $r)
    {
        $q    = trim((string) $r->input('q',''));
        $buscaAtiva = mb_strlen($q) >= 3;
        $like = \DB::getDriverName()==='pgsql' ? 'ilike' : 'like';

        $users = User::query()
            ->onlyMyTecnicos()
            ->when($buscaAtiva, function($qq) use ($q,$like){
                $d = preg_replace('/\D+/', '', $q);
                $qq->where(function($w) use ($q,$d,$like){
                    $w->where('name',$like,"%{$q}%")
                    ->orWhere('email',$like,"%{$q}%");
                    if ($d) $w->orWhere('cpf',$like,"%{$d}%");
                });
            }, fn($qq) => $qq->whereRaw('1 = 0'))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $permissions = $this->groupedPermissionsForForm();

        return view('coordenador.tecnicos.index', compact('users','permissions','q'));
    }

    public function create()
    {
        $permissions = $this->groupedPermissionsForForm();

        return view('coordenador.tecnicos.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'email' => $request->filled('email') ? strtolower($request->email) : null,
            'cpf'   => preg_replace('/\D+/', '', (string)$request->input('cpf')) ?: null,
        ]);

        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['nullable','email','max:255','unique:users,email'],
            'cpf'      => ['nullable','digits:11','unique:users,cpf'],
            'password' => ['required','string','min:8','confirmed'],
            'perms'    => ['array'],
            'perms.*'  => ['string', Rule::in($this->offerablePermissionNames())],
        ], self::messages(), self::attributes());

        $u = User::create([
            'name'           => $data['name'],
            'email'          => $data['email'] ?? null,
            'cpf'            => $data['cpf'] ?? null,
            'password'       => Hash::make($data['password']),
            'coordenador_id' => auth()->id(),
        ]);

        $u->syncRoles(['Tecnico']);

        $allowed = $this->offerablePermissionNames();
        $requested = (array) ($data['perms'] ?? []);
        $toGrant = array_values(array_intersect($requested, $allowed));

        $u->syncPermissions($toGrant);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('coordenador.tecnicos.index')->with('ok','Técnico criado.');
    }

    public function edit(User $user)
    {
        $this->assertMine($user);

        $permissions = $this->groupedPermissionsForForm();
        $usuarioPerms = $user->getDirectPermissions()->pluck('name')->all();

        return view('coordenador.tecnicos.edit', [
            'usuario'      => $user,
            'permissions'  => $permissions,
            'usuarioPerms' => $usuarioPerms,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->assertMine($user);

        $request->merge([
            'email' => $request->filled('email') ? strtolower($request->email) : null,
            'cpf'   => preg_replace('/\D+/', '', (string)$request->input('cpf')) ?: null,
        ]);

        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['nullable','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'cpf'      => ['nullable','digits:11', Rule::unique('users','cpf')->ignore($user->id)],
            'password' => ['nullable','string','min:8','confirmed'],
            'perms'    => ['array'],
            'perms.*'  => ['string', Rule::in($this->offerablePermissionNames())],
        ], self::messages(), self::attributes());

        $user->name  = $data['name'];
        $user->email = $data['email'] ?? null;
        $user->cpf   = $data['cpf'] ?? null;

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        $allowed = $this->offerablePermissionNames();
        $requested = (array) ($data['perms'] ?? []);
        $toGrant = array_values(array_intersect($requested, $allowed));

        $user->syncPermissions($toGrant);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return back()->with('ok','Técnico atualizado.');
    }

    public function destroy(User $user)
    {
        $this->assertMine($user);
        $user->delete();
        return redirect()->route('coordenador.tecnicos.index')->with('ok','Técnico excluído.');
    }

    private function assertMine(User $user): void
    {
        abort_unless($user->hasRole('Tecnico') && $user->coordenador_id === auth()->id(), 403);
    }

    private static function messages(): array
    {
        return [
            'required'   => 'Campo obrigatório.',
            'email'      => 'Informe um e-mail válido.',
            'digits'     => 'O campo :attribute deve ter :digits dígitos.',
            'unique'     => 'Já existe um registro com este :attribute.',
            'min.string' => 'A :attribute deve ter pelo menos :min caracteres.',
            'confirmed'  => 'A confirmação de :attribute não confere.',
        ];
    }
    private static function attributes(): array
    {
        return [
            'name' => 'nome', 'email' => 'e-mail', 'cpf' => 'CPF',
            'password' => 'senha','password_confirmation'=>'confirmação de senha',
        ];
    }

    private function offerablePermissionNames(): array
    {
        return auth()->user()?->delegablePermissionNames() ?? [];
    }

    private function groupedPermissionsForForm()
    {
        return Permission::query()
            ->whereIn('name', $this->offerablePermissionNames())
            ->orderBy('name')
            ->get()
            ->groupBy(fn ($p) => Str::before($p->name, '.'));
    }

}
