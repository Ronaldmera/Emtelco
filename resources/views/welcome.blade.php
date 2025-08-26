@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Página de Inicio</h1>
        </div>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active" aria-current="page">Inicio</li>
            </ol>
        </nav>

        <div class="row align-items-center">
            <div class="col-12 col-md-5 mb-4 mb-md-0">
                <div class="text-center shadow-sm p-4 bg-body rounded h-100 d-flex flex-column justify-content-center">
                    <h2 class="fw-bold purple-secondary-color">
                        ¡Bienvenidos a AdminE!
                    </h2>
                    <p class="mt-3 welcome-text">
                        Tu panel de control para una gestión eficiente y organizada.
                    </p>
                    <i class="bi bi-house-door-fill purple-secondary-color display-4 mt-3"></i>
                </div>
            </div>
            <div class="col-12 col-md-7">
                <img src="{{ asset('backend/assets/img/Index/emtelco.png') }}" alt="Equipo de Emtelco celebrando"
                    class="img-fluid rounded shadow-sm">
            </div>
        </div>
    </section>
@endsection
