<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once 'header.php' ?>
</head> 

<body>
    <div id="main-body" class="d-flex">
        <div id="left-content" class="d-flex"></div>
        <div id="right-content" class="flex-grow-1">
                <?php require_once './includes/usuario/header.html'; ?>
                <div id="right-content-data"></div>
            </div>
        </div>
    </div>
</body>
<script type="text/babel" src="./js/component/Menu.jsx"></script> 
</html>