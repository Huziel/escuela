<?php
namespace App\Modules\Usuarios\Controllers;
use App\Http\Controllers\Controller; use App\Traits\ApiResponse;
use App\Models\User; use App\Models\Acceso;
use Illuminate\Http\JsonResponse; use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = User::with('roles');
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use($s) {
                $q->where('nombre','like',"%{$s}%")->orWhere('apellido_paterno','like',"%{$s}%")->orWhere('apellido_materno','like',"%{$s}%")->orWhere('username','like',"%{$s}%")->orWhere('email','like',"%{$s}%");
            });
        }
        if ($request->filled('rol')) $query->where('rol', $request->rol);
        if ($request->has('activo')) $query->where('activo', $request->activo);
        return $this->paginated($query->orderBy('created_at','desc')->paginate($request->get('per_page',15)), 'Usuarios obtenidos.');
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'username' => 'required|string|max:50|unique:users',
            'nombre' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'rol' => 'required|in:super_admin,admin,control_escolar,docente,tutor,alumno',
            'sexo' => 'nullable|in:M,F',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:500',
            'curp' => 'nullable|string|max:18|unique:users',
            'fecha_nacimiento' => 'nullable|date',
        ]);
        $data['password'] = Hash::make($data['password']);
        $data['activo'] = true;
        $user = User::create($data);
        $user->assignRole($data['rol']);
        return $this->success($user->load('roles'), 'Usuario creado.', 201);
    }

    public function show(int $id): JsonResponse
    {
        $user = User::with(['roles','accesos'=>fn($q)=>$q->latest()->take(20)])->find($id);
        return $user ? $this->success($user) : $this->error('No encontrado.', 404);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $data = $request->validate([
            'username' => 'string|max:50|unique:users,username,'.$id,
            'nombre' => 'string|max:100',
            'apellido_paterno' => 'string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'email' => 'email|unique:users,email,'.$id,
            'sexo' => 'nullable|in:M,F',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:500',
            'curp' => 'nullable|string|max:18',
            'fecha_nacimiento' => 'nullable|date',
            'fotografia' => 'nullable|string|max:255',
        ]);
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
        if ($request->filled('rol')) {
            $user->syncRoles([$request->rol]);
            $data['rol'] = $request->rol;
        }
        $user->update($data);
        return $this->success($user->load('roles'), 'Actualizado.');
    }

    public function destroy(int $id): JsonResponse
    {
        User::findOrFail($id)->delete();
        return $this->success(null, 'Eliminado.');
    }

    public function uploadFoto(Request $request, int $id): JsonResponse
    {
        $request->validate(['foto' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048']);
        $user = User::findOrFail($id);
        
        if ($user->fotografia) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($user->fotografia);
        }
        
        $path = $request->file('foto')->store('fotos', 'public');
        $user->update(['fotografia' => $path]);
        
        return $this->success(['url' => asset('storage/' . $path)], 'Foto actualizada.');
    }

    public function bloquear(int $id): JsonResponse
    {
        User::findOrFail($id)->update(['activo' => false]);
        return $this->success(null, 'Usuario bloqueado.');
    }

    public function desbloquear(int $id): JsonResponse
    {
        User::findOrFail($id)->update(['activo' => true]);
        return $this->success(null, 'Usuario desbloqueado.');
    }

    public function bitacora(int $id): JsonResponse
    {
        $accesos = Acceso::where('user_id', $id)->latest()->paginate(20);
        return $this->paginated($accesos, 'Bitácora de acceso.');
    }
}
