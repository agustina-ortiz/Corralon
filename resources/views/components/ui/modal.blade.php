{{--
    <x-ui.modal> — kit de especificaciones, spec 12.

    El estado (abierto/cerrado) vive en el componente Livewire: se envuelve en
    @if($showModal). Este componente garantiza:
      - panel anclado arriba (no centrado) que nunca supera el viewport: header
        y footer fijos, solo el body scrollea. Anclarlo arriba deja lugar abajo
        para que los desplegables de un modal chico no cuelguen a mitad de pantalla;
      - scroll-lock del fondo mientras está abierto;
      - cierre por X y Escape, los dos por la misma acción Livewire (`cerrar`),
        y Escape solo cierra el modal de más arriba (el visible de mayor
        z-index). El click en el backdrop NO cierra: un click afuera sin querer
        no puede tirar un formulario a medio cargar;
      - teclado primero: al abrir, el foco va al primer campo del body; Enter
        en un campo pasa al siguiente (en el último, guarda); F2 guarda desde
        cualquier campo. Enter en un textarea sigue siendo salto de línea y en
        un botón sigue siendo click. Los desplegables resuelven su propio Enter
        y avisan con el evento `ui-siguiente-campo` cuando eligieron algo;
      - el foco no sale del modal (x-trap) y al cerrar vuelve a quien lo abrió.

    Props:
      titulo   Título del header.
      icono    Nombre del icono ("fa-plus"). Se antepone fa-solid salvo que ya
               traiga un estilo (fa-regular, fa-brands).
      cerrar   Acción Livewire de cierre (X y Escape). Por omisión "closeModal".
      ancho    auto (por omisión, ajustado al contenido con tope de 72rem) |
               sm | md | lg | xl | 2xl | 3xl | 4xl | 5xl | 6xl.
      submit   Acción Livewire: envuelve body + footer en <form wire:submit.prevent>.
      z        50 (por omisión) | 60 | 70 para modales apilados.

    Slots: default (body), `footer` y `encabezado` (al lado del título, para
    badges de estado o el indicador de pasos).
--}}
@props([
    'titulo' => '',
    'icono' => null,
    'cerrar' => 'closeModal',
    'ancho' => 'auto',
    'submit' => null,
    'z' => 50,
])

@php
    use App\Support\Tema;
    use Illuminate\Support\Str;

    // Clases literales: nada se interpola.
    $clasesAncho = [
        'auto' => 'w-fit max-w-full md:max-w-6xl',
        'sm' => 'w-full max-w-sm',
        'md' => 'w-full max-w-md',
        'lg' => 'w-full max-w-lg',
        'xl' => 'w-full max-w-xl',
        '2xl' => 'w-full max-w-2xl',
        '3xl' => 'w-full max-w-3xl',
        '4xl' => 'w-full max-w-4xl',
        '5xl' => 'w-full max-w-5xl',
        '6xl' => 'w-full max-w-6xl',
    ][$ancho] ?? 'w-fit max-w-full md:max-w-6xl';

    $claseZ = [
        50 => 'z-50',
        60 => 'z-[60]',
        70 => 'z-[70]',
    ][(int) $z] ?? 'z-50';

    if ($icono !== null && ! Str::startsWith($icono, ['fa-solid', 'fa-regular', 'fa-brands'])) {
        $icono = 'fa-solid '.$icono;
    }

    $idTitulo = 'modal-titulo-'.Str::random(6);
@endphp

