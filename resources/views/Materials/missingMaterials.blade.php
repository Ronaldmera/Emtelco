@extends('admin.layouts.app')
@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Materiales Faltantes</h1>
        </div>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Inicio</a></li>
                <li class="breadcrumb-item active" aria-current="page">Materiales Faltantes</li>
            </ol>
        </nav>
        {{-- mensaje de respuesta --}}
        <div id="responseMsg"></div>

        <div class="container mt-4 py-4 col-12 col-md-6 bg-white shadow-sm rounded">
            <form id="excelUploadForm" method="POST" action="{{ route('material.excelInput') }}"
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
        <div class="modal fade mt-5 " id="extraDataModal" tabindex="-1" aria-labelledby="extraDataModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="extraDataModalLabel">Datos del Almacén</h5>
                        <button type="button" class="btn-close-modal bg-dark" data-bs-dismiss="modal"
                            aria-label="Cerrar"><i class="bi bi-x-lg text-white"></i></button>
                    </div>
                    <div class="modal-body">
                        <form id="extraDataForm" method="POST" action="{{ route('material.modalData') }}">
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

<script>
    //subir archivos excel
    document.getElementById('excelUploadForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const form = e.target;
        const formData = new FormData(form);
        const responseMsg = document.getElementById('responseMsg');
        const btn_close_modal = document.querySelector('.btn-close-modal');
        const loader = document.querySelector('.box-loader');

        loader.style.display = "flex";

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

                // Mostrar modal
                const modalEl = document.getElementById('extraDataModal');
                if (modalEl.parentElement !== document.body) {
                    document.body.appendChild(modalEl);
                }
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
                btn_close_modal.addEventListener('click', () => {
                    modal.hide();
                });
            }

            // Desaparecer mensaje
            setTimeout(() => {
                const alert = responseMsg.querySelector('.alert-validation');
                if (alert) {
                    alert.classList.add('fade-out');
                    setTimeout(() => {
                        responseMsg.innerHTML = "";
                    }, 500);
                }
            }, 2000);

        } catch (error) {
            console.error(error);
            responseMsg.innerHTML =
                `<div class="alert-validation error">Error inesperado</div>`;
            statusPet = false;

            setTimeout(() => {
                const alert = responseMsg.querySelector('.alert-validation');
                if (alert) {
                    alert.classList.add('fade-out');
                    setTimeout(() => {
                        responseMsg.innerHTML = "";
                    }, 500);
                }
            }, 2000);
        } finally {
            loader.style.display = "none";
        }

        // Autocompletar ciudad cuando se selecciona bodega
        const almacenSelect = document.getElementById('almacenId');
        const ciudadInput = document.getElementById('ciudad');

        almacenSelect.addEventListener('change', () => {
            const selectedOption = almacenSelect.options[almacenSelect.selectedIndex];
            const ciudad = selectedOption.getAttribute('data-ciudad') || '';
            ciudadInput.value = ciudad;
        });
    });

    //clik en el boton del modal
    document.getElementById('btn-send-bodega-id').addEventListener('click', async function(e) {
        e.preventDefault();

        const loader = document.querySelector('.box-loader');
        const modalEl = document.getElementById('extraDataModal');
        const almacenSelect = document.getElementById('almacenId');
        const ciudadInput = document.getElementById('ciudad').value;

        loader.style.display = "flex";

        try {
            const almacenId = almacenSelect.value;


            const response = await fetch("{{ route('material.modalData') }}", {
                method: "POST",
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: new URLSearchParams({
                    almacen_id: almacenId,
                    ciudad: ciudadInput
                })
            });

            const data = await response.json();

            if (response.ok) {
                // Lanzar descarga
                window.location.href = data.file;
            } else {
                alert("Error: " + data.message);
            }
        } catch (err) {
            console.error(err);
            alert("Error inesperado");
        } finally {
            loader.style.display = "none";
            const modalEl = document.getElementById('extraDataModal');
            modalEl.classList.remove("show");
            modalEl.classList.add("fade-out");

            // Después de la transición, ocultarlo del todo
            setTimeout(() => {
                modalEl.style.display = "none";
                modalEl.classList.remove("fade-out");
                document.body.classList.remove("modal-open");
                document.querySelectorAll(".modal-backdrop").forEach(el => el.remove());

                // Resetear campos
                almacenSelect.value = "";
                ciudadInput.value = "";
            }, 200);
        }
    });
</script>

@endsection
