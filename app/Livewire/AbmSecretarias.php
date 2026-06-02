<?php

namespace App\Livewire;

use App\Models\Secretaria;
use App\Models\Area;
use Livewire\Component;
use Livewire\WithPagination;

class AbmSecretarias extends Component
{
    use WithPagination;

    public $search = '';

    // Modal Secretaría
    public $showModalSecretaria = false;
    public $editModeSecretaria = false;
    public $secretaria_id;
    public $secretaria = '';

    // Modal Área
    public $showModalArea = false;
    public $editModeArea = false;
    public $area_id;
    public $area = '';
    public $id_secretaria = '';

    // Modal Ver Áreas
    public $showModalAreas = false;
    public $areasSecretaria = [];
    public $nombreSecretariaAreas = '';
    public $idSecretariaAreas = null;

    protected function rules()
    {
        if ($this->showModalSecretaria) {
            return [
                'secretaria' => 'required|string|max:200',
            ];
        }

        return [
            'area' => 'required|string|max:200',
            'id_secretaria' => 'required|exists:secretarias,id',
        ];
    }

    protected function messages()
    {
        return [
            'secretaria.required' => 'El nombre de la secretaría es obligatorio',
            'area.required' => 'El nombre del área es obligatorio',
            'id_secretaria.required' => 'Debe seleccionar una secretaría',
            'id_secretaria.exists' => 'La secretaría seleccionada no es válida',
        ];
    }