<div
    x-data="{
        cerrar() { $wire.{{ $cerrar }}() },
        // Solo cuentan los modales visibles; el de arriba es el de mayor
        // z-index (a igual z, el último del DOM).
        modalesVisibles() {
            return Array.from(document.querySelectorAll('[aria-modal=&quot;true&quot;]'))
                .filter(m => document.body.contains(m) && m.getClientRects().length > 0);
        },
        esElDeArriba() {
            const z = m => parseInt(getComputedStyle(m).zIndex, 10) || 0;
            const arriba = this.modalesVisibles()
                .reduce((tope, m) => tope === null || z(m) >= z(tope) ? m : tope, null);
            return arriba === $el;
        },
        // Quién abrió el modal, para devolverle el foco al cerrar.
        anterior: null,
        init() {
            this.anterior = document.activeElement;
            document.body.classList.add('overflow-hidden');
            this.enfocarPrimero();
        },
        // ----- Teclado primero ------------------------------------------------
        // Los controles del body que reciben foco, en orden de documento y
        // solo los visibles (el buscador de un desplegable cerrado no cuenta).
        campos() {
            const selector = 'input:not([type=hidden]):not([disabled]), textarea:not([disabled]), select:not([disabled]), button[aria-haspopup=listbox]:not([disabled]), [data-campo-foco]';
            return Array.from($refs.cuerpo.querySelectorAll(selector))
                .filter(el => el.tabIndex >= 0 && el.getClientRects().length > 0);
        },
        enfocarPrimero() {
            this.$nextTick(() => {
                if (document.activeElement && $refs.cuerpo.contains(document.activeElement)) return;
                this.campos()[0]?.focus();
            });
        },
        // Mueve el foco al primer campo posterior a `desde` (sin contar los
        // que estén adentro de `desde`). Devuelve false si era el último.
        siguienteCampo(desde) {
            const siguiente = this.campos().find(campo =>
                campo !== desde
                && ! desde.contains(campo)
                && (desde.compareDocumentPosition(campo) & Node.DOCUMENT_POSITION_FOLLOWING)
            );
            if (! siguiente) return false;
            siguiente.focus();
            if (siguiente.select && ['text', 'number', 'search', 'tel', 'email', 'url'].includes(siguiente.type)) siguiente.select();
            return true;
        },
        // Enter en un campo del body: al siguiente; en el último, guardar.
        enterTabea(evento) {
            if (evento.defaultPrevented || evento.shiftKey || evento.ctrlKey || evento.altKey || evento.isComposing) return;
            const campo = evento.target;
            if (! (campo instanceof Element) || ! $refs.cuerpo.contains(campo)) return;
            if (['TEXTAREA', 'BUTTON', 'A', 'SELECT'].includes(campo.tagName) || campo.type === 'submit') return;
            evento.preventDefault();
            if (! this.siguienteCampo(campo)) this.guardar();
        },
        // Después de guardar, si volvieron errores, el foco va al primero
        // ([aria-invalid=true]). Se mira unos segundos: la respuesta de
        // Livewire llega cuando llega.
        enfocarError() {
            const inicio = Date.now();
            const mirar = () => {
                if (! document.body.contains($el)) return;
                const campo = $refs.cuerpo.querySelector('[aria-invalid=true]');
                if (campo) { campo.focus(); return }
                if (Date.now() - inicio < 4000) setTimeout(mirar, 150);
            };
            setTimeout(mirar, 300);
        },
        // Con submit, envía el form; sin form, el botón marcado data-atajo-guardar.
        guardar() {
            if ($refs.formulario) { $refs.formulario.requestSubmit(); return }
            $el.querySelector('[data-atajo-guardar]')?.click();
        },
        destroy() {
            // Solo se libera el scroll cuando no queda ningún OTRO modal abierto.
            const otros = this.modalesVisibles().filter(modal => modal !== $el);
            if (otros.length === 0) {
                document.body.classList.remove('overflow-hidden');
            }

            // Con el modal ya fuera, el foco vuelve a quien lo abrió (si sigue
            // en la página) en vez de caer en el <body>.
            const anterior = this.anterior;
            setTimeout(() => { if (anterior?.isConnected) anterior.focus() });
        },
    }"
    x-trap.inert="true"
    x-on:keydown.escape.window="if (esElDeArriba()) cerrar()"
    x-on:keydown.window="if ($event.key === 'F2' && esElDeArriba()) { $event.preventDefault(); guardar() }"
    x-on:keydown.enter="enterTabea($event)"
    x-on:submit="enfocarError()"
    x-on:ui-siguiente-campo="$event.stopPropagation(); if (! siguienteCampo($event.target)) $event.target.blur()"
    role="dialog"
    aria-modal="true"
    aria-labelledby="{{ $idTitulo }}"
    class="fixed inset-0 {{ $claseZ }} flex items-start justify-center p-4 sm:py-10 bg-gray-900/50 backdrop-blur-sm"
>
    <div
        {{ $attributes->class(['flex flex-col max-h-full bg-white rounded-2xl shadow-2xl text-left', $clasesAncho]) }}
        x-on:click.stop
        {{-- Guarda: el panel nunca scrollea (solo el body). Un control
             sr-only mal ubicado puede hacerlo scrollear al enfocarlo. --}}
        x-on:scroll.self="$el.scrollTop = 0; $el.scrollLeft = 0"
    >
        {{-- Header: siempre visible --}}
        <div class="flex items-center justify-between gap-4 px-4 py-4 sm:px-6 rounded-t-2xl shrink-0 {{ Tema::encabezadoModal() }}">
            <div class="flex min-w-0 flex-wrap items-center gap-x-3 gap-y-1">
                <h3 id="{{ $idTitulo }}" class="flex items-center gap-2 text-lg sm:text-xl font-semibold truncate">
                    @if($icono)
                        <i class="{{ $icono }} {{ Tema::iconoModal() }} text-base" aria-hidden="true"></i>
                    @endif
                    <span class="truncate">{{ $titulo }}</span>
                </h3>
                @isset($encabezado)
                    <div class="flex items-center gap-2 shrink-0">{{ $encabezado }}</div>
                @endisset
            </div>
            <button type="button" x-on:click="cerrar()"
                    class="shrink-0 rounded-lg p-1.5 transition-colors focus:outline-none focus:ring-2 {{ Tema::cerrarModal() }}"
                    aria-label="Cerrar" title="Cerrar (Esc)">
                <i class="fa-solid fa-xmark text-xl leading-none w-6 h-6 inline-flex items-center justify-center" aria-hidden="true"></i>
            </button>
        </div>

        @if($submit)
            {{-- display: contents deja que el body siga siendo el único que scrollea --}}
            <form wire:submit.prevent="{{ $submit }}" x-ref="formulario" class="contents">
        @endif

            {{-- Body: lo único que scrollea --}}
            <div x-ref="cuerpo" class="relative flex-1 min-h-0 overflow-y-auto scroll-fino px-4 py-5 sm:px-6 space-y-4 text-gray-800">
                {{ $slot }}
            </div>

            {{-- Footer: siempre visible. Envuelve en vez de apilar. --}}
            @isset($footer)
                <div {{ $footer->attributes->class(['flex flex-wrap items-center justify-end gap-3 px-4 py-4 sm:px-6 border-t border-gray-200 bg-gray-50 rounded-b-2xl shrink-0']) }}>
                    {{ $footer }}
                </div>
            @endisset

        @if($submit)
            </form>
        @endif
    </div>
</div>
