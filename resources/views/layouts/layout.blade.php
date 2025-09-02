<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
    <title>{{ config('app.name', 'CNF') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/x-icon" href="{{ asset("assets/src/assets/img/favicon-32x32.png") }}"/>
    <link href="{{ asset("assets/layouts/vertical-light-menu/css/light/loader.css") }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset("assets/layouts/vertical-light-menu/css/dark/loader.css") }}" rel="stylesheet" type="text/css" />
    <script src="{{ asset("assets/layouts/vertical-light-menu/loader.js") }}"></script>

    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:400,600,700" rel="stylesheet">
    <link href="{{ asset("assets/src/bootstrap/css/bootstrap.min.css") }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset("assets/layouts/vertical-light-menu/css/light/plugins.css") }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset("assets/layouts/vertical-light-menu/css/dark/plugins.css") }}" rel="stylesheet" type="text/css" />
    <!-- END GLOBAL MANDATORY STYLES -->

    <!-- BEGIN PAGE LEVEL PLUGINS/CUSTOM STYLES -->
    <link href="{{ asset("assets/src/plugins/src/apex/apexcharts.css") }}" rel="stylesheet" type="text/css">
    <link href="{{ asset("assets/src/assets/css/light/dashboard/dash_1.css") }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset("assets/src/assets/css/dark/dashboard/dash_1.css") }}" rel="stylesheet" type="text/css" />
    <!-- END PAGE LEVEL PLUGINS/CUSTOM STYLES -->

    <!-- BEGIN PAGE LEVEL STYLES -->

    <!-- END PAGE LEVEL STYLES -->

    <link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/src/tomSelect/tom-select.default.min.css") }}">
    <link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/css/light/tomSelect/custom-tomSelect.css") }}">
    <link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/css/dark/tomSelect/custom-tomSelect.css") }}">

    <!-- BEGIN THEME GLOBAL STYLES -->
    <link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/src/flatpickr/flatpickr.css") }}">
    <link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/css/light/flatpickr/custom-flatpickr.css") }}">
    <link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/css/dark/flatpickr/custom-flatpickr.css") }}">
    @vite(['resources/js/app.js'])
    @yield('styles')

</head>
<body class="layout-boxed">
<!-- BEGIN LOADER -->
@include("layouts.partials.loader")
<!--  END LOADER -->

<!--  BEGIN NAVBAR  -->
@include("layouts.partials.navbar")
<!--  END NAVBAR  -->
<div class="main-container" id="container">

    <div class="overlay"></div>
    <div class="search-overlay"></div>

    <!--  BEGIN SIDEBAR  -->
    @include("layouts.partials.sidebar")
    <!--  END SIDEBAR  -->
    <div id="content" class="main-content">
        <div class="layout-px-spacing">
            <div class="middle-content container-xxl p-0">

                <!--  BEGIN BREADCRUMBS  -->
            @include("layouts.partials.breadcrumbs")
            <!--  END BREADCRUMBS  -->
            @yield('content')

        </div>
        </div>
        <!--  BEGIN FOOTER  -->
        @include("layouts.partials.footer")
    <!--  END FOOTER  -->
    </div>
    <!--  END CONTENT AREA  -->

</div>
<!-- END MAIN CONTAINER -->

<!-- BEGIN GLOBAL MANDATORY SCRIPTS -->
<script src="{{ asset("assets/src/plugins/src/global/vendors.min.js") }}"></script>
<script src="{{ asset("assets/src/bootstrap/js/bootstrap.bundle.min.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/perfect-scrollbar/perfect-scrollbar.min.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/mousetrap/mousetrap.min.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/waves/waves.min.js") }}"></script>
<script src="{{ asset("assets/layouts/vertical-light-menu/app.js") }}"></script>
<script src="{{ asset("assets/src/assets/js/custom.js") }}"></script>
<!-- END GLOBAL MANDATORY SCRIPTS -->

<!-- BEGIN PAGE LEVEL PLUGINS/CUSTOM SCRIPTS -->
<script src="{{ asset("assets/src/plugins/src/apex/apexcharts.min.js") }}"></script>
<script src="{{ asset("assets/src/assets/js/dashboard/dash_1.js") }}"></script>
<!-- BEGIN PAGE LEVEL PLUGINS/CUSTOM SCRIPTS -->

<!-- BEGIN PAGE LEVEL SCRIPTS -->


<script src="{{ asset("assets/src/assets/js/scrollspyNav.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/tomSelect/tom-select.base.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/tomSelect/custom-tom-select.js") }}"></script>


<script src="{{ asset("assets/src/plugins/src/flatpickr/flatpickr.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/flatpickr/custom-flatpickr.js") }}"></script>

<script>

    //Select Box
    new TomSelect("#select-beast",{
        create: true,
        sortField: {
            field: "text",
            direction: "asc"
        }
    });
    //Date Picker
    // var f1 = flatpickr(document.getElementById('basicFlatpickr'));
    var f1 = flatpickr(document.getElementById('basicFlatpickr'), {
        enableTime: true,
        dateFormat: "d-m-Y",
    });

</script>
@yield('scripts')
</body>
</html>
