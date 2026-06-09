{{--
    Preview de archivos seleccionados antes de subir.
    Muestra miniatura para imágenes (JPG/PNG) e ícono para PDF, cada uno con
    botón de eliminar.

    Variables esperadas:
      $files        array de TemporaryUploadedFile (ej: $comprobantes)
      $removeMethod string nombre del método Livewire que quita por índice
--}}
@if(count($files) > 0)
    <div class="mt-3 grid grid-cols-2 sm:grid-cols-3 gap-3">
        @foreach($files as $index => $file)
            @php
                $nombre = $file->getClientOriginalName();
                $ext = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
                $esImagen = in_array($ext, ['jpg', 'jpeg', 'png']);
            @endphp
            <div wire:key="preview-{{ $removeMethod }}-{{ $index }}" class="relative border border-gray-200 rounded-xl overflow-hidden bg-gray-50">
                <button type="button"
                        wire:click="{{ $removeMethod }}({{ $index }})"
                        title="Eliminar archivo"
                        class="absolute top-1 right-1 z-10 bg-red-600 hover:bg-red-700 text-white rounded-full w-6 h-6 flex items-center justify-center shadow">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>

                @if($esImagen)
                    <img src="{{ $file->temporaryUrl() }}" alt="{{ $nombre }}" class="w-full h-24 object-cover">
                @else
                    <div class="w-full h-24 flex items-center justify-center bg-red-50">
                        <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                @endif

                <p class="px-2 py-1 text-[11px] text-gray-600 truncate" title="{{ $nombre }}">{{ $nombre }}</p>
            </div>
        @endforeach
    </div>
@endif
