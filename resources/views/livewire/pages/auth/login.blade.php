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

<div class="min-h-screen flex">

    {{-- Panel izquierdo decorativo --}}
    <div class="hidden lg:flex lg:w-1/2 flex-col justify-between px-16 py-14 relative bg-transparent">

        {{-- Patrón de puntos decorativo --}}
        <div class="absolute inset-0 opacity-10 pointer-events-none"
             style="background-image: radial-gradient(circle, #c7d100 1px, transparent 1px); background-size: 24px 24px;">
        </div>

        {{-- Formas geométricas --}}
        <div class="absolute top-10 right-10 w-24 h-24 border-2 border-[#77BF43]/40 rounded-2xl rotate-12 pointer-events-none"></div>
        <div class="absolute bottom-20 left-8 w-16 h-16 border-2 border-[#c7d100]/30 rounded-full pointer-events-none"></div>
        <div class="absolute bottom-32 right-16 w-10 h-10 bg-[#77BF43]/20 rounded-lg rotate-45 pointer-events-none"></div>

        {{-- Contenido superior --}}
        <div class="relative z-10">
            <img src="{{ asset('images/logo-municipalidad.png') }}" alt="Municipalidad de Mercedes" class="h-20 w-auto mb-8 drop-shadow-lg">

            <h2 class="text-4xl font-bold text-white leading-snug">
                Sistema de Gestión<br>
                <span class="text-white/80">de Stock</span>
            </h2>
            <p class="mt-4 text-white/75 text-sm leading-relaxed max-w-xs">
                Administración de insumos, maquinarias, vehículos y recursos de los corralones municipales.
            </p>
        </div>

        {{-- Lista de features --}}
        <div class="relative z-10 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586 4.707 9.293a1 1 0 00-1.414 1.414l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <span class="text-white/85 text-sm">Control de stock en tiempo real</span>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586 4.707 9.293a1 1 0 00-1.414 1.414l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <span class="text-white/85 text-sm">Múltiples corralones y depósitos</span>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586 4.707 9.293a1 1 0 00-1.414 1.414l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <span class="text-white/85 text-sm">Alertas y reportes automáticos</span>
            </div>

            <div class="pt-6 border-t border-white/25">
                <p class="text-xs text-white/50">Municipalidad de Mercedes &mdash; Uso interno</p>
            </div>
        </div>
    </div>

    {{-- Panel derecho — formulario --}}
    <div class="w-full lg:w-1/2 flex flex-col justify-center items-center px-10 py-14 bg-transparent">

        {{-- Header mobile --}}
        <div class="lg:hidden text-center mb-10">
            <img src="{{ asset('images/logo-municipalidad.png') }}" alt="Municipalidad de Mercedes" class="h-16 w-auto mx-auto mb-4 drop-shadow">
            <h1 class="text-xl font-bold text-gray-800">Sistema de Gestión de Stock</h1>
        </div>

        {{-- Título del form --}}
        <div class="mb-8 w-1/2">
            <h3 class="text-3xl font-bold text-gray-800">Bienvenido</h3>
            <p class="text-sm text-gray-500 mt-1">Ingresá tus credenciales para continuar</p>
            <div class="w-10 h-1 bg-[#77BF43] mt-3 rounded-full"></div>
        </div>

        {{-- Session Status --}}
        <x-auth-session-status class="mb-4 w-1/2" :status="session('status')" />

        {{-- Form --}}
        <form wire:submit="login" class="space-y-5 w-1/2">

            {{-- Email --}}
            <div>
                <x-input-label for="email" value="Correo electrónico" />
                <div class="relative mt-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <x-text-input
                        wire:model="form.email"
                        id="email"
                        type="email"
                        class="block w-full pl-9 rounded-lg border-gray-300 focus:border-[#77BF43] focus:ring-[#77BF43]"
                        required
                        autofocus
                        placeholder="usuario@ejemplo.com"
                    />
                </div>
                <x-input-error :messages="$errors->get('form.email')" class="mt-1" />
            </div>

            {{-- Password --}}
            <div x-data="{ show: false }">
                <x-input-label for="password" value="Contraseña" />
                <div class="relative mt-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <x-text-input
                        wire:model="form.password"
                        id="password"
                        x-bind:type="show ? 'text' : 'password'"
                        class="block w-full pl-9 pr-10 rounded-lg border-gray-300 focus:border-[#77BF43] focus:ring-[#77BF43]"
                        required
                        placeholder="••••••••"
                    />
                    <button type="button" x-on:click="show = !show"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                        <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="show" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('form.password')" class="mt-1" />
            </div>

            {{-- Remember + Forgot --}}
            <div class="flex items-center justify-between">
                <label class="flex items-center text-sm text-gray-600 cursor-pointer select-none">
                    <input
                        wire:model="form.remember"
                        type="checkbox"
                        class="rounded border-gray-300 text-[#77BF43] focus:ring-[#77BF43]"
                    >
                    <span class="ml-2">Recordarme</span>
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" wire:navigate
                       class="text-sm text-[#77BF43] hover:text-[#5a9a2e] hover:underline font-medium transition-colors">
                        Olvidé mi contraseña
                    </a>
                @endif
            </div>

            {{-- Submit --}}
            <button
                type="submit"
                class="w-full mt-2 flex items-center justify-center gap-2 bg-gradient-to-r from-[#77BF43] to-[#81af00] hover:from-[#6aad38] hover:to-[#739d00] text-white font-semibold py-2.5 px-4 rounded-lg transition-all shadow-md hover:shadow-lg active:scale-[0.98]"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
                Ingresar al sistema
            </button>
        </form>

        <p class="mt-10 text-xs text-gray-400 w-1/2">
            Municipalidad de Mercedes &mdash; Uso exclusivo interno
        </p>
    </div>
</div>
