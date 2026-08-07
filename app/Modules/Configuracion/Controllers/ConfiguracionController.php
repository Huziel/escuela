<?php
namespace App\Modules\Configuracion\Controllers;
use App\Http\Controllers\Controller; use App\Traits\ApiResponse;
use App\Models\Configuracion;
use App\Models\Noticia;
use App\Models\Aviso;
use App\Models\CicloEscolar;
use Illuminate\Http\JsonResponse; use Illuminate\Http\Request;

class ConfiguracionController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        return $this->success(Configuracion::all(), 'Configuraciones.');
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate(['configuraciones' => 'required|array', 'configuraciones.*.clave' => 'required|string', 'configuraciones.*.valor' => 'nullable']);
        foreach ($data['configuraciones'] as $c) {
            Configuracion::updateOrCreate(['clave' => $c['clave']], ['valor' => $c['valor'] ?? '', 'tipo' => $c['tipo'] ?? 'string', 'descripcion' => $c['descripcion'] ?? null]);
        }
        return $this->success(Configuracion::all(), 'Actualizadas.');
    }

    // Noticias
    public function noticias(): JsonResponse
    {
        return $this->paginated(Noticia::with('user')->latest()->paginate(10), 'Noticias.');
    }
    public function storeNoticia(Request $request): JsonResponse
    {
        $data = $request->validate([
            'titulo' => 'required|string|max:200',
            'contenido' => 'nullable|string',
            'imagen' => 'nullable|string|max:500',
            'fecha' => 'required|date',
            'publicado' => 'boolean',
        ]);
        $n = Noticia::create([
            'titulo' => $data['titulo'] ?? '',
            'contenido' => $data['contenido'] ?? '',
            'imagen' => $data['imagen'] ?? null,
            'fecha_publicacion' => $data['fecha'] ?? now(),
            'activo' => $data['publicado'] ?? true,
            'user_id' => auth()->id(),
        ]);
        return $this->success($n->load('user'), 'Noticia creada.', 201);
    }
    public function uploadNoticiaImagen(Request $request): JsonResponse
    {
        $request->validate(['imagen' => 'required|image|mimes:jpeg,png,jpg,webp|max:4096']);
        $path = $request->file('imagen')->store('noticias', 'public');
        return $this->success(['url' => asset('storage/' . $path)], 'Imagen subida.');
    }
    public function updateNoticia(Request $request, int $id): JsonResponse
    {
        $n = Noticia::findOrFail($id);
        $data = $request->validate([
            'titulo' => 'nullable|string|max:200',
            'contenido' => 'nullable|string',
            'imagen' => 'nullable|string|max:500',
            'fecha' => 'required|date',
            'publicado' => 'boolean',
        ]);
        $n->update([
            'titulo' => $data['titulo'] ?? $n->titulo,
            'contenido' => $data['contenido'] ?? $n->contenido,
            'imagen' => $data['imagen'] ?? $n->imagen,
            'fecha_publicacion' => $data['fecha'] ?? $n->fecha_publicacion,
            'activo' => $data['publicado'] ?? $n->activo,
        ]);
        return $this->success($n, 'Actualizada.');
    }
    public function destroyNoticia(int $id): JsonResponse { Noticia::findOrFail($id)->delete(); return $this->success(null,'Eliminada.'); }

    // Avisos
    public function avisos(): JsonResponse
    {
        return $this->paginated(Aviso::with('user','grupo')->latest()->paginate(10), 'Avisos.');
    }
    public function storeAviso(Request $request): JsonResponse
    {
        $data = $request->validate(['titulo'=>'required|string|max:200','contenido'=>'required|string','tipo'=>'required|in:general,alumnos,docentes,grupo','grupo_id'=>'nullable|exists:grupos,id','fecha_inicio'=>'required|date','fecha_fin'=>'nullable|date','activo'=>'boolean']);
        $a = Aviso::create($data + ['user_id'=>auth()->id()]);
        return $this->success($a->load('user'), 'Aviso creado.',201);
    }
    public function updateAviso(Request $request, int $id): JsonResponse
    {
        $a = Aviso::findOrFail($id);
        $data = $request->validate(['titulo'=>'string|max:200','contenido'=>'string','tipo'=>'in:general,alumnos,docentes,grupo','grupo_id'=>'nullable|exists:grupos,id','fecha_inicio'=>'date','fecha_fin'=>'nullable|date','activo'=>'boolean']);
        $a->update($data);
        return $this->success($a, 'Actualizado.');
    }
    public function destroyAviso(int $id): JsonResponse { Aviso::findOrFail($id)->delete(); return $this->success(null,'Eliminado.'); }

    // Ciclos Escolares
    public function ciclos(): JsonResponse { return $this->success(CicloEscolar::all(), 'Ciclos.'); }
    public function storeCiclo(Request $request): JsonResponse
    {
        $data = $request->validate(['nombre'=>'required|string|max:50','fecha_inicio'=>'required|date','fecha_fin'=>'required|date|after:fecha_inicio','activo'=>'boolean']);
        if ($data['activo'] ?? false) CicloEscolar::where('activo',true)->update(['activo'=>false]);
        $c = CicloEscolar::create($data);
        return $this->success($c, 'Ciclo creado.',201);
    }
    public function updateCiclo(Request $request, int $id): JsonResponse
    {
        $c = CicloEscolar::findOrFail($id);
        $data = $request->validate(['nombre'=>'string|max:50','fecha_inicio'=>'date','fecha_fin'=>'date|after:fecha_inicio','activo'=>'boolean']);
        if ($data['activo'] ?? false) CicloEscolar::where('id','!=',$id)->where('activo',true)->update(['activo'=>false]);
        $c->update($data);
        return $this->success($c, 'Actualizado.');
    }
    public function destroyCiclo(int $id): JsonResponse { CicloEscolar::findOrFail($id)->delete(); return $this->success(null,'Eliminado.'); }

    public function upload(Request $request): JsonResponse
    {
        $request->validate(['file' => 'required|image|max:2048', 'tipo' => 'required|in:logo,favicon']);
        $tipo = $request->tipo;
        $path = $request->file('file')->storeAs('branding', $tipo . '.' . $request->file('file')->extension(), 'public');
        
        Configuracion::updateOrCreate(
            ['clave' => 'site_' . $tipo],
            ['valor' => $path, 'tipo' => 'file', 'descripcion' => $tipo === 'logo' ? 'Logo del sitio' : 'Favicon del sitio']
        );
        
        return $this->success(['url' => asset('storage/' . $path)], 'Imagen guardada.');
    }
}
