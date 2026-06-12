<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\EmpleadoMunicipal;

class AbmEmpleados extends Component
{
    use WithPagination;

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

    public function render()
    {
        $empleados = EmpleadoMunicipal::query()
            ->activos()
            ->when($this->search, function ($query) {
                $term = '%' . $this->search . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('NOMBRE', 'like', $term)
                      ->orWhere('LEGAJO', 'like', $term)
                      ->orWhere('DNI', 'like', $term);
                });
            })
            ->orderBy('NOMBRE')
            ->paginate(10);

        return view('livewire.abm-empleados', [
            'empleados' => $empleados,
        ])->layout('layouts.app', [
            'header' => 'Empleados'
        ]);
    }
}
