@extends('admin.layouts.app')
@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Materiales Faltantes</h1>
        </div>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Materiales Faltantes</li>
            </ol>
        </nav>

        <div class="container mt-4 py-4">
            <form id="excelUploadForm" method="POST" action="{{ route('material.excelInput') }}"
                enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="excelFile" class="form-label">Subir archivos Excel</label>
                    <input class="form-control" type="file" id="excelFile" name="excel_files[]" accept=".xls, .xlsx"
                        multiple required>
                </div>
                <button type="submit" class="btn btn-primary">Subir</button>
            </form>
            {{-- mensaje de respuesta --}}
            <div id="responseMsg"></div>
        </div>
    </section>
@endsection
<script>
    document.getElementById('excelUploadForm').addEventListener('submit', async function(e) {
        e.preventDefault(); // evita recarga

        const form = e.target;
        const formData = new FormData(form);
        const responseMsg = document.getElementById('responseMsg');

        try {
            const response = await fetch("{{ route('material.excelInput') }}", {
                method: "POST",
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            });

            const data = await response.json();

            if (response.ok) {
                responseMsg.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                form.reset();
            } else {
                responseMsg.innerHTML =
                    `<div class="alert alert-danger">${data.message || 'Error al subir archivos'}</div>`;
            }
        } catch (error) {
            console.error(error);
            responseMsg.innerHTML = `<div class="alert alert-danger">Error inesperado</div>`;
        }
    });
</script>
