@extends('admin.layouts.app')
@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Materiales Faltantes</h1>
        </div>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('welcome') }}">Inicio</a></li>
                <li class="breadcrumb-item active" aria-current="page">Materiales Faltantes</li>
            </ol>
        </nav>
        {{-- mensaje de respuesta --}}
        <div id="responseMsg"></div>

        <div class="container mt-4 py-4">
            <form id="excelUploadForm" method="POST" action="{{ route('material.excelInput') }}"
                enctype="multipart/form-data">
                @csrf
                <div class="mb-3 ">
                    <label for="excelFile" class="form-label">Subir archivos Excel</label>
                    <br>
                    <input class="form-control btn btn-outline-primary w-100" type="file" id="excelFile"
                        name="excel_files[]" accept=".xls, .xlsx, .xlsm" multiple required>
                </div>
                <button type="submit" class="btn btn-primary">Subir</button>
            </form>
        </div>
    </section>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            document.getElementById('excelUploadForm').addEventListener('submit', async function(e) {
                e.preventDefault();

                const form = e.target;
                const formData = new FormData(form);
                const responseMsg = document.getElementById('responseMsg');

                try {
                    const response = await fetch("{{ route('material.excelInput') }}", {
                        method: "POST",
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (response.ok) {
                        responseMsg.innerHTML =
                            `<div class="alert-validation success">${data.message}</div>`;
                        form.reset();
                    } else {
                        responseMsg.innerHTML =
                            `<div class="alert-validation error">${data.message}</div>`;
                    }

                    // Desaparecer con efecto fade después de 2 segundos
                    setTimeout(() => {
                        const alert = responseMsg.querySelector('.alert-validation');
                        if (alert) {
                            alert.classList.add('fade-out');
                            setTimeout(() => {
                                responseMsg.innerHTML = "";
                            }, 500); // esperar animación
                        }
                    }, 2000);

                } catch (error) {
                    console.error(error);
                    responseMsg.innerHTML =
                        `<div class="alert-validation error">Error inesperado</div>`;

                    setTimeout(() => {
                        const alert = responseMsg.querySelector('.alert-validation');
                        if (alert) {
                            alert.classList.add('fade-out');
                            setTimeout(() => {
                                responseMsg.innerHTML = "";
                            }, 500);
                        }
                    }, 2000);
                }
            });
        });
    </script>
@endsection
