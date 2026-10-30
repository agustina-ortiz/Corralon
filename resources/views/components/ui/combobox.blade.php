{{--
    <x-ui.combobox> — kit de especificaciones, spec 16, adaptado al Corralón
    (mismo aspecto que <x-ui.input>: rounded-xl, borde gris, texto de 16px).

    Toda lista de opciones de datos de un formulario. Solo dentro de una vista
    Livewire. Va adentro de <x-ui.campo>: con `nombre` el botón lleva
    id="campo-<nombre>" (el rótulo lo enfoca), borde rojo + aria-invalid con
    error (el modal lleva el foco ahí) y aria-describedby al error/ayuda.

    Dos modos:

    1) Lista (por omisión): las opciones viajan enteras y se filtran en el
       navegador. Respeta wire:model y wire:model.live vía @entangle.

         <x-ui.combobox nombre="id_categoria" wire:model="id_categoria"
                        :opciones="$categorias" etiqueta="nombre" placeholder="Seleccione una categoría" />

    2) Servidor (`buscar-en`): para listas que pueden superar ~200 renglones
       (insumos, vehículos, empleados). El buscador del panel queda enlazado a
       esa propiedad con wire:model.live y las opciones son los resultados que
       arma el componente en render(). Al elegir llama a `accion` con el valor
       (o, sin `accion`, lo pone en el wire:model). Al abrirse dispara el
       evento `combobox-abierto` (x-on:combobox-abierto="$wire.mostrarLista()"
       para las búsquedas que cargan la lista recién al enfocarse).

         <x-ui.combobox nombre="search_insumo" buscar-en="search_insumo" accion="seleccionarInsumo"
                        :opciones="$opciones" x-on:combobox-abierto="$wire.mostrarLista()" />

    Props:
      opciones     array asociativo valor => texto, lista de modelos/arrays
                   (usa `clave` y `etiqueta`), lista de enums con etiqueta(), o
                   lista de cadenas. Los arrays pueden traer además, para una
                   opción de dos líneas: `detalle` (segunda línea), `insignia`
                   (chip azul antes del detalle), `cifra` + `subcifra` (a la
                   derecha: stock y unidad), `cifra_color` (verde | naranja),
                   `nota` (al lado del texto) y `deshabilitada`.
      nombre       Propiedad Livewire del campo (id, error, aria).
      placeholder  Texto cuando no hay nada elegido.
      vacio        Texto de la opción "sin valor" (''). Sin vacio no hay opción
                   vacía, pero la X deja el campo en ''.
      ordenar      true: alfabético transliterando tildes. false para órdenes
                   semánticos. En modo servidor no ordena (ordena la consulta).
      clave / etiqueta   Campos de las opciones modelo/array (id / nombre).
      disabled     Deshabilita el control.
      buscar       true fuerza el buscador (por omisión, con más de 10).
      limpiable    false saca la X.
      buscar-en    Modo servidor: propiedad Livewire del texto buscado.
      accion       Modo servidor: método Livewire que recibe el valor elegido.
      sin-resultados  Modo servidor: texto con búsqueda y sin resultados.

    El vacío es '' (no null), como el <option value=""> de los <select> que
    reemplaza. Teclado: flechas / Enter / Escape (Escape con el panel cerrado
    sigue cerrando el modal); escribir sobre el botón abre el buscador con esa
    letra. Al elegir con Enter avisa al modal (`ui-siguiente-campo`) para que
    el foco pase al campo siguiente (en modo servidor no: la acción cambia el
    formulario). Con más de 200 opciones y APP_DEBUG deja un aviso en el log.

    Las opciones viajan en data-opciones y Alpine las relee cuando Livewire
    cambia ese atributo (combo dependiente, resultados del servidor).
--}}
@props([
    'opciones' => [],
    'nombre' => null,
    'placeholder' => null,
    'vacio' => null,
    'ordenar' => true,
    'clave' => 'id',
    'etiqueta' => 'nombre',
    'disabled' => false,
    'buscar' => null,
    'limpiable' => true,
    'buscarEn' => null,
    'accion' => null,
    'sinResultados' => 'No hay resultados que coincidan con la búsqueda',
])

