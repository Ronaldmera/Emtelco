@extends('admin.layouts.app')
@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Página de Inicio</h1>
        </div>

        <div class="row justify-content-center align-items-center">
            <div class="col-md-6 text-center d-flex flex-column justify-content-center align-items-center">
                <h2 class="text-white p-4 rounded bg-info w-100 py-5">
                    ¡Bienvenido a AdminE! <br>
                    Tu panel de control para una gestión eficiente y organizada.
                    <i class="bi bi-house-door-fill text-dark display-4 d-block mt-2 mt-sm-4"></i>
                </h2>
            </div>
            <div class="col-md-6">
                <img src="backend/assets/img/Index/img_Experiencia.png" alt="Imagen de experiencia" class="img-fluid">
            </div>
        </div>
    </section>
@endsection
