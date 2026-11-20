<?php

namespace App\Livewire;

use App\Models\User;
use App\Support\ActividadLegible;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

/**
 * Registro de actividad (kit, spec 38), solo lectura: quién creó, editó o eliminó qué y
 * cuándo, con buscador, filtros y el detalle de cada cambio (antes → después). La
 * traducción a texto legible está en ActividadLegible.
 *
 * La ven solo los administradores (no entra en la matriz de permisos). Por ahora
 * registra usuarios y permisos, incluido lo hecho desde la intranet ("Intranet ·
 * Nombre (DNI n)").
 */
class Actividad extends Component
{
    use WithPagination;

    public const ACCIONES = ['created', 'updated', 'deleted'];

    #[Url(as: 'q', except: '')]
    public string $buscar = '';

    #[Url(as: 'accion', except: '')]
    public string $filtroEvento = '';

    /** Id del usuario, `sistema` (sin usuario ni intranet), `intranet` o '' (todos). */
    #[Url(as: 'usuario', except: '')]
    public string $filtroUsuario = '';

    #[Url(as: 'tipo', except: '')]
    public string $filtroTipo = '';

    #[Url(except: '')]
    public string $desde = '';

    #[Url(except: '')]
    public string $hasta = '';

    public bool $mostrarFiltros = false;

    public function mount(): void
    {
        abort_unless(auth()->user()?->esAdministrador(), 403);
    }

    /** Cualquier filtro vuelve a la primera página. */
    public function updated(string $propiedad): void
    {
        if (in_array($propiedad, ['buscar', 'filtroEvento', 'filtroUsuario', 'filtroTipo', 'desde', 'hasta'], true)) {
            $this->resetPage();
        }
    }

    public function limpiarFiltros(): void
    {
        $this->reset(['buscar', 'filtroEvento', 'filtroUsuario', 'filtroTipo', 'desde', 'hasta']);
        $this->resetPage();
    }

    private function fecha(string $valor): ?Carbon
    {
        try {
            return $valor === '' ? null : Carbon::createFromFormat('Y-m-d', $valor);
        } catch (\Throwable) {
            return null;
        }
    }

    /** Cada palabra tiene que aparecer en el nombre, el contexto, el usuario, el actor de la intranet o el tipo. */
    private function aplicarBusqueda(Builder $q): void
    {
        $palabras = preg_split('/\s+/', trim($this->buscar), -1, PREG_SPLIT_NO_EMPTY);

        foreach (array_slice($palabras, 0, 8) as $palabra) {
            $like = '%'.addcslashes($palabra, '%_\\').'%';

            $usuarios = User::where('name', 'like', $like)->pluck('id');
            $clases = collect(array_keys(ActividadLegible::TIPOS))
                ->filter(fn ($clase) => Str::contains(Str::lower(Str::ascii(ActividadLegible::etiqueta($clase))), Str::lower(Str::ascii($palabra))));

            $q->where(fn (Builder $w) => $w
                ->where('description', 'like', $like)
                ->orWhere('contexto', 'like', $like)
                ->orWhere('properties->actor_nombre', 'like', $like)
                ->when($usuarios->isNotEmpty(), fn ($w) => $w->orWhere(fn ($u) => $u
                    ->where('causer_type', (new User)->getMorphClass())->whereIn('causer_id', $usuarios)))
                ->when($clases->isNotEmpty(), fn ($w) => $w->orWhereIn('subject_type', $clases)));
        }
    }

    private function consulta(): Builder
    {
        $desde = $this->fecha($this->desde)?->startOfDay();
        $hasta = $this->fecha($this->hasta)?->endOfDay();

        return Activity::query()
            ->with('causer')
            ->when(trim($this->buscar) !== '', fn ($q) => $this->aplicarBusqueda($q))
            ->when(in_array($this->filtroEvento, self::ACCIONES, true), fn ($q) => $q->where('event', $this->filtroEvento))
            ->when($this->filtroUsuario === 'sistema', fn ($q) => $q->whereNull('causer_id')
                ->where(fn ($w) => $w->whereNull('properties->origen')->orWhere('properties->origen', '!=', 'intranet')))
            ->when($this->filtroUsuario === 'intranet', fn ($q) => $q->whereNull('causer_id')
                ->where('properties->origen', 'intranet'))
            ->when(ctype_digit($this->filtroUsuario), fn ($q) => $q
                ->where('causer_type', (new User)->getMorphClass())
                ->where('causer_id', (int) $this->filtroUsuario))
            ->when(in_array($this->filtroTipo, ActividadLegible::GRUPOS, true), fn ($q) => $q
                ->whereIn('subject_type', ActividadLegible::clasesDelGrupo($this->filtroTipo)))
            ->when($desde, fn ($q) => $q->where('created_at', '>=', $desde))
            ->when($hasta, fn ($q) => $q->where('created_at', '<=', $hasta))
            ->latest()
            ->orderByDesc('id');
    }

    /** Todo lo que la vista muestra de una actividad. */
    private function fila(Activity $a): array
    {
        $existe = $a->subject_type && class_exists($a->subject_type) && $a->subject !== null;

        return [
            'id' => $a->id,
            'evento' => $a->event ?? 'sistema',
            'nombre' => filled($a->description) ? $a->description : __('actividad.sin_nombre'),
            'tipo' => ActividadLegible::etiqueta($a->subject_type),
            'contexto' => $a->contexto,
            'resumen' => ActividadLegible::resumen($a),
            'cambios' => ActividadLegible::cambios($a),
            'usuario' => $a->causer?->name ?? ActividadLegible::autorExterno($a),
            'fecha' => $a->created_at,
            'borrado' => ! $existe && $a->event !== 'deleted' && $a->subject_type !== null,
        ];
    }

    public function render()
    {
        $actividades = $this->consulta()->paginate(25);

        $usuarios = User::orderBy('name')->pluck('name', 'id')
            ->mapWithKeys(fn ($nombre, $id) => [(string) $id => $nombre])->all();

        $filtrosActivos = collect([$this->filtroEvento, $this->filtroUsuario, $this->filtroTipo, $this->desde, $this->hasta])
            ->filter(fn ($v) => $v !== '')->count();

        return view('livewire.actividad', [
            'actividades' => $actividades,
            'filas' => $actividades->getCollection()->map(fn (Activity $a) => $this->fila($a)),
            'acciones' => array_combine(self::ACCIONES, array_map(fn ($a) => __("actividad.acciones_filtro.{$a}"), self::ACCIONES)),
            'grupos' => array_combine(ActividadLegible::GRUPOS, array_map(fn ($g) => __("actividad.grupos.{$g}"), ActividadLegible::GRUPOS)),
            'quienes' => ['intranet' => __('actividad.filtros.usuario.intranet'), 'sistema' => __('actividad.filtros.usuario.sistema')] + $usuarios,
            'filtrosActivos' => $filtrosActivos,
            'hayFiltros' => $filtrosActivos > 0 || trim($this->buscar) !== '',
        ])->layout('layouts.app', [
            'header' => __('actividad.titulo'),
        ]);
    }
}