@php
    use App\Support\Tema;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Str;

    $servidor = $buscarEn !== null;
    $items = $opciones instanceof \Illuminate\Support\Enumerable ? $opciones->all() : (array) $opciones;
    $esLista = array_is_list($items);
    $extras = ['detalle', 'insignia', 'cifra', 'subcifra', 'cifra_color', 'nota', 'deshabilitada'];

    $normalizadas = [];
    $rica = false;
    foreach ($items as $indice => $item) {
        $extra = [];
        if ($item instanceof \BackedEnum) {
            $valor = $item->value;
            $texto = method_exists($item, 'etiqueta') ? $item->etiqueta() : $item->name;
        } elseif (is_array($item) || is_object($item)) {
            $valor = data_get($item, $clave);
            $texto = data_get($item, $etiqueta);
            foreach ($extras as $campo) {
                if (is_array($item) && isset($item[$campo])) {
                    $extra[$campo] = $item[$campo];
                }
            }
        } elseif ($esLista) {
            $valor = $item;
            $texto = $item;
        } else {
            $valor = $indice;
            $texto = $item;
        }

        $rica = $rica || array_diff_key($extra, ['deshabilitada' => true, 'nota' => true]) !== [];
        $normalizadas[] = ['valor' => $valor, 'texto' => (string) $texto] + $extra;
    }

    if ($ordenar && ! $servidor) {
        usort($normalizadas, fn ($a, $b) => strnatcasecmp(Str::ascii($a['texto']), Str::ascii($b['texto'])));
    }

    if (config('app.debug') && count($normalizadas) > 200) {
        Log::warning(sprintf(
            '<x-ui.combobox wire:model="%s"> recibió %d opciones: con más de 200 va el modo servidor (buscar-en).',
            $attributes->wire('model')->value() ?: $nombre ?: '?',
            count($normalizadas),
        ));
    }

    $conModelo = (string) $attributes->wire('model')->value() !== '';
    $conBuscador = $servidor || ($buscar ?? count($normalizadas) > 10);
    $placeholder ??= 'Seleccionar...';
    $idLista = 'combobox-'.Str::random(6);

    $conError = $nombre !== null && isset($errors) && $errors->has($nombre);
    $describe = $nombre === null ? null : trim(($conError ? 'error-'.$nombre.' ' : '').'ayuda-'.$nombre);
@endphp

