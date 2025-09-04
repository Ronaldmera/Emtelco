 <div class="main-sidebar sidebar-style-2">
     <aside id="sidebar-wrapper">
         <div class="sidebar-brand pt-3">
             <a href="{{ route('home.index') }}">
                 <img src="{{ asset('backend/assets/img/LogoEmtelco/Logo_Emtelco.png') }}" alt=""
                     class="w-50 h-100">
             </a>
         </div>
         <div class="sidebar-brand sidebar-brand-sm pt-3">
             <a href="{{ route('home.index') }}">
                 <img src="{{ asset('backend/assets/img/LogoEmtelco/Logo_Emtelco.png') }}" alt=""
                     class="w-75 h-75">

             </a>
         </div>
         <ul class="sidebar-menu">
             <li class="menu-header">Opciones</li>
             <li class="dropdown">
                 <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i
                         class="bi bi-box text-primary"></i>
                     <span>Materiales
                         faltantes</span></a>
                 <ul class="dropdown-menu">
                     <li><a class="nav-link" href="{{ route('material.showMissingMaterials') }}"><i
                                 class="bi bi-funnel text-primary"></i><span>Filtrar
                                 Registros</span></a>
                     </li>
                     <li><a class="nav-link" href="#"><i
                                 class="bi bi-box-arrow-down text-primary"></i><span>Generar Exporte
                             </span></a>
                     </li>
                 </ul>
             </li>
             {{-- <li><a class="nav-link" href="{{ route('material.showMissingMaterials') }}"><i
                         class="bi bi-box text-primary"></i> <span>Materiales
                         faltantes</span></a></li> --}}
             <li><a class="nav-link" href="blank.html"><i class="bi bi-arrow-counterclockwise text-primary"></i>
                     <span>Logistica Inversa
                     </span></a></li>
     </aside>
 </div>
