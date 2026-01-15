@props(['header'])

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Corralón - Sistema de Gestión</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        /* Personalización del scrollbar para el sidebar */
        .sidebar-scroll::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar-scroll::-webkit-scrollbar-track {
            background: transparent; /* Ya está en transparente, pero asegúrate */
        }
        
        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3); /* Thumb más sutil */
            border-radius: 3px;
        }
        
        .sidebar-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5); /* Un poco más visible en hover */
        }

        /* Para Firefox */
        .sidebar-scroll {
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.3) transparent;
        }

        /* Color personalizado verde para items activos y hover */
        .bg-custom-green {
            background-color: #77BF43;
        }

        .hover-custom-green:hover {
            background-color: #92d158;
        }
    </style>
</head>

<body class="bg-gray-100">
    <div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">
        <!-- Overlay para móvil -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 bg-gray-700 bg-opacity-50 z-20 lg:hidden"
             style="display: none;">
        </div>

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="fixed lg:static inset-y-0 left-0 z-30 w-64 bg-gray-700 text-white flex flex-col transform transition-transform duration-300 ease-in-out lg:translate-x-0">
            <!-- Logo/Header -->
            <div class="p-3 border-b border-gray-600 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <!-- Logo - Ajusta la ruta según tu estructura -->
                    <img src="{{ asset('images/logo-municipalidad.png') }}" alt="Logo" class="h-12 w-12">
                    <div>
                        <h1 class="text-lg font-bold text-white">CORRALÓN</h1>
                        <p class="text-xs text-gray-400">Municipalidad de Mercedes</p>
                    </div>
                </div>
                <!-- Botón cerrar en móvil -->
                <button @click="sidebarOpen = false" class="lg:hidden text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto p-4 sidebar-scroll">
                <div class="space-y-2"> 
                    <!-- Dashboard -->
                    <a href="{{ route('dashboard') }}" 
                       wire:navigate
                       @click="sidebarOpen = false"
                       class="flex items-center px-4 py-2 rounded-lg transition-colors duration-200 {{ request()->routeIs('dashboard') ? 'bg-custom-green' : 'hover-custom-green hover:bg-gray-600' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        Dashboard
                    </a>

                    <!-- Sección Inventario -->
                    <div class="pt-4">
                        <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Inventario</p>
                        <div class="mt-2 space-y-1">
                            <a href="{{ route('insumos') }}" 
                                wire:navigate
                                @click="sidebarOpen = false"
                                class="flex items-center px-4 py-2 rounded-lg transition-colors duration-200 {{ request()->routeIs('insumos') ? 'bg-custom-green' : 'hover-custom-green hover:bg-gray-600' }}">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                                Insumos
                            </a>
                            <a href="{{ route('maquinarias') }}" 
                                wire:navigate
                                @click="sidebarOpen = false"
                               class="flex items-center px-4 py-2 rounded-lg transition-colors duration-200 {{ request()->routeIs('maquinarias') ? 'bg-custom-green' : 'hover-custom-green hover:bg-gray-600' }}">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Maquinaria
                            </a>
                            <a href="{{ route('vehiculos') }}" 
                                wire:navigate
                                @click="sidebarOpen = false"
                               class="flex items-center px-4 py-2 rounded-lg transition-colors duration-200 {{ request()->routeIs('vehiculos') ? 'bg-custom-green' : 'hover-custom-green hover:bg-gray-600' }}">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"d="M9 17a2 2 0 11-4 0 2 2 0 014 0zm10 0a2 2 0 11-4 0 2 2 0 014 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"d="M3 5h11v12H3z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"d="M14 9h4l3 3v5h-7V9z" />
                                </svg>
                                Vehículos
                            </a>
                            <a href="{{ route('categorias-insumos') }}" 
                                wire:navigate
                                @click="sidebarOpen = false"
                               class="flex items-center px-4 py-2 rounded-lg transition-colors duration-200 {{ request()->routeIs('categorias-insumos') ? 'bg-custom-green' : 'hover-custom-green hover:bg-gray-600' }}">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                                Categorías Insumos
                            </a>
                            <a href="{{ route('categorias-maquinarias') }}" 
                                wire:navigate
                                @click="sidebarOpen = false"
                               class="flex items-center px-4 py-2 rounded-lg transition-colors duration-200 {{ request()->routeIs('categorias-maquinarias') ? 'bg-custom-green' : 'hover-custom-green hover:bg-gray-600' }}">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                                Categorías Maquinarias
                            </a>
                            <a href="{{ route('depositos') }}" 
                                wire:navigate
                                @click="sidebarOpen = false"
                               class="flex items-center px-4 py-2 rounded-lg transition-colors duration-200 {{ request()->routeIs('depositos') ? 'bg-custom-green' : 'hover-custom-green hover:bg-gray-600' }}">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                Depósitos
                            </a>
                            <a href="{{ route('eventos') }}" 
                                wire:navigate
                                @click="sidebarOpen = false"
                                class="flex items-center px-4 py-2 rounded-lg transition-colors duration-200 {{ request()->routeIs('eventos') ? 'bg-custom-green' : 'hover-custom-green hover:bg-gray-600' }}">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Eventos
                            </a>
                            <a href="{{ route('transferencias-insumos') }}" 
                                wire:navigate
                                @click="sidebarOpen = false"
                               class="flex items-center px-4 py-2 rounded-lg transition-colors duration-200 {{ request()->routeIs('transferencias-insumos') ? 'bg-custom-green' : 'hover-custom-green hover:bg-gray-600' }}">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                </svg>
                                Movimientos Insumos
                            </a>
                            <a href="{{ route('transferencias-maquinarias') }}"
                                wire:navigate
                                @click="sidebarOpen = false"
                               class="flex items-center px-4 py-2 rounded-lg transition-colors duration-200 {{ request()->routeIs('transferencias-maquinarias') ? 'bg-custom-green' : 'hover-custom-green hover:bg-gray-600' }}">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                </svg>
                                Movimientos Maquinarias
                            </a>
                        </div>
                    </div>

                    <!-- Sección Recursos -->
                    <div class="pt-4">
                        <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Recursos</p>
                        <div class="mt-2 space-y-1">
                            <a href="{{ route('empleados') }}" 
                                wire:navigate
                                @click="sidebarOpen = false"
                               class="flex items-center px-4 py-2 rounded-lg transition-colors duration-200 {{ request()->routeIs('empleados') ? 'bg-custom-green' : 'hover-custom-green hover:bg-gray-600' }}">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                Empleados
                            </a>
                            <a href="{{ route('usuarios') }}" 
                                wire:navigate
                                @click="sidebarOpen = false"
                               class="flex items-center px-4 py-2 rounded-lg transition-colors duration-200 {{ request()->routeIs('usuarios') ? 'bg-custom-green' : 'hover-custom-green hover:bg-gray-600' }}">
                                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                Usuarios
                            </a>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- User Section - Footer del Sidebar -->
            <div class="p-4 border-t border-gray-600">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3 min-w-0">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center" style="background-color: #77BF43;">
                                <span class="text-sm font-semibold text-white">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </span>
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="flex-shrink-0">
                        @csrf
                        <button type="submit" 
                                class="p-2 text-red-400 hover:text-red-300 hover:bg-gray-600 rounded-lg transition-colors"
                                title="Cerrar sesión">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto w-full lg:w-auto">
            <!-- Header -->
            <header class="bg-white shadow-sm">
                <div class="px-4 sm:px-6 flex items-center">
                    <!-- Botón hamburguesa para móvil -->
                    <button @click="sidebarOpen = true" 
                            class="lg:hidden mr-4 text-gray-600 hover:text-gray-900 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </header>

            <!-- Content -->
            <div class="p-4 sm:p-6">
                {{ $slot }}
            </div>
        </main>
    </div>

    @livewireScripts
</body>
</html>