<div
    x-data="{
        @if($conModelo)
        valor: @entangle($attributes->wire('model')),
        @else
        valor: null,
        @endif
        servidor: @js($servidor),
        accion: @js($accion),
        buscarEn: @js($buscarEn),
        abierto: false,
        busqueda: '',
        resaltado: -1,
        version: 0,
        temporizador: null,
        init() {
            // Livewire cambia data-opciones al re-renderizar: se relee.
            new MutationObserver(() => this.version++).observe($el, { attributes: true, attributeFilter: ['data-opciones'] });
        },
        get opciones() { this.version; return JSON.parse($el.dataset.opciones) },
        normalizar(texto) { return String(texto).normalize('NFD').replace(/\p{Diacritic}/gu, '').toLowerCase() },
        get filtradas() {
            if (this.servidor) return this.opciones;
            const q = this.normalizar(this.busqueda.trim());
            return q ? this.opciones.filter(o => this.normalizar(o.texto).includes(q)) : this.opciones;
        },
        get seleccionada() {
            if (this.valor === null || this.valor === undefined || this.valor === '') return null;
            return this.opciones.find(o => String(o.valor) === String(this.valor)) ?? null;
        },
        get vacio() { return this.seleccionada === null },
        // El panel se posiciona con `fixed` contra el botón: así sobresale del
        // modal en vez de quedar recortado por el body, que scrollea.
        panel: { top: 0, left: 0, width: 0, arriba: false, max: null },
        posicionar() {
            const r = this.$refs.boton.getBoundingClientRect();
            // Alto natural del panel (buscador + lista), sin el tope de max.
            const lista = this.$refs.lista;
            const alto = (this.$refs.buscador?.parentElement.offsetHeight ?? 0) + Math.min(lista?.scrollHeight ?? 280, lista ? parseFloat(getComputedStyle(lista).maxHeight) || 280 : 280) + 2;
            const abajo = window.innerHeight - r.bottom - 12;
            const arriba = r.top - 12;
            // Abajo si entra; si no, arriba si entra; si no entra en
            // ninguno, del lado con más lugar y achicado a ese lugar.
            this.panel.arriba = alto > abajo && (alto <= arriba || arriba > abajo);
            const lugar = this.panel.arriba ? arriba : abajo;
            this.panel.max = alto > lugar ? lugar : null;
            this.panel.top = this.panel.arriba ? r.top - Math.min(alto, lugar) - 4 : r.bottom + 4;
            this.panel.left = r.left;
            this.panel.width = r.width;
        },
        abrir(tecla = null) {
            if ($el.dataset.disabled === '1') return;
            this.abierto = true;
            // Modo servidor: el texto vive acá y se manda con $wire.set (un
            // wire:model.live en el buscador perdía lo tipeado mientras volvía
            // el pedido que carga la lista).
            this.busqueda = this.servidor ? String($wire.get(this.buscarEn) ?? '') : '';
            if (tecla !== null) { this.busqueda = tecla; this.enviarBusqueda() }
            this.resaltado = Math.max(0, this.filtradas.findIndex(o => this.seleccionada && String(o.valor) === String(this.seleccionada.valor)));
            if (this.servidor) this.$dispatch('combobox-abierto');
            this.$nextTick(() => {
                this.posicionar();
                const buscador = this.$refs.buscador;
                (buscador ?? this.$refs.boton)?.focus();
                this.enfocarResaltado();
            });
        },
        cerrar() { this.abierto = false; if (! this.servidor) this.busqueda = ''; this.resaltado = -1 },
        alternar() { this.abierto ? this.cerrar() : this.abrir() },
        elegir(opcion) {
            if (opcion.deshabilitada) return;
            this.cerrar();
            this.$refs.boton.focus();
            if (this.accion) { $wire.call(this.accion, opcion.valor); return }
            this.valor = opcion.valor;
        },
        limpiar() { this.valor = ''; this.cerrar(); this.$refs.boton.focus() },
        // Escribir sobre el botón abre el buscador con esa letra; las que
        // llegan antes de que el foco pase al buscador se le suman.
        teclaEnBoton(evento) {
            if (evento.key.length !== 1 || evento.ctrlKey || evento.metaKey || evento.altKey || evento.key === ' ') return;
            if (! @js($conBuscador)) return;
            evento.preventDefault();
            if (! this.abierto) { this.abrir(evento.key); return }
            this.busqueda += evento.key;
            this.resaltado = 0;
            this.$refs.buscador?.focus();
            this.enviarBusqueda();
        },
        // Modo servidor: manda el texto a la propiedad de búsqueda (300 ms
        // después de la última tecla).
        enviarBusqueda() {
            if (! this.servidor) return;
            clearTimeout(this.temporizador);
            this.temporizador = setTimeout(() => $wire.set(this.buscarEn, this.busqueda), 300);
        },
        mover(delta) {
            if (! this.abierto) { this.abrir(); return }
            const n = this.filtradas.length;
            if (n === 0) return;
            let siguiente = this.resaltado;
            for (let i = 0; i < n; i++) {
                siguiente = (siguiente + delta + n) % n;
                if (! this.filtradas[siguiente].deshabilitada) break;
            }
            this.resaltado = siguiente;
            this.enfocarResaltado();
        },
        enfocarResaltado() {
            this.$refs.lista?.querySelectorAll('[data-opcion]')[this.resaltado]?.scrollIntoView({ block: 'nearest' });
        },
        confirmar() {
            if (! this.abierto) { this.abrir(); return }
            const opcion = this.filtradas[this.resaltado];
            if (! opcion || opcion.deshabilitada) return;
            this.elegir(opcion);
            // Elegido con el teclado: el modal pasa el foco al campo siguiente.
            if (! this.servidor) this.$dispatch('ui-siguiente-campo');
        },
    }"
    x-on:keydown.down.prevent="mover(1)"
    x-on:keydown.up.prevent="mover(-1)"
    x-on:keydown.enter.prevent="confirmar()"
    x-on:keydown.escape="if (abierto) { $event.stopPropagation(); cerrar(); $refs.boton.focus() }"
    x-on:click.outside="cerrar()"
    x-on:scroll.window.capture="if (abierto && ! $refs.panel.contains($event.target)) posicionar()"
    x-on:resize.window="if (abierto) posicionar()"
    data-opciones="{{ json_encode($normalizadas, JSON_UNESCAPED_UNICODE) }}"
    data-disabled="{{ $disabled ? '1' : '0' }}"
    {{ $attributes->whereDoesntStartWith('wire:model')->class(['relative w-full']) }}