    public function mount()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
    }

    public function render()
    {
        $user = auth()->user();

        $secretarias = Secretaria::withCount('areas')
            ->with(['areas' => function ($query) {
                $query->orderBy('area');
            }])
            ->when($this->search, function ($query) {
                $query->where('secretaria', 'like', '%' . $this->search . '%')
                    ->orWhereHas('areas', function ($q) {
                        $q->where('area', 'like', '%' . $this->search . '%');
                    });
            })
            ->orderBy('secretaria')
            ->paginate(15);

        return view('livewire.abm-secretarias', [
            'secretarias' => $secretarias,
            'puedeCrear' => $user->puedeCrearSecretarias(),
            'puedeEditar' => $user->puedeEditarSecretarias(),
            'puedeEliminar' => $user->puedeEliminarSecretarias(),
        ])->layout('layouts.app', [
            'header' => 'ABM Secretarías y Áreas'
        ]);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function verAreas($id)
    {
        $secretaria = Secretaria::with(['areas' => fn($q) => $q->orderBy('area')])->findOrFail($id);
        $this->idSecretariaAreas = $secretaria->id;
        $this->nombreSecretariaAreas = $secretaria->secretaria;
        $this->areasSecretaria = $secretaria->areas->toArray();
        $this->showModalAreas = true;
    }

    public function cerrarModalAreas()
    {
        $this->showModalAreas = false;
        $this->areasSecretaria = [];
        $this->nombreSecretariaAreas = '';
        $this->idSecretariaAreas = null;
    }

    public function eliminarAreaDesdeModal($id)
    {
        if (!auth()->user()->puedeEliminarSecretarias()) {
            session()->flash('error', 'No tienes permisos para eliminar áreas.');
            return;
        }

        $area = Area::findOrFail($id);

        try {
            $area->delete();
            // Refrescar el listado del modal
            $this->verAreas($this->idSecretariaAreas);
        } catch (\Exception $e) {
            session()->flash('error', 'No se puede eliminar el área porque tiene registros asociados.');
            $this->showModalAreas = false;
        }
    }

    // --- CRUD Secretarías ---

    public function crearSecretaria()
    {
        if (!auth()->user()->puedeCrearSecretarias()) {
            session()->flash('error', 'No tienes permisos para crear secretarías.');
            return;
        }

        $this->resetFormSecretaria();
        $this->editModeSecretaria = false;
        $this->showModalSecretaria = true;
    }

    public function editarSecretaria($id)
    {
        if (!auth()->user()->puedeEditarSecretarias()) {
            session()->flash('error', 'No tienes permisos para editar secretarías.');
            return;
        }

        $secretaria = Secretaria::findOrFail($id);
        $this->secretaria_id = $secretaria->id;
        $this->secretaria = $secretaria->secretaria;
        $this->editModeSecretaria = true;
        $this->showModalSecretaria = true;
    }

    public function guardarSecretaria()
    {
        if (!$this->editModeSecretaria && !auth()->user()->puedeCrearSecretarias()) {
            session()->flash('error', 'No tienes permisos para crear secretarías.');
            $this->showModalSecretaria = false;
            return;
        }

        if ($this->editModeSecretaria && !auth()->user()->puedeEditarSecretarias()) {
            session()->flash('error', 'No tienes permisos para editar secretarías.');
            $this->showModalSecretaria = false;
            return;
        }

        $this->validate();

        if ($this->editModeSecretaria) {
            $secretaria = Secretaria::findOrFail($this->secretaria_id);
            $secretaria->update(['secretaria' => $this->secretaria]);
            session()->flash('message', 'Secretaría actualizada correctamente.');
        } else {
            Secretaria::create(['secretaria' => $this->secretaria]);
            session()->flash('message', 'Secretaría creada correctamente.');
        }

        $this->showModalSecretaria = false;
        $this->resetFormSecretaria();
    }

    public function eliminarSecretaria($id)
    {
        if (!auth()->user()->puedeEliminarSecretarias()) {
            session()->flash('error', 'No tienes permisos para eliminar secretarías.');
            return;
        }

        $secretaria = Secretaria::findOrFail($id);

        if ($secretaria->areas()->count() > 0) {
            session()->flash('error', 'No se puede eliminar la secretaría porque tiene áreas asociadas.');
            return;
        }

        if ($secretaria->corralones()->count() > 0) {
            session()->flash('error', 'No se puede eliminar la secretaría porque tiene corralones asociados.');
            return;
        }

        try {
            $secretaria->delete();
            session()->flash('message', 'Secretaría eliminada correctamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'No se puede eliminar la secretaría porque tiene registros asociados.');
        }
    }

    // --- CRUD Áreas ---

    public function crearArea($secretariaId = null)
    {
        if (!auth()->user()->puedeCrearSecretarias()) {
            session()->flash('error', 'No tienes permisos para crear áreas.');
            return;
        }

        $this->showModalAreas = false;
        $this->resetFormArea();
        $this->editModeArea = false;
        if ($secretariaId) {
            $this->id_secretaria = $secretariaId;
        }
        $this->showModalArea = true;
    }

    public function editarArea($id)
    {
        if (!auth()->user()->puedeEditarSecretarias()) {
            session()->flash('error', 'No tienes permisos para editar áreas.');
            return;
        }

        $area = Area::findOrFail($id);
        $this->showModalAreas = false;
        $this->area_id = $area->id;
        $this->area = $area->area;
        $this->id_secretaria = $area->id_secretaria;
        $this->editModeArea = true;
        $this->showModalArea = true;
    }

    public function guardarArea()
    {
        if (!$this->editModeArea && !auth()->user()->puedeCrearSecretarias()) {
            session()->flash('error', 'No tienes permisos para crear áreas.');
            $this->showModalArea = false;
            return;
        }

        if ($this->editModeArea && !auth()->user()->puedeEditarSecretarias()) {
            session()->flash('error', 'No tienes permisos para editar áreas.');
            $this->showModalArea = false;
            return;
        }

        $this->validate();

        if ($this->editModeArea) {
            $area = Area::findOrFail($this->area_id);
            $area->update([
                'area' => $this->area,
                'id_secretaria' => $this->id_secretaria,
            ]);
            session()->flash('message', 'Área actualizada correctamente.');
        } else {
            Area::create([
                'area' => $this->area,
                'id_secretaria' => $this->id_secretaria,
            ]);
            session()->flash('message', 'Área creada correctamente.');
        }

        $this->showModalArea = false;
        $this->resetFormArea();
    }

    public function eliminarArea($id)
    {
        if (!auth()->user()->puedeEliminarSecretarias()) {
            session()->flash('error', 'No tienes permisos para eliminar áreas.');
            return;
        }

        $area = Area::findOrFail($id);

        try {
            $area->delete();
            session()->flash('message', 'Área eliminada correctamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'No se puede eliminar el área porque tiene registros asociados.');
        }
    }

    public function cerrarModalSecretaria()
    {
        $this->showModalSecretaria = false;
        $this->resetFormSecretaria();
    }

    public function cerrarModalArea()
    {
        $this->showModalArea = false;
        $this->resetFormArea();
    }

    private function resetFormSecretaria()
    {
        $this->secretaria_id = null;
        $this->secretaria = '';
        $this->resetErrorBag();
    }

    private function resetFormArea()
    {
        $this->area_id = null;
        $this->area = '';
        $this->id_secretaria = '';
        $this->resetErrorBag();
    }
}
