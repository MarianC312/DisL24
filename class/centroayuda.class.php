<?php
    class Centroayuda{
        private static $data = [
            "idSistema" => 1,
            "database" => 1,
            "sistema" => [
                0 => null,
                1 => "emine.com.ar",
                2 => "cguro.com.ar",
                3 => "/nuevaBled",
                4 => "/mine"
            ]
        ];

        public static $publicData = [
            "intervaloRevision" => 0
        ]; 

        public static function getLastTicketCheck(){
            Session::iniciar();
            return $_SESSION["usuario"]->getLastTicketCheck();
        } 
        public static function setLastTicketCheck(){
            Session::iniciar();
            $_SESSION["usuario"]->setLastTicketCheck();
        }
        public static function esAdmin(){
            Session::iniciar();
            return ($_SESSION["usuario"]->isAdmin()) ? true : false;
        }
        public static function getIntervaloRevision(){ return Centroayuda::$publicData["intervaloRevision"]; }

        public static function deberiaRevisarActividad(){ 
            Session::iniciar();
            $date1 = Centroayuda::getLastTicketCheck();
            if(is_null($date1)){
                return true;
            }else{
                $date2 = Date::current();
                $timestamp1 = strtotime($date1);
                $timestamp2 = strtotime($date2);
                $minutos = abs($timestamp2 - $timestamp1)/(60);
                return ($minutos >= Centroayuda::getIntervaloRevision()) ? true : false;
            }
        }

        public static function ticketTieneNuevaActividad(){
            $response = [
                "status" => false,
                "mensajeUser" => "",
                "mensajeAdmin" => "",
                "dependencia" => "Centroayuda / ticketTieneNuevaActividad",
                "data" => [
                    "array" => null,
                    "count" => null
                ]
            ];
            if(Centroayuda::deberiaRevisarActividad()){
                Session::iniciar();
                $ultimaRevision = Centroayuda::getLastTicketCheck();
                $query = DataBase::select("ticket", "id,ultimaActividad", "estado = 1 AND idOperador = '".$_SESSION["usuario"]->getId()."' AND ultimaActividad > '".((!is_null($ultimaRevision)) ? $ultimaRevision : Date::current())."'", "", Centroayuda::$data["database"]);
                if($query){
                    Centroayuda::setLastTicketCheck();
                    $data = [];
                    if(DataBase::getNumRows($query) > 0){
                        while($dataQuery = DataBase::getArray($query)){
                            $data[$dataQuery["id"]] = $dataQuery;
                        }
                        foreach($data AS $key => $value){
                            foreach($value AS $iKey => $iValue){
                                if(is_int($iKey)){
                                    unset($data[$key][$iKey]);
                                }
                            }
                        }
                    } 
                    $response["status"] = true;
                    $response["data"]["array"] = $data;
                    $response["data"]["count"] = count($data);
                }else{
                    $response["mensajeUser"] = "Ocurrió un error al consultar la actividad de tus tickets.";
                    $response["mensajeAdmin"] = "Ref.: ".Database::getError();
                }
            }else{
                $response["status"] = true;
                $response["mensajeAdmin"] = "Aún no se debe revisar la actividad del Centro de ayuda.";
            }
            return $response;
        }

        public static function mainContainer($admin = false){
            Session::iniciar();
            $caUsuarioNombre = $_SESSION["usuario"]->getNombre();
            ?>
            <div class="ca-container">
                <div class="ca-main-header">
                    <h4><b>Hola de nuevo,</b> <?php echo $caUsuarioNombre; ?>!</h4>
                    <span id="ticketMensaje">
                        No tenemos registrados tickets de consulta, para agregar uno nuevo utilizá <a href="#" onclick="caTicketFormulario()">este link</a>.
                    </span>
                </div>
                <div class="ca-main-body">
                    <div id="ca-main-ticket-lista">
                        <?php
                            Centroayuda::ticketLista($admin);
                        ?>
                    </div>
                    <div id="ca-main-ticket-lista-process"></div>
                </div>
            </div>
            <?php
        }

        public static function ticketFormulario(){
            ?>
            <div id="centro-ayuda-container" class="ventana-flotante">
                <div id="centro-ayuda" class="mine-container">
                    <div class="d-flex justify-content-between"> 
                        <div class="titulo">Registrar ticket consulta / reclamo</div>
                        <button type="button" onclick="$('#centro-ayuda-container').remove()" class="btn delete"><i class="fa fa-times"></i></button>
                    </div>
                    <div class="dropdown-divider"></div>
                    <div class="p-1" style="width: 50vw; height: 45vh;">
                        <div id="ticket-process" style="display: none"></div>
                        <form id="ticket-form" action="./engine/sistema/ca/ticket-formulario-registro.php" form="#ticket-form" process="#ticket-process" style="padding: 0 2rem;">
                            <div class="form-group">
                                <label class="col-form-label" for="asunto">Asunto</label>
                                <input type="text" class="form-control" id="asunto" name="asunto">
                            </div>
                            <div class="form-group">
                                <label for="exampleTextarea">Consulta</label>
                                <textarea class="form-control" id="consulta" name="consulta" rows="6"></textarea>
                            </div>
                            <div class="form-group">
                                <button type="button" class="btn btn-primary" onclick="caTicketFormularioRegistro()">Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php
        }

        public static function ticketFormularioRegistro($data){
            Session::iniciar();
            if($_SESSION["usuario"]->isAdmin()) echo '<button type="button" class="btn btn-primary" onclick="$(\''.$data["process"].'\').hide(150); $(\''.$data["form"].'\').show(150)"><i class="fa fa-reload"></i></button>';
            if(isset($data) && is_array($data) && count($data) > 0){
                $query = DataBase::insert("ticket", "asunto, consulta, idSistema, idOperador, tagOperador", "'".$data["asunto"]."', '".$data["consulta"]."', '".CentroAyuda::$data["idSistema"]."', '".$_SESSION["usuario"]->getId()."', '".$_SESSION["usuario"]->getNombre()."'", CentroAyuda::$data["database"]);
                if($query){
                    $idTicket = DataBase::getLastId();
                    $hash = Sistema::hash($idTicket);
                    $mensaje['tipo'] = 'success';
                    $mensaje['cuerpo'] = 'Se registró tu ticket satisfactoriamente. <br>Un administrador se pondrá en contacto a la brevedad. <br>Código de ticket #'.Database::getLastId();
                    Alert::mensaje($mensaje);
                    echo '<script>setTimeout(() => { caTicketVisualizarFormulario('.$idTicket.', "'.$hash["hash"].'") }, 350)</script>';
                }else{
                    $mensaje['tipo'] = 'danger';
                    $mensaje['cuerpo'] = 'Ocurrió un error al registrar el ticket. <br><b>Intente nuevamente o contacte al administrador.</b>';
                    Alert::mensaje($mensaje);
                }
            }else{
                $mensaje['tipo'] = 'danger';
                $mensaje['cuerpo'] = 'Ocurrió un error con la información recibida. No se registró la consulta. <br><b>Intente nuevamente o contacte al administrador</b>.';
                Alert::mensaje($mensaje);
            }
        }

        public static function ticketLista($admin = false){
            $ticketDataResponse = Centroayuda::ticketData(null, $admin);
            if($ticketDataResponse["status"] === true){
                if($ticketDataResponse["data"]["count"] > 0){
                    ?>
                    <div class="ca-container">
                        <table id="tablaTicket" class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">ID</th>
                                    <th>Título</th>
                                    <th>Última actividad</th>
                                    <th>Estado</th>
                                    <th>&nbsp;</th>
                                </tr>
                            </thead>
                                <?php
                                    foreach($ticketDataResponse["data"]["array"] AS $key => $value){
                                        $hash = Sistema::hash($value["id"]);
                                        switch($value["estado"]){
                                            case 1:
                                                $estado = "<span class='text-success'>Abierto</span>";
                                                break;
                                            case 2:
                                                $estado = "<span class='text-warning'>Cerrado</span>";
                                                break;
                                            default:
                                                $estado = "<span class='text-danger' style='text-decoration: line-through;'>Eliminado</span>";
                                                break;
                                        }
                                        ?> 
                                        <tr id="ticketId<?php echo $value["id"] ?>">
                                            <td><?php echo "#".intval($value["id"]) ?></td>
                                            <td><?php echo mb_strtoupper($value["asunto"]) ?></td>
                                            <td><?php echo (!is_null($value["ultimaActividad"])) ? $value["ultimaActividad"] : "Sin actividad" ?></td>
                                            <td><?php echo $estado ?></td>
                                            <td class="text-right"><button type="button" class="btn btn-sm btn-primary" onclick="caTicketVisualizarFormulario(<?php echo $value['id'] ?>, '<?php echo $hash['hash'] ?>', <?php echo $admin ?>)"><i class="fa fa-long-arrow-right"></i></button></td>
                                        </tr>
                                        <?php
                                    }
                                ?>
                            <tbody>
                            </tbody>
                        </table>
                        <script>
                            $(".ca-main-header #ticketMensaje").html("Esta es tu actividad más reciente, para cargar un nuevo ticket utilizá <a href=\"#/\" onclick=\"caTicketFormulario()\">este link</a>");
                        </script>
                    </div>
                    <?php
                }
            }else{
                $mensaje['tipo'] = 'danger';
                $mensaje['cuerpo'] = $ticketDataResponse["mensajeUser"];
                $mensaje['cuerpo'] .= "<div class='p-1 mt-4'><b>Información para el administrador:</b> <br> Dependencia: ".$ticketDataResponse["dependencia"].". <br>".$ticketDataResponse["mensajeAdmin"]."</div>";
                Alert::mensaje($mensaje);
            } 
        }

        public static function ticketVisualizarFormulario($data){
            if(isset($data) && is_array($data) && count($data) > 0){
                if(Sistema::hashCheck($data["idTicket"], $data["hash"])){
                    $ticketDataResponse = Centroayuda::ticketData($data["idTicket"], $data["admin"]);
                    if($ticketDataResponse["status"] === true){
                        $ticketComentarioDataResponse = [];
                        switch($ticketDataResponse["data"]["array"][$data["idTicket"]]["estado"]){
                            case 1:
                                $estado = "<span class='text-success'>Abierto</span>";
                                break;
                            case 2:
                                $estado = "<span class='text-warning'>Cerrado</span>";
                                break;
                            default:
                                $estado = "<span class='text-danger' style='text-decoration: line-through;'>Eliminado</span>";
                                break;
                        }
                        ?>
                        <div class="ca-container ca-ticket-container" id="<?php echo $data["idTicket"] ?>">
                            <div class="ca-ticket-header">
                                <div class="d-flex py-2 px-4">
                                    <button type="button" class="btn btn-primary" onclick="$('<?php echo $data['process'] ?>').hide(150).html(''); $('<?php echo $data['form'] ?>').show(150);"><i class="fa fa-long-arrow-left"></i> Regresar</button>
                                    <div class="ticket-subheader">
                                        <span id="identificador">#<?php echo $ticketDataResponse["data"]["array"][$data["idTicket"]]["id"] ?> </span>
                                        <div id="asunto">
                                            <?php echo mb_strtoupper($ticketDataResponse["data"]["array"][$data["idTicket"]]["asunto"]) ?>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <ul class="list-group">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <b>Estado</b>
                                            <?php echo $estado ?>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <b>Última actividad</b>
                                            <span class="text-muted"><?php echo (!is_null($ticketDataResponse["data"]["array"][$data["idTicket"]]["ultimaActividad"])) ? $ticketDataResponse["data"]["array"][$data["idTicket"]]["ultimaActividad"] : "Sin actividad" ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <b>Fecha registro</b>
                                            <span class="text-muted"><?php echo date("d/m/Y, H:i A", strtotime($ticketDataResponse["data"]["array"][$data["idTicket"]]["fechaCarga"])) ?></span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="ca-ticket-body"> 
                                <div class="comentario-container">
                                    <div class="inner">
                                        <div class="icono">
                                            <?php echo mb_strtoupper(substr($_SESSION["usuario"]->getNombre(), 0, 1)) ?>
                                        </div>
                                        <div class="contenido">
                                            <div class="header"><b>YO</b><span class="text-muted ml-3"><?php echo date("d/m/Y, H:i A", strtotime($ticketDataResponse["data"]["array"][$data["idTicket"]]["fechaCarga"])) ?></span></div>
                                            <div class="body">
                                                <?php echo nl2br($ticketDataResponse["data"]["array"][$data["idTicket"]]["consulta"]) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="ca-ticket-action">
                                <?php Centroayuda::ticketComentarioFormulario($data["idTicket"]); ?>
                            </div>
                        </div>
                        <script>
                            $(document).ready(() => {
                                setTimeout(() => { caTicketComentarioRecarga(<?php echo $data["idTicket"] ?>) }, 750);
                                /*
                                    setear el ca-ticket-body como width: 500px y overflow-y: auto
                                    luego de llamar a la función. correr el script de scroll del div
                                    $(".ca-ticket-body").scrollTop($(".ca-ticket-body")[0].scrollHeight);
                                */
                            })
                        </script>
                        <?php
                    }else{
                        $mensaje['tipo'] = 'danger';
                        $mensaje['cuerpo'] = $ticketDataResponse["mensajeUser"];
                        $mensaje['cuerpo'] .= "<div class='p-1 mt-4'><b>Información para el administrador:</b> <br> Dependencia: ".$ticketDataResponse["dependencia"].". <br>".$ticketDataResponse["mensajeAdmin"]."</div>";
                        Alert::mensaje($mensaje);
                    }
                }else{
                    $mensaje['tipo'] = 'warning';
                    $mensaje['cuerpo'] = 'Evite modificar los formularios! No se cargará la información.';
                    Alert::mensaje($mensaje);
                }
            }else{
                $mensaje['tipo'] = 'danger';
                $mensaje['cuerpo'] = 'Ocurrió un error con el identificador del ticket. <b>Contante al administrador a la brevedad.</b>';
                Alert::mensaje($mensaje);
            }
        }

        public static function ticketComentarioFormulario($idTicket){
            if(isset($idTicket) && is_numeric($idTicket) && $idTicket > 0){
                ?>
                <div class="ticket-comentario-container">
                    <div class="contenido">
                        <div class="body">
                            <div id="ticket-comentario-process" style="display: none"></div>
                            <form id="ticket-comentario-form" action="./engine/sistema/ca/ticket-comentario-formulario-registro.php" onsubmit="return false;" form="#ticket-comentario-form" process="#ticket-comentario-process"> 
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-lg" placeholder="Escribir un mensaje..." id="comentario" name="comentario">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-lg btn-success" onclick="caTicketComentarioFormularioRegistro(<?php echo $idTicket ?>)"><i class="fa fa-paper-plane-o"></i></button>
                                    </div>
                                </div>
                            </form>
                            <script>
                                $("#ticket-comentario-form").on("keypress", (e) => {
                                    if(e.keyCode == 13){
                                        caTicketComentarioFormularioRegistro(<?php echo $idTicket ?>);
                                    }
                                })
                            </script>
                        </div>
                    </div>
                </div>
                <?php
            }else{
                $mensaje['tipo'] = 'danger';
                $mensaje['cuerpo'] = 'Ocurrió un error con el identificador del ticket. <b>Contante al administrador a la brevedad.</b>';
                Alert::mensaje($mensaje);
            }
        }

        public static function ticketComentarioRecarga($idTicket, $fecha = null){
            if(isset($idTicket) && is_numeric($idTicket) && $idTicket > 0){ 
                $ticketComentarioDataResponse = Centroayuda::ticketComentarioData($idTicket, $fecha);
                if($ticketComentarioDataResponse["status"] === true){
                    if($ticketComentarioDataResponse["data"]["count"] > 0){
                        foreach($ticketComentarioDataResponse["data"]["array"] AS $key => $value){
                            ?>
                            <div class="comentario-container">
                                <div class="inner">
                                    <div class="icono">
                                        <?php echo mb_strtoupper(substr($value["tagOperador"], 0, 1)) ?>
                                    </div>
                                    <div class="contenido">
                                        <div class="header"><b><?php echo mb_strtoupper($value["tagOperador"]) ?></b><span class="text-muted ml-3"><?php echo date("d/m/Y, H:i A", strtotime($value["fechaCarga"])) ?></span></div>
                                        <div class="body">
                                            <?php echo nl2br($value["comentario"]) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    }
                }else{
                    $mensaje['tipo'] = 'danger';
                    $mensaje['cuerpo'] = $ticketComentarioDataResponse["mensajeUser"];
                    $mensaje['cuerpo'] .= "<div class='p-1 mt-4'><b>Información para el administrador:</b> <br> Dependencia: ".$ticketComentarioDataResponse["dependencia"].". <br>".$ticketComentarioDataResponse["mensajeAdmin"]."</div>";
                    Alert::mensaje($mensaje);
                }
            }else{
                $mensaje['tipo'] = 'danger';
                $mensaje['cuerpo'] = 'Ocurrió un error al intentar cargar los comentarios del ticket. El identificador del ticket es incorrecto. <br><b>Intente nuevamente o contacte al administrador.</b>';
                Alert::mensaje($mensaje);
            }
        }

        public static function ticketComentarioFormularioRegistro($data){
            Session::iniciar();
            if($_SESSION["usuario"]->isAdmin()) echo '<button type="button" class="btn btn-primary" onclick="$(\''.$data["process"].'\').hide(150); $(\''.$data["form"].'\').show(150)"><i class="fa fa-reload"></i></button>';
            if(isset($data) && is_array($data) && count($data) > 0){
                $query = DataBase::insert("ticket_comentario", "comentario, idTicket, idOperador, tagOperador", "'".$data["comentario"]."', '".$data["idTicket"]."', '".$_SESSION["usuario"]->getId()."', '".$_SESSION["usuario"]->getNombre()."'", CentroAyuda::$data["database"]);
                if($query){
                    $mensaje['tipo'] = 'success';
                    $mensaje['cuerpo'] = 'Se registró tu comentario satisfactoriamente.';
                    Alert::mensaje($mensaje);
                    echo '<script>setTimeout(() => { $("'.$data["form"].' #comentario").val("") }, 1750)</script>';
                }else{
                    $mensaje['tipo'] = 'danger';
                    $mensaje['cuerpo'] = 'Ocurrió un error al registrar el ticket. <br><b>Intente nuevamente o contacte al administrador.</b>';
                    Alert::mensaje($mensaje);
                }
            }else{
                $mensaje['tipo'] = 'danger';
                $mensaje['cuerpo'] = 'Ocurrió un error con la información recibida. No se registró la consulta. <br><b>Intente nuevamente o contacte al administrador</b>.';
                Alert::mensaje($mensaje);
            }
            echo '<script>setTimeout(() => { $("'.$data["process"].'").hide(250); $("'.$data["form"].' #comentario").focus(); caTicketComentarioRecarga('.$data["idTicket"].', "'.Date::current().'") }, 2500)</script>';
        }

        private static function ticketComentarioData($idTicket, $fecha = null){
            $response = [
                "status" => false,
                "mensajeUser" => "",
                "mensajeAdmin" => "",
                "dependencia" => "Centroayuda / ticketData",
                "data" => [
                    "array" => null,
                    "count" => null
                ]
            ];
            Session::iniciar();
            if(isset($idTicket) && is_numeric($idTicket) && $idTicket > 0){
                $query = DataBase::select("ticket_comentario", "*", "idTicket = '".$idTicket."' AND estado = 1 ".((!is_null($fecha)) ? "AND fechaCarga >= '".$fecha."'" : ""), "ORDER BY fechaCarga ASC LIMIT 25", Centroayuda::$data["database"]);
                if($query){
                    $response["status"] = true;
                    $data = [];
                    if(DataBase::getNumRows($query) > 0){
                        while($dataQuery = DataBase::getArray($query)){
                            $data[$dataQuery["id"]] = $dataQuery;
                        }
                        foreach($data AS $key => $value){
                            foreach($value AS $iKey => $iValue){
                                if(is_int($iKey)){
                                    unset($data[$key][$iKey]);
                                }
                            }
                        }
                    }
                    $response["data"]["array"] = $data;
                    $response["data"]["count"] = count($data);
                }else{
                    $response["mensajeUser"] = "Ocurrió un error al comprobar los comentarios del ticket.";
                    $response["mensajeAdmin"] = "Ref.: ".DataBase::getError();
                }
            }else{
                $response["mensajeUser"] = "El identificador del ticket presenta un error. <b>Contacte al administrador a la brevedad.</b>";
            }
            return $response;
        }

        private static function ticketData($idTicket = null, $admin = false){
            $response = [
                "status" => false,
                "mensajeUser" => "",
                "mensajeAdmin" => "",
                "dependencia" => "Centroayuda / ticketData",
                "data" => [
                    "array" => null,
                    "count" => null
                ]
            ];
            Session::iniciar();
            if(($admin == "true") && Centroayuda::esAdmin()){
                $condicion = ((!is_null($idTicket) && is_numeric($idTicket) && $idTicket > 0) ? "id = '".$idTicket."' AND " : "")."(idOperadorActual != '".$_SESSION["usuario"]->getId()."' OR idOperadorActual IS NULL) AND estado = 1";
            }else{
                $condicion = ((!is_null($idTicket) && is_numeric($idTicket) && $idTicket > 0) ? "id = '".$idTicket."' AND " : "")."idSistema = '".Centroayuda::$data["idSistema"]."' AND idOperador = '".$_SESSION["usuario"]->getId()."'";
            }
            $query = DataBase::select("ticket", "*", $condicion, "ORDER BY ultimaActividad DESC, fechaCarga DESC LIMIT 5", Centroayuda::$data["database"]);
            if($query){
                $response["status"] = true;
                $data = [];
                if(DataBase::getNumRows($query) > 0){
                    while($dataQuery = DataBase::getArray($query)){
                        $data[$dataQuery["id"]] = $dataQuery;
                    }
                    foreach($data AS $key => $value){
                        foreach($value AS $iKey => $iValue){
                            if(is_int($iKey)){
                                unset($data[$key][$iKey]);
                            }
                        }
                    }
                }
                $response["data"]["array"] = $data;
                $response["data"]["count"] = count($data);
            }else{
                $response["mensajeUser"] = "Ocurrió un error al comprobar tus tickets.";
                $response["mensajeAdmin"] = "Ref.: ".DataBase::getError();
            }
            return $response;
        }
    }
?>