>
    {{-- Control --}}
    <button type="button" x-ref="boton" x-on:click="alternar()" x-on:keydown="teclaEnBoton($event)"
            @if($nombre) id="campo-{{ $nombre }}" aria-describedby="{{ $describe }}" @endif
            @if($conError) aria-invalid="true" @endif
            :aria-expanded="abierto" aria-haspopup="listbox" aria-controls="{{ $idLista }}"
            @disabled($disabled)
            class="{{ Tema::controlCombobox($conError) }}">
        @if($servidor)
            <i class="fa-solid fa-magnifying-glass shrink-0 text-gray-400" aria-hidden="true"></i>
        @endif
        <span class="flex-1 truncate" :class="vacio && 'text-gray-400'"
              x-text="seleccionada ? seleccionada.texto : @js($placeholder)">{{ $placeholder }}</span>
        @if(! $disabled && $limpiable && ! $servidor)
            <span x-show="! vacio" x-cloak x-on:click.stop="limpiar()" role="button" tabindex="-1"
                  class="flex shrink-0 items-center rounded p-0.5 text-gray-400 hover:text-gray-600" aria-label="Quitar la selección">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </span>
        @endif
        <i class="fa-solid fa-chevron-down shrink-0 text-xs text-gray-400 transition-transform" :class="abierto && 'rotate-180'" aria-hidden="true"></i>
    </button>

    {{-- Desplegable: fixed para que no lo recorte el modal (z sobre todo) --}}
    <div x-ref="panel" x-show="abierto" x-cloak x-transition.opacity.duration.100ms
         :style="`top: ${panel.top}px; left: ${panel.left}px; width: ${panel.width}px` + (panel.max ? `; max-height: ${panel.max}px` : '')"
         class="fixed z-[80] flex flex-col rounded-xl border border-gray-200 bg-white shadow-lg">
        @if($conBuscador)
            <div class="shrink-0 border-b border-gray-200 p-2">
                <input type="text" x-ref="buscador" autocomplete="off" placeholder="Buscar..."
                       x-model="busqueda" x-on:input="resaltado = 0; enviarBusqueda()"
                       class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:outline-none {{ Tema::anilloFoco() }}">
            </div>
        @endif

        {{-- Tope de 7 filas: con más, la lista se iría de la pantalla --}}
        <ul x-ref="lista" id="{{ $idLista }}" role="listbox"
            class="{{ $rica ? 'max-h-80' : 'max-h-[17.5rem]' }} min-h-0 overflow-y-auto scroll-fino py-1 text-sm">
            @if($vacio !== null)
                <li role="option" x-on:click="limpiar()" :aria-selected="vacio"
                    class="cursor-pointer px-4 py-2 text-gray-500 hover:bg-gray-100"
                    :class="vacio && 'font-medium'">
                    {{ $vacio }}
                </li>
            @endif

            <template x-for="(opcion, indice) in filtradas" :key="String(opcion.valor)">
                <li role="option" data-opcion
                    x-on:click="elegir(opcion)"
                    x-on:mouseenter="if (! opcion.deshabilitada) resaltado = indice"
                    :aria-selected="seleccionada && String(seleccionada.valor) === String(opcion.valor)"
                    :aria-disabled="opcion.deshabilitada ? 'true' : null"
                    class="px-4 py-2 text-gray-800 {{ $rica ? 'border-b border-gray-100 last:border-b-0' : '' }}"
                    :class="{
                        '{{ Tema::acentoFondoSuave() }}': resaltado === indice,
                        'font-medium': seleccionada && String(seleccionada.valor) === String(opcion.valor),
                        'cursor-pointer': ! opcion.deshabilitada,
                        'opacity-50 cursor-not-allowed': opcion.deshabilitada,
                    }">
                    @if($rica)
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 font-medium text-gray-900">
                                    <span x-text="opcion.texto"></span>
                                    <span x-show="opcion.nota" class="text-xs font-normal text-blue-600" x-text="opcion.nota"></span>
                                </div>
                                <div x-show="opcion.insignia || opcion.detalle" class="mt-1 flex flex-wrap items-center gap-2 text-xs text-gray-500">
                                    <span x-show="opcion.insignia" x-text="opcion.insignia"
                                          class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium whitespace-nowrap {{ Tema::insigniaOpcion() }}"></span>
                                    <span x-show="opcion.insignia && opcion.detalle" aria-hidden="true">•</span>
                                    <span x-text="opcion.detalle"></span>
                                </div>
                            </div>
                            <div x-show="opcion.cifra !== undefined" class="ml-3 shrink-0 text-right">
                                <div class="font-semibold" :class="opcion.cifra_color === 'naranja' ? 'text-orange-600' : 'text-green-600'" x-text="opcion.cifra"></div>
                                <div class="text-xs text-gray-400" x-text="opcion.subcifra"></div>
                            </div>
                        </div>
                    @else
                        <span class="flex items-center gap-2">
                            <span x-text="opcion.texto"></span>
                            <span x-show="opcion.nota" class="text-xs text-blue-600" x-text="opcion.nota"></span>
                        </span>
                    @endif
                </li>
            </template>

            @if($servidor)
                <li wire:loading class="px-4 py-2 text-gray-400">Buscando...</li>
                <li x-show="filtradas.length === 0" x-cloak wire:loading.remove class="px-4 py-2 text-gray-400">
                    <span x-text="busqueda.trim() === '' ? 'No hay opciones' : @js($sinResultados)"></span>
                </li>
            @else
                <li x-show="filtradas.length === 0" x-cloak class="px-4 py-2 text-gray-400">
                    <span x-text="opciones.length === 0 ? 'No hay opciones' : 'No hay resultados que coincidan con la búsqueda'"></span>
                </li>
            @endif
        </ul>
    </div>
</div>
