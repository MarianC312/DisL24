<!DOCTYPE html>
<?php
    require_once 'autoload.class.php';
    if(!Sistema::usuarioLogueado()){
        echo '<meta http-equiv="refresh" content="0;URL=./index.php" />';
        exit;
    }
?>
<html lang="en">

<head>
    <?php require_once 'header.php' ?>
</head> 

<body>
    <div id="loadProgressContainer" class="ventana-flotante d-none" style="backdrop-filter: blur(10px);">
        <div class="h1 w-50 font-weight-bold text-light text-uppercase">Estamos cargando los módulos necesarios para comenzar a trabajar, aguarda un momento por favor...</div>
    </div>
    <div id="main-body" class="d-flex">
        <div class="d-inline-flex flex-row-reverse fixed-bottom" style="width: fit-content; left: 85%; bottom: 10%">
            <button type="button" id="backToTop" class="btn btn-outline-primary" style="display: none"><i class="fa fa-chevron-up fa-2x"></i></button>
        </div>
        <div id="left-content" class="d-flex">
            <?php Componente::menu(); ?>
        </div>
        <div id="right-content" class="flex-grow-1" style="background-color: #FFF"> 
            <?php Componente::headerUsuario() ?>
            <div id="right-content-alerts"><?php Sistema::facturaImpagaAlerta() ?></div>
            <div id="right-content-process"></div> 
            <div id="right-content-data">
                <?php Componente::inicio(); ?>
            </div>
            <div id="right-content-producto-data">
                <?php
                    Compania::productoLista();
                ?>
            </div> 
        </div>
    </div>
</body>
</html>
<script> 
   $(document).ready(function(){
        $(window).bind('scroll', function()
        {
            if($(this).scrollTop() > 0 ){
                $("#backToTop").slideDown(300);
            }else{
                $("#backToTop").slideUp(300);
            }
        });
        
        $("#backToTop").on('click', function(){
            $('html, body').animate({scrollTop:'0px'}, '300');
        });
        $("#loadProgressContainer").remove();
    });
</script>