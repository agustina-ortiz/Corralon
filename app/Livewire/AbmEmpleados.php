<?php

namespace App\Livewire;

use App\Livewire\Traits\OrdenaPorColumna;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\EmpleadoMunicipal;

class AbmEmpleados extends Component
{
    use WithPagination;
    use OrdenaPorColumna;

    public $search = '';

    public function mount()
    {
        // Verificar que el usuario esté autenticado
        if (!auth()->check()) {
            return redirect()->route('login');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    protected function columnasOrdenables(): array
    {
        return [
            'legajo' => 'LEGAJO',
            'nombre' => 'NOMBRE',
            'dni' => 'DNI',
            'categoria' => 'CATEGORIA',
            'ingreso' => 'FECING',
        ];
    }

    protected function ordenInicial(): array
    {
        return ['nombre', 'asc'];
    }

    public function render()
    {
        $empleados = $this->aplicarOrden(EmpleadoMunicipal::query()
            ->activos()
            ->when($this->search, function ($query) {
                $term = '%' . $this->search . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('NOMBRE', 'like', $term)
                      ->orWhere('LEGAJO', 'like', $term)
                      ->orWhere('DNI', 'like', $term);
                });
            })
        )->paginate(10);

        return view('livewire.abm-empleados', [
            'empleados' => $empleados,
        ])->layout('layouts.app', [
            'header' => 'Empleados'
        ]);
    }
}
