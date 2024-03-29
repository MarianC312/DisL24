<?php
    require_once 'autoload.class.php';
    Session::iniciar();

    // Globally define the Timezone
    define( 'TIMEZONE', 'America/Buenos_aires' );

    // Set Timezone
    date_default_timezone_set( TIMEZONE );
?>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Mi Negocio Virtual - MiNe</title>
    <link rel="icon" href="./image/logo-bg-white.ico" />
    <!-- Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

    <!-- Bootstrap extension -->
    <link href="https://unpkg.com/bootstrap-table@1.18.0/dist/bootstrap-table.min.css" rel="stylesheet">
    <link href="https://unpkg.com/bootstrap-table@1.18.0/dist/extensions/sticky-header/bootstrap-table-sticky-header.css" rel="stylesheet"> 
    <script src="https://unpkg.com/bootstrap-table@1.18.0/dist/bootstrap-table.min.js"></script>
    <script src="https://unpkg.com/bootstrap-table@1.18.0/dist/extensions/sticky-header/bootstrap-table-sticky-header.min.js"></script> 

    <!-- Data Tables -->
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.css" />
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css" />
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>

    <!-- Stepper -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/bs-stepper/dist/js/bs-stepper.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bs-stepper/dist/css/bs-stepper.min.css">

    <!-- Chart JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js" integrity="sha512-d9xgZrVZpmmQlfonhQUvTR7lMPtO7NkZMkA0ABN3PHCbKA5nqylQ/yWlFAyY6hYgdF1Qh6nYiuADWwKB4C2WSw==" crossorigin="anonymous"></script> 

    <!-- Tippy tooltip -->
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://unpkg.com/tippy.js@6"></script>

    <!-- Tail Selectors -->
    <link rel="stylesheet" href="./css/tail.select-default.css" />
    <script type="text/javascript" src="./js/tail.select-full.min.js"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />

    <!-- Slippry -->
    <link rel="stylesheet" href="./css/slippry.css">
    <script src="./js/slippry.min.js"></script>

    <!-- Baguette Box -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/baguettebox.js/1.8.1/baguetteBox.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/baguettebox.js/1.8.1/baguetteBox.min.js"></script>

    <!-- Cargar React. -->
    <!-- Nota: cuando se despliegue, reemplazar "development.js" con "production.min.js". -->
    <script src="https://unpkg.com/react@16/umd/react.development.js" crossorigin></script>
    <script src="https://unpkg.com/react-dom@16/umd/react-dom.development.js" crossorigin></script>

    <!-- Axios para React -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <!-- Router para React -->
    <script src="https://unpkg.com/react-router/umd/react-router.min.js"></script>
    <script src="https://unpkg.com/react-router-dom/umd/react-router-dom.min.js"></script>

    <!-- Babel para JSX -->
    <script src="https://unpkg.com/babel-standalone@6/babel.min.js"></script>

    <!-- Require for React -->
    <script src="./js/require.js"></script>

    <!-- BarcodeJS -->
    <script src="./js/JsBarcode.all.min.js"></script>

    <!-- Stepper -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/bs-stepper/dist/js/bs-stepper.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bs-stepper/dist/css/bs-stepper.min.css">

    <!-- MiNe -->
    <link rel="stylesheet" href="./css/style.css?v=<?php echo Sistema::$version ?>" /> 
    <?php 
        if(isset($_SESSION["usuario"]) && $_SESSION["usuario"]->getAuth()){ 
            Sistema::reloadStaticData();
            echo '<script src="./js/mine-actions.js?v='.Sistema::$version.'"></script>';
        }else{
            echo '<script src="./js/login/index.js?v='.Sistema::$version.'"></script>';
        }
    ?> 
</head> 