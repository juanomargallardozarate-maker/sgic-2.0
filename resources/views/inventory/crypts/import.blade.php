<x-app-layout>
    <x-slot name="title">Importación Masiva de Criptas</x-slot>

    <x-slot name="header">
        <div class="flex items-center space-x-4">
            <a href="{{ route('inventory.crypts.index') }}" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i class="fa-solid fa-arrow-left text-lg"></i>
            </a>
            <div>
                <h2 class="text-xl font-bold text-slate-800">Importación Masiva de Criptas</h2>
                <p class="text-sm text-slate-500 mt-1">Carga tu inventario existente vía CSV (Máx 10,000 registros)</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        
        {{-- Instrucciones y Plantilla --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                <i class="fa-solid fa-file-csv text-emerald-600 mr-2"></i>
                1. Descarga la Plantilla
            </h3>
            <p class="text-sm text-slate-600 mb-4">
                El sistema requiere un formato específico para crear la jerarquía automáticamente. 
                <strong>La jerarquía (Sección/Bloque/Nivel) se creará sola</strong> si no existe.
            </p>
            <a href="{{ route('inventory.crypts.import-template') }}" class="inline-flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-lg transition-colors">
                <i class="fa-solid fa-download mr-2"></i> Descargar plantilla_criptas_sgic.csv
            </a>
        </div>

        {{-- Formulario de Carga --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                <i class="fa-solid fa-cloud-arrow-up text-indigo-600 mr-2"></i>
                2. Sube tu Archivo CSV
            </h3>
            
            <form method="POST" action="{{ route('inventory.crypts.import.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="flex items-center justify-center w-full">
                    <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-64 border-2 border-slate-300 border-dashed rounded-lg cursor-pointer bg-slate-50 hover:bg-slate-100 transition-colors" id="dropzone-label">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6" id="dropzone-content">
                            <i class="fa-solid fa-cloud-arrow-up text-4xl text-slate-400 mb-3"></i>
                            <p class="mb-2 text-sm text-slate-500">
                                <span class="font-semibold text-slate-700">Haz clic para subir</span> o arrastra el archivo
                            </p>
                            <p class="text-xs text-slate-500">Solo CSV (Máx. 10MB)</p>
                        </div>
                        <input id="dropzone-file" type="file" name="file" class="hidden" accept=".csv" required />
                    </label>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const fileInput = document.getElementById('dropzone-file');
                        const dropzoneContent = document.getElementById('dropzone-content');
                        const dropzoneLabel = document.getElementById('dropzone-label');
                        
                        fileInput.addEventListener('change', function(e) {
                            const file = e.target.files[0];
                            if (file) {
                                const fileName = file.name;
                                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                                
                                dropzoneContent.innerHTML = `
                                    <i class="fa-solid fa-file-csv text-4xl text-emerald-600 mb-3"></i>
                                    <p class="text-sm font-semibold text-slate-700 mb-1">${fileName}</p>
                                    <p class="text-xs text-slate-500">${fileSize} MB</p>
                                    <p class="text-xs text-emerald-600 mt-2"><i class="fa-solid fa-check mr-1"></i>Archivo listo para subir</p>
                                `;
                                
                                dropzoneLabel.classList.remove('border-slate-300', 'bg-slate-50');
                                dropzoneLabel.classList.add('border-emerald-500', 'bg-emerald-50');
                            }
                        });
                    });
                </script>
                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                        <i class="fa-solid fa-upload mr-2"></i> Procesar Importación
                    </button>
                </div>
            </form>
        </div>

        {{-- Estadísticas de Importación --}}
        @if (session('import_stats'))
            @php $stats = session('import_stats'); @endphp
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-6 text-center">
                    <div class="text-3xl font-bold text-emerald-700 mb-1">{{ $stats['success'] }}</div>
                    <div class="text-sm text-emerald-600 font-medium">Creadas Exitosamente</div>
                </div>
                <div class="bg-amber-50 border border-amber-100 rounded-xl p-6 text-center">
                    <div class="text-3xl font-bold text-amber-700 mb-1">{{ $stats['warnings'] }}</div>
                    <div class="text-sm text-amber-600 font-medium">Omitidas (Duplicadas)</div>
                </div>
                <div class="bg-red-50 border border-red-100 rounded-xl p-6 text-center">
                    <div class="text-3xl font-bold text-red-700 mb-1">{{ $stats['errors'] }}</div>
                    <div class="text-sm text-red-600 font-medium">Errores</div>
                </div>
            </div>
        @endif

        {{-- Reporte de Errores Detallado --}}
        @if (session('import_report'))
            @php $report = session('import_report'); @endphp
            
            @if (count($report['errors']) > 0)
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                        <i class="fa-solid fa-circle-exclamation text-red-500 mr-2"></i>
                        Errores de Importación
                    </h3>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Fila</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Código Cripta</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Error</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-100">
                                @foreach ($report['errors'] as $error)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-4 py-3 text-sm text-slate-600">{{ $error['row'] }}</td>
                                        <td class="px-4 py-3 text-sm font-mono text-slate-800">{{ $error['code'] ?? 'N/A' }}</td>
                                        <td class="px-4 py-3 text-sm text-red-600">{{ $error['message'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endif
    </div>
</x-app-layout>