<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class UsuarioController extends Controller
{
    private function onlyDigits(?string $v): string
    {
        return preg_replace('/\D+/', '', (string)$v ?? '');
    }

    public function index(Request $request)
    {
        $q    = trim((string)$request->input('q',''));
        $role = trim((string)$request->input('role',''));

        $users = User::query()
            ->with('roles')
            ->when($q !== '', function ($qq) use ($q) {
                $digits = preg_replace('/\D+/', '', $q);
                $qq->where(function ($w) use ($q, $digits) {
                    $w->where('name','like',"%{$q}%")
                      ->orWhere('email','like',"%{$q}%")
                      ->orWhere('cpf','like',"%{$digits}%");
                });
            })
            ->when($role !== '', fn($qq) => $qq->whereHas('roles', fn($r)=>$r->where('name',$role)))
            ->orderBy('name')
            ->paginate(12)
            ->appends($request->only('q','role'));

        $roles = \Spatie\Permission\Models\Role::query()->orderBy('name')->pluck('name','id');

        return view('admin.usuarios.index', compact('users','roles','q','role'));
    }

    public function create()
    {
        $roles = \Spatie\Permission\Models\Role::orderBy('name')->get(); // <= objetos
        $permissions = \Spatie\Permission\Models\Permission::orderBy('name')->get()
            ->groupBy(fn($p) => \Illuminate\Support\Str::before($p->name, '.'));

        return view('admin.usuarios.create', compact('roles', 'permissions'));
    }


    public function store(Request $request)
    {
        $request->merge([
            'email' => $request->filled('email') ? strtolower($request->email) : null,
            'cpf'   => preg_replace('/\D+/', '', (string) $request->input('cpf')) ?: null,
        ]);

        $rules = [
            'name'                  => ['required','string','max:255'],
            'email'                 => ['nullable','email','max:255', Rule::unique('users','email')->ignore($usuario->id ?? null)],
            'cpf'                   => ['nullable','digits:11', Rule::unique('users','cpf')->ignore($usuario->id ?? null)],
            'password'              => [request()->isMethod('post') ? 'required' : 'nullable','string','min:8','confirmed'],
            'roles'                 => ['array'],
            'roles.*'               => ['nullable','string'], // aceita nome ou id como string
            'perms'                 => ['array'],
            'perms.*'               => ['string'],
        ];

        // mensagens e aliases em PT-BR
        $msg = [
            'required'   => 'Campo obrigatório.',
            'email'      => 'Informe um e-mail válido.',
            'digits'     => 'O campo :attribute deve ter :digits dígitos.',
            'unique'     => 'Já existe um registro com este :attribute.',
            'min.string' => 'A :attribute deve ter pelo menos :min caracteres.',
            'confirmed'  => 'A confirmação de :attribute não confere.',
        ];
        $attr = [
            'name'                  => 'nome',
            'email'                 => 'e-mail',
            'cpf'                   => 'CPF',
            'password'              => 'senha',
            'password_confirmation' => 'confirmação de senha',
        ];

        $data = $request->validate($rules, $msg, $attr);


        $user = new User();
        $user->name  = $data['name'];
        $user->cpf   = $data['cpf'];            // obrigatório
        $user->email = $data['email'] ?? null;  // opcional
        $user->password = \Hash::make($data['password']);
        $user->save();

        if (!empty($data['roles'])) {
            $roleNames = \Spatie\Permission\Models\Role::whereIn('id',$data['roles'])->pluck('name')->all();
            $user->syncRoles($roleNames);
        }

        return redirect()->route('admin.usuarios.index')->with('ok','Usuário criado com sucesso.');
    }

    public function edit(User $usuario)
    {
        $roles = \Spatie\Permission\Models\Role::orderBy('name')->get(); // objetos
        $permissions = \Spatie\Permission\Models\Permission::orderBy('name')->get()
            ->groupBy(fn($p) => \Illuminate\Support\Str::before($p->name, '.'));

        $usuarioRoles = $usuario->roles->pluck('name')->all();
        $usuarioPerms = $usuario->getPermissionNames()->all();

        return view('admin.usuarios.edit', compact('usuario','roles','permissions','usuarioRoles','usuarioPerms'));
    }


    public function update(Request $request, User $usuario)
    {
        // normalizações
        $request->merge([
            'email' => $request->filled('email') ? strtolower($request->email) : null,
            'cpf'   => preg_replace('/\D+/', '', (string) $request->input('cpf')) ?: null,
        ]);

        $rules = [
            'name'                  => ['required','string','max:255'],
            'email'                 => ['nullable','email','max:255', Rule::unique('users','email')->ignore($usuario->id ?? null)],
            'cpf'                   => ['nullable','digits:11', Rule::unique('users','cpf')->ignore($usuario->id ?? null)],
            'password'              => [request()->isMethod('post') ? 'required' : 'nullable','string','min:8','confirmed'],
            'roles'                 => ['array'],
            'roles.*'               => ['nullable','string'], // aceita nome ou id como string
            'perms'                 => ['array'],
            'perms.*'               => ['string'],
        ];

        // mensagens e aliases em PT-BR
        $msg = [
            'required'   => 'Campo obrigatório.',
            'email'      => 'Informe um e-mail válido.',
            'digits'     => 'O campo :attribute deve ter :digits dígitos.',
            'unique'     => 'Já existe um registro com este :attribute.',
            'min.string' => 'A :attribute deve ter pelo menos :min caracteres.',
            'confirmed'  => 'A confirmação de :attribute não confere.',
        ];
        $attr = [
            'name'                  => 'nome',
            'email'                 => 'e-mail',
            'cpf'                   => 'CPF',
            'password'              => 'senha',
            'password_confirmation' => 'confirmação de senha',
        ];

        $data = $request->validate($rules, $msg, $attr);


        $usuario->name  = $data['name'];
        $usuario->cpf   = $data['cpf'];
        $usuario->email = $data['email'] ?? null;

        if (!empty($data['password'])) {
            $usuario->password = \Hash::make($data['password']);
        }

        $usuario->save();

        if (auth()->id() === $usuario->id && empty($data['roles'])) {
            // não zera os próprios papéis acidentalmente
        } else {
            $roleNames = !empty($data['roles'])
                ? \Spatie\Permission\Models\Role::whereIn('id',$data['roles'])->pluck('name')->all()
                : [];
            $usuario->syncRoles($roleNames);
        }

        return redirect()->route('admin.usuarios.index')->with('ok','Usuário atualizado com sucesso.');
    }

    public function destroy(User $usuario)
    {
        if (auth()->id() === $usuario->id) {
            return back()->with('erro','Você não pode excluir seu próprio usuário.');
        }
        $usuario->delete();
        return redirect()->route('admin.usuarios.index')->with('ok','Usuário excluído.');
    }
}
