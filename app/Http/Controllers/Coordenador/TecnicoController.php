<?php

namespace App\Http\Controllers\Coordenador;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

class TecnicoController extends Controller
{
    public function index(Request $r)
    {
        $q    = trim((string) $r->input('q',''));
        $like = \DB::getDriverName()==='pgsql' ? 'ilike' : 'like';

        $users = User::query()
            ->onlyMyTecnicos()
            ->when($q !== '', function($qq) use ($q,$like){
                $d = preg_replace('/\D+/', '', $q);
                $qq->where(function($w) use ($q,$d,$like){
                    $w->where('name',$like,"%{$q}%")
                      ->orWhere('email',$like,"%{$q}%");
                    if ($d) $w->orWhere('cpf',$like,"%{$d}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        // Permissões que o coordenador PODE oferecer
        $offerable   = auth()->user()->getAllPermissions()->pluck('name')->sort()->values();
        $permissions = Permission::whereIn('name',$offerable)->get()
            ->groupBy(fn($p)=>\Illuminate\Support\Str::before($p->name,'.'));

        return view('coordenador.tecnicos.index', compact('users','permissions','q'));
    }

    public function create()
    {
        $permissions = Permission::whereIn('name',
            auth()->user()->getAllPermissions()->pluck('name')
        )->get()->groupBy(fn($p)=>\Illuminate\Support\Str::before($p->name,'.'));

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
            'perms.*'  => ['string'],
        ], self::messages(), self::attributes());

        $u = User::create([
            'name'           => $data['name'],
            'email'          => $data['email'] ?? null,
            'cpf'            => $data['cpf'] ?? null,
            'password'       => Hash::make($data['password']),
            'coordenador_id' => auth()->id(),
        ]);
        $u->syncRoles(['Tecnico']);

        $allowed   = auth()->user()->getAllPermissions()->pluck('name')->all();
        $requested = Permission::whereIn('name', (array)($data['perms'] ?? []))->pluck('name')->all();
        $toGrant   = array_values(array_intersect($requested, $allowed));
        $u->syncPermissions($toGrant);

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('coordenador.tecnicos.index')->with('ok','Técnico criado.');
    }

    public function edit(User $user)
    {
        $this->assertMine($user);

        $permissions = Permission::whereIn('name',
            auth()->user()->getAllPermissions()->pluck('name')
        )->get()->groupBy(fn($p)=>\Illuminate\Support\Str::before($p->name,'.'));

        $usuarioPerms = $user->getPermissionNames()->all();

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
            'perms.*'  => ['string'],
        ], self::messages(), self::attributes());

        $user->name  = $data['name'];
        $user->email = $data['email'] ?? null;
        $user->cpf   = $data['cpf'] ?? null;
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        $allowed   = auth()->user()->getAllPermissions()->pluck('name')->all();
        $requested = Permission::whereIn('name', (array)($data['perms'] ?? []))->pluck('name')->all();
        $toGrant   = array_values(array_intersect($requested, $allowed));
        $user->syncPermissions($toGrant);

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

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
}
