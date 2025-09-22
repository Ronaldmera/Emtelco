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
                <li class="breadcrumb-item active" aria-current="page">Generar exporte</li>
            </ol>
        </nav>
        {{-- mensaje de respuesta --}}
        <div id="responseMsg"></div>

        <div class="container text-center col-6  bg-white p-3 rounded">
            <h6><i class="bi bi-exclamation-circle-fill mr-2 text-info"></i>Sube el archivo excel previamente filtrado y
                posterior a
                ello
                se generará un exporte sólo de
                los Materiales
                Faltantes</h6>
        </div>

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

    </section>
@section('loader')
    @include('admin.includes.components.loader')
@endsection

@section('scripts')
    <script src="{{ asset('backend/assets/js/missingMaterials.js') }}"></script>
@endsection

@endsection
