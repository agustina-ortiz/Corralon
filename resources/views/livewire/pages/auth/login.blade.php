<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(
            default: route('dashboard', absolute: false),
            navigate: true
        );
    }
};
?>

<div class="min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-md bg-white/90 backdrop-blur rounded-2xl shadow-2xl p-8 border border-gray-100">

        <!-- Header -->
        <div class="text-center">  
            <h1 class="text-2xl font-bold text-gray-800">
                Sistema de Gestión de Stock
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                Ingreso al sistema
            </p>
            <div class="w-16 h-1 bg-[#c7d100] mx-auto mt-3 rounded-full"></div>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <!-- Form -->
        <form wire:submit="login" class="space-y-4 mt-6">

            <!-- Email -->
            <div>
                <x-input-label for="email" value="Email" />
                <x-text-input
                    wire:model="form.email"
                    id="email"
                    type="email"
                    class="block mt-1 w-full rounded-lg border-gray-300 focus:border-[#81af00] focus:ring-[#81af00]"
                    required
                    autofocus
                />
                <x-input-error :messages="$errors->get('form.email')" class="mt-1" />
            </div>

            <!-- Password -->
            <div>
                <x-input-label for="password" value="Contraseña" />
                <x-text-input
                    wire:model="form.password"
                    id="password"
                    type="password"
                    class="block mt-1 w-full rounded-lg border-gray-300 focus:border-[#81af00] focus:ring-[#81af00]"
                    required
                />
                <x-input-error :messages="$errors->get('form.password')" class="mt-1" />
            </div>

            <!-- Remember -->
            <div class="flex items-center justify-between">
                <label class="flex items-center text-sm text-gray-600">
                    <input
                        wire:model="form.remember"
                        type="checkbox"
                        class="rounded border-gray-300 text-[#81af00] focus:ring-[#81af00]"
                    >
                    <span class="ml-2">Recordarme</span>
                </label>

                @if (Route::has('password.request'))
                    <a
                        href="{{ route('password.request') }}"
                        wire:navigate
                        class="text-sm text-[#81af00] hover:underline"
                    >
                        ¿Olvidaste tu contraseña?
                    </a>
                @endif
            </div>

            <!-- Button -->
            <button
                type="submit"
                class="w-full mt-4 bg-gradient-to-r from-[#81af00] to-[#c7d100] hover:opacity-90 text-white font-semibold py-2 px-4 rounded-lg transition shadow-md"
            >
                Ingresar
            </button>
        </form>
    </div>
</div>
