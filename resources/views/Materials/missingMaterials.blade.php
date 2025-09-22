@extends('admin.layouts.app')
@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Materiales Faltantes</h1>
        </div>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Inicio</a></li>
                <li class="breadcrumb-item">Materiales Faltantes</li>
                <li class="breadcrumb-item active" aria-current="page">Filtrar Registros</li>
            </ol>
        </nav>
        {{-- mensaje de respuesta --}}
        <div id="responseMsg"></div>

        <div class="container mt-4 py-4 col-12 col-md-6 bg-white shadow-sm rounded">
            <form id="excelUploadForm" method="POST" data-url="{{ route('material.excelInput') }}"
                enctype="multipart/form-data">
                @csrf
                <div class="mb-3 ">
                    <label for="excelFile" class="form-label">Subir archivos Excel</label>
                    <br>
                    <input class="form-control btn btn-outline-primary w-100" type="file" id="excelFile"
                        name="excel_files[]" accept=".xls, .xlsx, .xlsm" multiple required>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-cloud-arrow-up-fill me-2 "></i> Subir
                </button>

            </form>
        </div>
        {{-- Modal para datos adicionales  --}}
        <div class="modal fade mt-5 pt-5" id="extraDataModal" tabindex="-1" aria-labelledby="extraDataModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="extraDataModalLabel">Datos del Almacén</h5>
                        <button type="button" class="btn-close-modal bg-dark" data-bs-dismiss="modal"
                            aria-label="Cerrar"><i class="bi bi-x-lg text-white"></i></button>
                    </div>
                    <div class="modal-body">
                        <form id="extraDataForm" method="POST" data-url="{{ route('material.modalData') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="almacenId" class="form-label">Selecciona la Bodega</label>
                                <select id="almacenId" name="almacen_id" class="form-control" required>
                                    <option value="">-- Selecciona --</option>
                                    @foreach ($bodegas as $bodega)
                                        <option value="{{ $bodega['id'] }}" data-ciudad="{{ $bodega['ciudad'] }}">
                                            {{ $bodega['id'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="ciudad" class="form-label">Ciudad</label>
                                <input type="text" class="form-control" id="ciudad" name="ciudad" readonly>
                            </div>
                            <button type="submit" class="btn btn-primary" id="btn-send-bodega-id">Guardar Datos</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </section>
@section('loader')
    @include('admin.includes.components.loader')
@endsection

@section('scripts')
    <script src="{{ asset('backend/assets/js/missingMaterials.js') }}"></script>
@endsection

@endsection
