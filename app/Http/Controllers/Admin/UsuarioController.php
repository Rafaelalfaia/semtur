<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $q    = trim((string) $request->input('q', ''));
        $role = trim((string) $request->input('role', ''));

        $users = User::query()
            ->with('roles')
            ->when($q !== '', function ($qq) use ($q) {
                $digits = preg_replace('/\D+/', '', $q);
                $qq->where(function ($w) use ($q, $digits) {
                    $w->where('name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%")
                      ->orWhere('cpf', 'like', "%{$digits}%");
                });
            })
            ->when($role !== '', fn ($qq) => $qq->whereHas('roles', fn ($r) => $r->where('name', $role)))
            ->orderBy('name')
            ->paginate(12)
            ->appends($request->only('q', 'role'));

        $roles = Role::query()->orderBy('name')->pluck('name', 'id');

        return view('admin.usuarios.index', compact('users', 'roles', 'q', 'role'));
    }

    public function create()
    {
        $roles = $this->assignableRoles();

        $permissions = $this->grantablePermissions()
            ->groupBy(fn ($p) => Str::before($p->name, '.'));

        return view('admin.usuarios.create', compact('roles', 'permissions'));
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);

        $roleName = $this->resolveRoleName($data['roles'][0] ?? null);

        abort_if($roleName === 'Tecnico', 422, 'Técnico só pode ser criado pelo Coordenador.');

        $user = new User();
        $user->name = $data['name'];
        $user->cpf = $data['cpf'];
        $user->email = $data['email'] ?? null;
        $user->password = Hash::make($data['password']);
        $user->save();

        $user->syncRoles([$roleName]);

        if ($roleName === 'Coordenador') {
            $user->syncCoordenadorDirectPermissions($this->filterGrantablePermissions($data['perms'] ?? []));
        } else {
            $user->syncPermissions([]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('admin.usuarios.index')->with('ok', 'Usuário criado com sucesso.');
    }

    public function edit(User $usuario)
    {
        abort_if($usuario->hasRole('Tecnico'), 403, 'Técnico é gerenciado pelo Coordenador.');

        $roles = $this->assignableRoles();

        $permissions = $this->grantablePermissions()
            ->groupBy(fn ($p) => Str::before($p->name, '.'));

        $usuarioRoles = $usuario->roles->pluck('name')->all();
        $usuarioPerms = $usuario->hasRole('Coordenador')
            ? $usuario->getDirectPermissions()->pluck('name')->all()
            : [];

        return view('admin.usuarios.edit', compact(
            'usuario',
            'roles',
            'permissions',
            'usuarioRoles',
            'usuarioPerms'
        ));
    }

    public function update(Request $request, User $usuario)
    {
        abort_if($usuario->hasRole('Tecnico'), 403, 'Técnico é gerenciado pelo Coordenador.');

        $data = $this->validatePayload($request, $usuario);

        $roleName = $this->resolveRoleName($data['roles'][0] ?? null);

        abort_if($roleName === 'Tecnico', 422, 'Técnico só pode ser criado pelo Coordenador.');

        if (auth()->id() === $usuario->id && $roleName !== 'Admin') {
            return back()->with('erro', 'Você não pode remover seu próprio papel de Admin.');
        }

        if ($usuario->hasRole('Coordenador') && $roleName !== 'Coordenador' && $usuario->tecnicos()->exists()) {
            return back()->with('erro', 'Este Coordenador possui Técnicos vinculados. Remova ou transfira os Técnicos antes de alterar o papel.');
        }

        $usuario->name = $data['name'];
        $usuario->cpf = $data['cpf'];
        $usuario->email = $data['email'] ?? null;

        if (!empty($data['password'])) {
            $usuario->password = Hash::make($data['password']);
        }

        $usuario->save();

        $usuario->syncRoles([$roleName]);

        if ($roleName === 'Coordenador') {
            $usuario->syncCoordenadorDirectPermissions($this->filterGrantablePermissions($data['perms'] ?? []));
        } else {
            $usuario->syncPermissions([]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('admin.usuarios.index')->with('ok', 'Usuário atualizado com sucesso.');
    }

    public function destroy(User $usuario)
    {
        if (auth()->id() === $usuario->id) {
            return back()->with('erro', 'Você não pode excluir seu próprio usuário.');
        }

        if ($usuario->hasRole('Tecnico')) {
            return back()->with('erro', 'Técnicos devem ser excluídos pelo Coordenador.');
        }

        if ($usuario->hasRole('Coordenador') && $usuario->tecnicos()->exists()) {
            return back()->with('erro', 'Este Coordenador possui Técnicos vinculados. Remova os Técnicos antes de excluir.');
        }

        $usuario->delete();

        return redirect()->route('admin.usuarios.index')->with('ok', 'Usuário excluído.');
    }

    private function validatePayload(Request $request, ?User $usuario = null): array
    {
        $request->merge([
            'email' => $request->filled('email') ? strtolower($request->email) : null,
            'cpf'   => preg_replace('/\D+/', '', (string) $request->input('cpf')) ?: null,
        ]);

        $roleIds = $this->assignableRoles()->pluck('id')->map(fn ($id) => (string) $id)->all();
        $grantablePerms = $this->grantablePermissions()->pluck('name')->all();

        return $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($usuario?->id)],
            'cpf'                   => ['nullable', 'digits:11', Rule::unique('users', 'cpf')->ignore($usuario?->id)],
            'password'              => [$usuario ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'roles'                 => ['required', 'array', 'size:1'],
            'roles.*'               => ['required', 'string', Rule::in($roleIds)],
            'perms'                 => ['nullable', 'array'],
            'perms.*'               => ['string', Rule::in($grantablePerms)],
        ], $this->messages(), $this->attributes());
    }

    private function assignableRoles()
    {
        return Role::query()
            ->whereIn('name', ['Admin', 'Coordenador'])
            ->orderBy('name')
            ->get();
    }

    private function grantablePermissions()
    {
        return Permission::query()
            ->where('name', 'not like', 'usuarios.%')
            ->where('name', 'not like', 'console.cache.%')
            ->orderBy('name')
            ->get();
    }

    private function filterGrantablePermissions(array $requested): array
    {
        $allowed = $this->grantablePermissions()->pluck('name')->all();

        return array_values(array_intersect($requested, $allowed));
    }

    private function resolveRoleName(?string $roleId): string
    {
        $roleId = (string) $roleId;

        $role = $this->assignableRoles()
            ->first(fn ($r) => (string) $r->id === $roleId);

        abort_unless($role, 422, 'Papel inválido.');

        return (string) $role->name;
    }

    private function messages(): array
    {
        return [
            'required'   => 'Campo obrigatório.',
            'email'      => 'Informe um e-mail válido.',
            'digits'     => 'O campo :attribute deve ter :digits dígitos.',
            'unique'     => 'Já existe um registro com este :attribute.',
            'min.string' => 'A :attribute deve ter pelo menos :min caracteres.',
            'confirmed'  => 'A confirmação de :attribute não confere.',
            'size'       => 'Selecione apenas um papel.',
            'in'         => 'Valor inválido para :attribute.',
        ];
    }

    private function attributes(): array
    {
        return [
            'name'                  => 'nome',
            'email'                 => 'e-mail',
            'cpf'                   => 'CPF',
            'password'              => 'senha',
            'password_confirmation' => 'confirmação de senha',
            'roles'                 => 'papel',
            'perms'                 => 'permissões',
        ];
    }
}
