const loading = (tipo = "loader") => { return ('<span class="' + tipo + '"></span>'); },
    prime = [2, 3, 5, 7, 11, 13, 17, 19],
    randomHexa = size => [...Array(size)].map(() => Math.floor(Math.random() * 16).toString(16)).join(''),
    productoChunkLimit = 2500;

function goToByScroll(div) {
    $(div).show(150);
    $('html,body').animate({
        scrollTop: $(div).offset().top
    }, 'slow');
}

const dragElement = (elmnt, scope) => {
    var pos1 = 0,
        pos2 = 0,
        pos3 = 0,
        pos4 = 0;
    if (document.getElementById(elmnt.id + scope)) {
        // if present, the header is where you move the DIV from:
        document.getElementById(elmnt.id + scope).onmousedown = dragMouseDown;
    } else {
        // otherwise, move the DIV from anywhere inside the DIV:
        elmnt.onmousedown = dragMouseDown;
    }

    function dragMouseDown(e) {
        e = e || window.event;
        e.preventDefault();
        // get the mouse cursor position at startup:
        pos3 = e.clientX;
        pos4 = e.clientY;
        document.onmouseup = closeDragElement;
        // call a function whenever the cursor moves:
        document.onmousemove = elementDrag;
    }

    function elementDrag(e) {
        e = e || window.event;
        e.preventDefault();
        // calculate the new cursor position:
        pos1 = pos3 - e.clientX;
        pos2 = pos4 - e.clientY;
        pos3 = e.clientX;
        pos4 = e.clientY;
        // set the element's new position:
        elmnt.style.top = (elmnt.offsetTop - pos2) + "px";
        elmnt.style.left = (elmnt.offsetLeft - pos1) + "px";
    }

    function closeDragElement() {
        // stop moving when mouse button is released:
        document.onmouseup = null;
        document.onmousemove = null;
    }
}

let cart = [],
    reloadStaticData = setInterval(() => {

        /*
        let date = new Date();

        var h = date.getHours();
        var m = date.getMinutes();
        var s = date.getSeconds();

        console.log(h + " : " + m + " : " + s);
        */

        sistemaReloadStaticData();
    }, (30 * 60 * 1000)), // 30min,
    alertaBaseProductoNuevoActualizado = setInterval(() => {
        sistemaConsultaProductoNuevoActualizado();
    }, (1 * 60 * 1000)), // 15 min
    baseProducto = {
        estado: "no cargado",
        fechaUpdate: null,
        producto: []
    },
    baseStock = {
        estado: "no cargado",
        fechaUpdate: null,
        stock: []
    },
    debug = true;

const sistemaLoadBaseData = (fecha = null, force = false) => {
    console.log("Sistema: sistemaLoadBaseData cancelado.");
    return false;
    let idVentana = ventanaAlertaFlotante("Acción en proceso...", "Cargando base de productos y base de stock de productos.<br>Esto puede llevar un tiempo prolongado dependiendo de la velocidad de internet y la cantidad de productos registrados que tengas.");
    setTimeout(() => {
        $("#" + idVentana + "processContainer").html(loading());
        sistemaLoadBaseProducto(((fecha == null || fecha == "") ? null : fecha), force, idVentana);
    }, 1500);
}

const sistemaLoadBaseProducto = (fecha = null, force = false, idVentana = null, chunk = 0) => {
    if (idVentana !== null) {
        if (chunk == 0) {
            $("#" + idVentana + "processContainer .loader").remove();
            $("#" + idVentana + "processContainer").html('<div class="progress"><div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div></div>');
        }
    } else {
        idVentana = ventanaAlertaFlotante("Acción en proceso...", "Cargando base de productos.<br>Esto puede llevar un tiempo prolongado dependiendo de la velocidad de internet y la cantidad de productos registrados que tengas.<br><b>Te avisaremos cuando la carga finalice.</b>");
        $("#" + idVentana + "processContainer").html(loading());
    }
    let formData = new FormData();
    formData.append("force", (force == true || baseProducto.producto == null || baseProducto.producto.length == 0) ? true : false);
    formData.append("fecha", fecha);
    formData.append("chunk", chunk);
    this.serverRequest =
        axios({
            method: "post",
            url: "./engine/control/compania/base-producto.php",
            data: formData,
            headers: { "Content-Type": "multipart/form-data" }
        })
        .then((result) => {
                if (debug) console.log(result);
                if (result.status === 200) {
                    if (debug) console.log("sistemaReloadBaseProductos status 200 ok");
                    if (result.data["status"] === true) {
                        if (debug) console.log("sistemaReloadBaseProductos status true ok");
                        if (parseInt(result.data["data"]["array"]["chunk"]["actual"]) <= result.data["data"]["array"]["chunk"]["totales"]) {

                            if (parseInt(result.data["data"]["array"]["chunk"]["actual"]) in baseProducto.producto) {
                                baseProducto.producto[parseInt(result.data["data"]["array"]["chunk"]["actual"])] = result.data["data"]["array"]["producto"];
                            } else {
                                //baseProducto.producto.push(result.data["data"]["array"]["producto"]);
                            }
                            baseProducto.estado = "Cargando";
                            let porcentaje = parseInt(Math.round(parseInt(result.data["data"]["array"]["chunk"]["actual"]) * 100 / result.data["data"]["array"]["chunk"]["totales"]));
                            $("#" + idVentana + "processContainer .progress .progress-bar").css({ "width": porcentaje + "%" });
                            $("#" + idVentana + "processContainer .progress .progress-bar").attr("aria-valuenow", porcentaje);
                            setTimeout(() => { sistemaLoadBaseProducto(fecha, force, idVentana, (parseInt(result.data["data"]["array"]["chunk"]["actual"]) + 1)); }, 225);

                        } else {
                            baseProducto.fechaUpdate = fecha;
                            baseProducto.estado = "Cargado";
                            if (idVentana !== null && $("#" + idVentana).length > 0) {
                                $("#" + idVentana + "processContainer").html("<span><b>Resultado carga base de productos:</b> base de productos y stock cargada satisfactoriamente.</span>");
                            } else {
                                setTimeout(() => { ventanaAlertaFlotante("Información!", "<span><b>Resultado carga base de productos:</b> base de productos y stock cargada satisfactoriamente.</span>"); }, 750);
                            }
                        }
                    } else {
                        if (idVentana !== null && $("#" + idVentana).length > 0) {
                            $("#" + idVentana + "processContainer").html("<span><b>Resultado carga base de productos:</b> " + result.data["mensajeUser"]);
                        } else {
                            setTimeout(() => { handleFail(result.data["mensajeUser"], "Información de base de productos!") }, 450);
                        }
                    }
                }
            },
            (error) => {
                handleFail("Ocurrió un error al cargar la base de productos. <br><br>Request error, " + error);
            }
        )
        .catch(function(error) {
            handleFail("Ocurrió un error al cargar la base de productos. <br><br>Catch request error, " + error);
        });
}

const handleFail = (mensaje = null, titulo = "Advertencia", form = null, process = null, callback = null) => {
    if (form !== null) $(form).show(150);
    if (process !== null) $(process).hide(150);
    ventanaAlertaFlotante(titulo, mensaje, callback);
}

const handleSuccess = (mensaje = null, titulo = "Correcto!", callback = null) => {
    ventanaAlertaFlotante(titulo, mensaje, callback);
}

const handleRunning = () => {
    alert("Ya se está realizando una solicitud. Aguarde un momento e intente nuevamente...");
}

const handleBeforeSend = () => {

}

const handleComplete = () => {

}

const ventanaAlertaFlotante = (titulo = "Advertencia", mensaje = null, callback = null) => {
    let hex = randomHexa(64);

    let idVentana = "";

    for (let i = 0; i < 3; i++) {
        let r2 = prime[Math.floor((Math.random() * 5) + 2)]
        let r1 = Math.floor((Math.random() * (64 - r2)) + 1);
        idVentana += ((idVentana.length > 0) ? "-" : "") + hex.substr(r1, r2);
    }

    let container = document.createElement("div");
    container.className = "ventana-flotante";
    container.id = idVentana;

    let ventanaContainer = document.createElement("div");
    ventanaContainer.className = "ventana-container";

    let ventanaHeaderContainer = document.createElement("div");
    ventanaHeaderContainer.className = "ventana-header-container";

    let headerTituloSpan = document.createElement("span");
    headerTituloSpan.className = "ventana-header-span";
    headerTituloSpan.innerHTML = titulo;

    let ventanaBodyContainer = document.createElement("div");
    ventanaBodyContainer.className = "ventana-body-container";

    let bodyMensajeSpan = document.createElement("span");
    bodyMensajeSpan.className = "ventana-body-span";
    bodyMensajeSpan.innerHTML = (mensaje !== null && mensaje.length > 0) ? mensaje : "Error desconocido.";

    let ventanaBodyProcessContainer = document.createElement("div");
    ventanaBodyProcessContainer.className = "process-container";
    ventanaBodyProcessContainer.id = idVentana + "processContainer";

    let bodyBotonContainer = document.createElement("div");
    bodyBotonContainer.className = "d-flex p-1 " + ((callback === null) ? "justify-content-end" : "justify-content-around");

    let bodyBotonLeft = document.createElement("button");
    bodyBotonLeft.type = "button";
    bodyBotonLeft.className = "btn btn-primary";
    bodyBotonLeft.innerHTML = "Aceptar";
    bodyBotonLeft.onclick = () => {
        if (callback === null) {
            $("#" + idVentana).remove();
        } else {
            callback();
            $("#" + idVentana).remove();
        }
    }

    bodyBotonContainer.appendChild(bodyBotonLeft);

    if (callback !== null) {
        let bodyBotonRight = document.createElement("button");
        bodyBotonRight.type = "button";
        bodyBotonRight.className = "btn btn-outline-danger";
        bodyBotonRight.innerHTML = "Cancelar";
        bodyBotonRight.onclick = () => {
            $(".ventana-flotante").remove();
        }

        bodyBotonContainer.appendChild(bodyBotonRight);
    }

    ventanaBodyContainer.appendChild(bodyMensajeSpan);
    ventanaBodyContainer.appendChild(ventanaBodyProcessContainer);
    ventanaBodyContainer.appendChild(bodyBotonContainer);
    ventanaHeaderContainer.appendChild(headerTituloSpan);
    ventanaContainer.appendChild(ventanaHeaderContainer);
    ventanaContainer.appendChild(ventanaBodyContainer);
    container.appendChild(ventanaContainer);

    document.getElementsByTagName("body")[0].appendChild(container);
    //dragElement(document.getElementById(idVentana), " .ventana-header-container");
    $("#" + idVentana + " .btn").focus();
    return idVentana;
}

const cartErase = () => { cart = []; }

const beep1 = new Audio("./sound/scanner-beep.mp3");

const filtrarPorPropiedad = (arr, prop, val, skip = null) => {
    var filtrado = [];
    arr.map((data, i) => {
        if (i == skip) {
            filtrado.push(data);
        } else {
            for (var key in data) {
                if (typeof(data[key] == "object")) {
                    var item = data[key];
                    if (item[prop] != val) {
                        filtrado.push([item]);
                    }
                }
            }
        }
    });
    return filtrado;
}

const charter = (container, tipo, labels = ["test 1", "test 2", "test 3"], datasets = [{
    label: "# test X",
    data: [8, 4, 12],
    backgroundColor: ['rgba(255, 99, 132, 0.8)',
        'rgba(54, 162, 235, 0.8)',
        'rgba(255, 206, 86, 0.8)'
    ],
    borderColor: [
        'rgba(255, 99, 132, 1)',
        'rgba(54, 162, 235, 1)',
        'rgba(255, 206, 86, 1)'
    ],
    borderWidth: 1
}]) => {
    //<canvas id="myChart"></canvas>
    var ctx = document.getElementById(container).getContext('2d');
    let chartType = ['line', 'bar', 'radar', 'pie', 'doughnut', 'polarArea', 'bubble'];
    var myChart = new Chart(ctx, {
        type: chartType[tipo],
        data: {
            labels: labels,
            datasets: datasets
        },
        options: {

        }
    });
}

const stockRegistroPRoductoListaFormularioSetStock = (idProducto, tipo = ['stock', 'minimo', 'maximo', 'precio', 'precioMayorista', 'precioKiosco']) => {
    tipo.map((data) => {
        replaceClass("#producto-" + idProducto + " #" + data, "opacity-0", "");
    })
}

const ventaPagoReset = (total = null) => {
    var aPagar = parseFloat((total == null) ? $("#tabla-venta-productos #total").html() : total).toFixed(2);
    $("#container-pago-1, #container-pago-2, #container-pago-3, #container-pago-4, #container-pago-5, #container-pago-6, #container-pago-7, #container-pago-8").addClass("d-none").find("*").attr("disabled", true).find("input").val("0");
    $("#container-pago-3 #cuota, #container-pago-5 #cuota, #container-pago-6 #cuota").val("1");
    $("#container-pre-obs").html("");
    $("#pre-total").html(aPagar);
    $("#pre-pagar").html(aPagar);
    $("#pre-vuelto").html("0");
}

const calculaPreTotal = (container, total = null) => {
    let selector, data, debito, preTotal = parseFloat((total == null) ? $("#tabla-venta-productos #total").html() : total).toFixed(2),
        interes, nuevoValor, resto;
    switch (parseInt($("#pago").val())) {
        case 3:
            selector = document.getElementById(container).querySelector("#cuota");
            data = selector.options[selector.selectedIndex].dataset;
            interes = parseFloat(data.interes).toFixed(4);
            nuevoValor = (preTotal * interes).toFixed(2);
            $("#pre-total").html(nuevoValor);
            $("#container-pre-obs").html('(' + data.cuotas + ' cuotas de $' + ((nuevoValor / data.cuotas).toFixed(2)) + ')');
            break;
        case 4:
            contado = parseFloat($("#" + container + " #monto-contado").val());
            debito = parseFloat($("#" + container + " #monto-debito").val());
            $("#pre-total").html(preTotal);
            $("#container-pre-obs").html('($' + contado + ' contado + $' + debito + ' débito)');
            break;
        case 5:
            selector = document.getElementById(container).querySelector("#cuota");
            data = selector.options[selector.selectedIndex].dataset;
            contado = parseFloat($("#" + container + " #monto-contado").val());
            credito = parseFloat($("#" + container + " #monto-credito").val());
            interes = parseFloat(data.interes).toFixed(4);
            nuevoValor = parseFloat(((credito * interes) + contado).toFixed(2));
            //console.log(credito + " X " + interes + " + " + contado + " + " + resto + " = " + nuevoValor);
            $("#pre-total").html(nuevoValor);
            $("#container-pre-obs").html('($' + contado + ' contado + $' + credito + ' en ' + data.cuotas + ' cuotas de $' + (((credito * interes) / data.cuotas).toFixed(2)) + ')');
            break;
        case 6:
            selector = document.getElementById(container).querySelector("#cuota");
            data = selector.options[selector.selectedIndex].dataset;
            debito = parseFloat($("#" + container + " #monto-debito").val());
            credito = parseFloat($("#" + container + " #monto-credito").val());
            interes = parseFloat(data.interes).toFixed(4);
            nuevoValor = parseFloat(((credito * interes) + debito).toFixed(2));
            //console.log(credito + " X " + interes + " + " + debito + " + " + resto + " = " + nuevoValor);
            $("#pre-total").html(nuevoValor);
            $("#container-pre-obs").html('($' + debito + ' débito + $' + credito + ' en ' + data.cuotas + ' cuotas de $' + (((credito * interes) / data.cuotas).toFixed(2)) + ')')
            break;
        default:
            console.log("no " + parseInt($("#pago").val()) + " " + $("#pago").val());
            break;
    }

}

const cajaCalculaTotal = () => {
    let data = document.getElementById("lista-productos-agregados").childNodes;
    let subtotal = 0.00;
    let descuento = $("#descuento").val();
    for (let i = 1; i <= (data.length - 1); i++) {
        let idProducto = data[i].dataset.idProducto;
        let pos = data[i].dataset.pos;
        let cantidad = $("#producto-" + pos + "-" + idProducto + "-cantidad").val();
        let precio = parseFloat($("#producto-" + pos + "-" + idProducto + "-precio").val());
        subtotal += cantidad * precio;
    }
    if (!$("#iva").is(":checked")) {
        subtotal = subtotal - (subtotal / 100 * 21);
    }
    subtotal = subtotal - (subtotal / 100 * descuento);
    $('#total').html(subtotal.toFixed(2));
}

const cajaCalculaTotalBruto = () => {
    let data = document.getElementById("lista-productos-agregados").childNodes;
    let prodsubtotal = 0.00;
    let iva = 21;
    for (let i = 1; i <= (data.length - 1); i++) {
        let idProducto = data[i].dataset.idProducto;
        let pos = data[i].dataset.pos;
        let cantidad = $("#producto-" + pos + "-" + idProducto + "-cantidad").val();
        let precio = parseFloat($("#producto-" + pos + "-" + idProducto + "-precio").val());
        prodsubtotal += cantidad * precio;
    }
    prodsubtotal = parseFloat(prodsubtotal);
    let subtotal = prodsubtotal - ((prodsubtotal / 100) * iva);
    $("#subtotal").html(subtotal.toFixed(2));
    cajaCalculaTotal();
}

const cajaCalculaProductoPrecioBruto = (pos, idProducto) => {
    let cantidad = parseInt($('#producto-' + pos + '-' + idProducto + '-cantidad').val());
    let precio = parseFloat($('#producto-' + pos + '-' + idProducto + '-precio').val());
    let newValue = (cantidad * precio).toFixed(2);
    $('#producto-' + pos + '-' + idProducto + '-total-bruto').html(newValue);
}

const tailSelectSet = (componente, search = true, classNames = ["flex-grow-1, w-100"]) => {
    return tail.select(componente, {
        search: search,
        classNames: classNames,
        deselect: true
    });
}

const dataTableSet = (componente, sort = false, lengthMenu = [
    [8, 25, 50, 100, -1],
    [8, 25, 50, 100, "Todos"]
], pageLength = 8, order = [0, "desc"]) => {
    $(componente).DataTable({
        "sDom": '<"d-flex justify-content-between"lfp>rt<"d-flex justify-content-between"ip><"clear">',
        "lengthMenu": lengthMenu,
        "pageLength": parseInt(pageLength),
        "bSort": sort,
        "order": [order],
        "language": {
            "decimal": "",
            "emptyTable": "No hay información para mostrar.",
            "info": "Mostrando página _PAGE_ de _PAGES_",
            "infoEmpty": "Mostrando 0 a 0 de 0 registros",
            "infoFiltered": "(filtrado de _MAX_ total de registros)",
            "infoPostFix": "",
            "thousands": ",",
            "lengthMenu": "Mostrar _MENU_ registros.",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": "Buscar:",
            "zeroRecords": "No se encontraron coincidencias.",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            },
            "aria": {
                "sortAscending": ": activar para ordenar ascendentemente",
                "sortDescending": ": activar para ordenar descendientemente"
            }
        }
    });
}

function ventaProductoAgregarInput(idParent, productoBuscado, productoNombre = null, precio = 0, sucursal = null, compania = null) {

    let producto = [];

    //let base = document.querySelector("#companiaProductoLista li[id='7794529041424']");

    let productoEncontrado = null;

    if (productoBuscado != null && productoBuscado != "") {
        productoEncontrado = document.querySelector("#companiaProductoLista li[data-producto-codigoBarra='" + productoBuscado + "']");
        //console.log(productoEncontrado);
        if (productoEncontrado != null) {
            producto = {
                data: {
                    id: productoEncontrado.dataset.productoId,
                    nombre: productoEncontrado.dataset.productoNombre,
                    codigoBarra: productoEncontrado.dataset.productoCodigobarra
                },
                stock: {
                    id: productoEncontrado.dataset.stockId,
                    stock: productoEncontrado.dataset.stockStock,
                    precio: productoEncontrado.dataset.stockPrecio,
                    precioMayorista: productoEncontrado.dataset.stockPreciomayorista,
                    precioKiosco: productoEncontrado.dataset.stockPreciokiosco
                }
            }

            if (producto.data.codigoBarra !== null) {
                if (producto.stock == null || producto.stock.stock == null || producto.stock.stock <= 0) {
                    ventanaAlertaFlotante("Advertencia!", "El producto " + producto.data.nombre + " se encuentra sin stock.");
                    return;
                }

                if (producto.stock.precio === null || producto.stock.precio <= 0 || producto.stock.precio == "") {
                    ventanaAlertaFlotante("Advertencia!", "El producto " + producto.data.nombre + " no tiene un precio registrado.");
                    return;
                }
            }
            //producto = (productoBuscado.substring(0, 3) == "PFC") ? baseProducto.producto[Math.floor(productoEncontrado[0] / productoChunkLimit)].noCodificado.lista[productoEncontrado[0]] : baseProducto.producto[Math.floor(productoEncontrado[0] / productoChunkLimit)].codificado.lista[productoEncontrado[0]];
        } else {
            ventanaAlertaFlotante("Advertencia!", "El producto no se encontró en la base de productos.");
            return;
        }
    } else {
        producto = {
            data: {
                id: 0,
                nombre: (productoNombre == null || productoNombre == "") ? "VARIOS" : productoNombre,
                codigoBarra: null
            },
            stock: {
                id: 0,
                stock: 500,
                precio: precio,
                precioMayorista: 0,
                precioKiosco: 0,
            }
        };
    }

    let cantidad = document.getElementById(idParent + "-agregados").childElementCount;
    let container = document.createElement("tr");
    container.id = "producto-" + cantidad + "-" + producto.data.id;
    container.setAttribute("data-id-producto", producto.data.id);
    container.setAttribute("data-producto", producto.data.nombre);
    container.setAttribute("data-stock", producto.stock.stock);
    container.setAttribute("data-precio", producto.stock.precio);
    container.setAttribute("data-precio-mayorista", producto.stock.precioMayorista);
    container.setAttribute("data-precio-kiosco", producto.stock.precioKiosco);
    container.setAttribute("data-bar-code", producto.data.codigoBarra);
    container.setAttribute("data-pos", cantidad);

    var icon = document.createElement("i");
    icon.className = "fa fa-trash-o";

    var btn = document.createElement("button");
    btn.type = "button";
    btn.id = "btn-acc-" + producto.data.id;
    btn.onclick = () => {
        $("#producto-" + cantidad + "-" + producto.data.id).remove();
        cajaCalculaTotalBruto();
    };
    btn.className = "btn btn-outline-danger";
    btn.appendChild(icon);

    let inputContainer0 = document.createElement("td");
    inputContainer0.className = "align-middle";
    inputContainer0.appendChild(btn);

    let inputContainer1 = document.createElement("td");
    inputContainer1.id = "producto-" + cantidad + "-" + producto.data.id + "-barcode";

    let inputContainer2 = document.createElement("td");
    inputContainer2.className = "align-middle";
    inputContainer2.style.cssText = "line-height: 1em;";
    inputContainer2.innerHTML = producto.data.nombre + "<br><small class='text-muted'>- " + producto.data.codigoBarra + " -</small>";

    let input5 = document.createElement("select");
    input5.className = "form-control";
    input5.id = "producto-" + cantidad + "-" + producto.data.id + "-precio";
    input5.onchange = () => {
        cajaCalculaProductoPrecioBruto(cantidad, producto.data.id);
        $("#producto-" + cantidad + "-" + producto.data.id + "-precio-unitario").val($("#producto-" + cantidad + "-" + producto.data.id + "-precio").val());
        cajaCalculaTotalBruto();
        cajaCalculaTotal();
    }

    var option1 = document.createElement("option");
    option1.value = producto.stock.precio;
    option1.text = "$" + producto.stock.precio + " (Minorista)";
    if (isNaN(parseFloat(producto.stock.precio))) option1.setAttribute("disabled", true);
    var option2 = document.createElement("option");
    option2.value = producto.stock.precioMayorista;
    option2.text = "$" + producto.stock.precioMayorista + " (Mayorista)";
    if (isNaN(parseFloat(producto.stock.precioMayorista))) { option2.setAttribute("disabled", true); }
    var option3 = document.createElement("option");
    option3.value = producto.stock.precioKiosco;
    option3.text = "$" + producto.stock.precioKiosco + " (Kiosco)";
    /* ========================
        Precio kiosco
        LA24 , Sucursal #3
    ======================== */
    if (parseInt(compania) == 2 && parseInt(sucursal) == 3) {
        option3.setAttribute("selected", "selected");
    }
    if (isNaN(parseFloat(producto.stock.precioKiosco))) option3.setAttribute("disabled", true);

    input5.appendChild(option1);
    input5.appendChild(option2);
    input5.appendChild(option3);

    let inputContainer3 = document.createElement("td");
    inputContainer3.className = "text-center align-middle";
    inputContainer3.appendChild(input5);

    let input1 = document.createElement("input");
    input1.type = "number";
    input1.className = "form-control";
    input1.id = "producto-" + cantidad + "-" + producto.data.id + "-cantidad";
    input1.name = "producto-cantidad[]";
    input1.max = producto.stock.stock;
    input1.min = 0;
    input1.value = 1;
    input1.onkeyup = (e) => {
        /*
        console.log(e.keyCode);
        console.log(e.key);
        console.log(typeof e.key);
        console.log(isNaN(e.key));
        console.log(isNaN(parseInt(e.key)));
        */
        console.log(e.currentTarget.id);
        //cajaCalculaProductoPrecioBruto(cantidad, producto.data.id);
        if ((document.activeElement === document.getElementById(e.currentTarget.id))) {
            var keycode = (e.keyCode ? e.keyCode : e.which);
            if (keycode == '9' || keycode == '17' || keycode == '39' || keycode == '13') {
                if (producto.data.codigoBarra !== null) {
                    $("#container-producto #producto").focus();
                } else {
                    $("#tipoProducto").prop("checked", true);
                    ventaRegistrarFormularioUpdatetipoProducto();
                }
                return;
            }
            if (!isNaN(parseInt(e.key))) {
                let inputVal = parseInt(e.currentTarget.value),
                    min = 0,
                    max = producto.stock.stock;
                let totalBruto = (inputVal * producto.stock.precio).toFixed(2);
                if (inputVal < min) {
                    totalBruto = (min * producto.stock.precio).toFixed(2);
                    //console.log(totalBruto);
                    $("#" + e.currentTarget.id).val(min);
                    alert("El valor ingresado es incorrecto.");
                } else if (inputVal > max) {
                    totalBruto = (max * producto.stock.precio).toFixed(2);
                    //console.log(totalBruto);
                    $("#" + e.currentTarget.id).val(max);
                    alert("El valor ingresado supera el stock del producto. Stock disponible: " + max);
                }
                $('#producto-' + cantidad + '-' + producto.data.id + '-total-bruto').html(totalBruto);
                setTimeout(cajaCalculaTotalBruto(), 1000);
            } else {
                $("#" + e.currentTarget.id).val(1)
                alert("El valor ingresado en la cantidad del producto es incorrecto. 0 (cero) es el valor mínimo.");
            }
            setTimeout(() => {
                cajaCalculaProductoPrecioBruto(cantidad, producto.data.id);
                setTimeout(() => {
                    cajaCalculaTotalBruto();
                    setTimeout(() => {
                        cajaCalculaTotal();
                    }, 350);
                }, 350);
            }, 350);
        } else {
            //console.log(document.activeElement);
            //console.log(document.getElementById(e.currentTarget.id));
        }
    };

    let input2 = document.createElement("input");
    input2.type = "number";
    input2.className = "form-control d-none";
    input2.setAttribute("readonly", true);
    input2.id = "producto-" + cantidad + "-" + producto.data.id + "-identificador";
    input2.name = "producto-identificador[]";
    input2.value = producto.stock.id;

    let input7 = document.createElement("input");
    input7.type = "number";
    input7.className = "form-control d-none";
    input7.setAttribute("readonly", true);
    input7.id = "producto-" + cantidad + "-" + producto.data.id + "-identificador-producto";
    input7.name = "producto-identificador-producto[]";
    input7.value = producto.data.id;

    let input3 = document.createElement("input");
    input3.type = "text";
    input3.className = "form-control d-none";
    input3.setAttribute("readonly", true);
    input3.id = "producto-" + cantidad + "-" + producto.data.id + "-precio-unitario";
    input3.name = "producto-precio-unitario[]";
    input3.value = producto.stock.precio;

    let input4 = document.createElement("input");
    input4.type = "text";
    input4.className = "form-control d-none";
    input4.setAttribute("readonly", true);
    input4.id = "producto-" + cantidad + "-" + producto.data.id + "-descripcion";
    input4.name = "producto-descripcion[]";
    input4.value = producto.data.nombre;

    let input6 = document.createElement("input");
    input6.type = "text";
    input6.className = "form-control d-none";
    input6.setAttribute("readonly", true);
    input6.id = "producto-" + cantidad + "-" + producto.data.id + "-tipo";
    input6.name = "producto-tipo[]";
    input6.value = (productoBuscado != null && productoBuscado.substring(0, 3) == "PFC") ? "noCodificado" : "codificado";

    let inputContainer4 = document.createElement("td");
    inputContainer4.className = "align-middle";
    inputContainer4.appendChild(input1);

    let inputContainer5 = document.createElement("td");
    inputContainer5.className = "align-middle";
    inputContainer5.innerHTML = '$<span id="producto-' + cantidad + '-' + producto.data.id + '-total-bruto">' + producto.stock.precio + '</span>';

    inputContainer0.appendChild(input2);
    inputContainer0.appendChild(input3);
    inputContainer0.appendChild(input4);
    inputContainer0.appendChild(input6);
    inputContainer0.appendChild(input7);
    container.appendChild(inputContainer0);
    container.appendChild(inputContainer1);
    container.appendChild(inputContainer2);
    container.appendChild(inputContainer3);
    container.appendChild(inputContainer4);
    container.appendChild(inputContainer5);

    document.getElementById(idParent + "-agregados").appendChild(container);

    if (producto.data.codigoBarra !== null && false) {
        const format = ["CODE128", "CODE39", "EAN13", "EAN8", "EAN5", "EAN2", "UPC", "ITF", "ITF-14", "MSI", "MSI10", "MSI11", "MSI1010", "MSI1110", "Pharmacode", "Codabar"];
        switch (true) {
            case (producto.data.codigoBarra.substring(0, 3) == "PFC"):
                setFormat = format[0];
                break;
            case (producto.data.codigoBarra.length <= 2):
                setFormat = format[5];
                break;
            case (producto.data.codigoBarra.length > 2 && producto.data.codigoBarra.length <= 5):
                setFormat = format[4];
                break;
            case (producto.data.codigoBarra.length > 5 && producto.data.codigoBarra.length <= 8):
                setFormat = format[3];
                break;
            case (producto.data.codigoBarra.length > 8 && producto.data.codigoBarra.length < 13):
                setFormat = format[6];
                break;
            case (producto.data.codigoBarra.length == 13):
                setFormat = format[2];
                break;
            default:
                setFormat = format[2];
                break;
        }
        console.log(setFormat);
        let divDOM = document.getElementById("producto-" + cantidad + "-" + producto.data.id + "-barcode");
        let svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
        svg.setAttribute('jsbarcode-format', setFormat);
        svg.setAttribute('jsbarcode-value', producto.data.codigoBarra);
        svg.setAttribute('jsbarcode-width', 1);
        svg.setAttribute('jsbarcode-height', 45);
        svg.setAttribute('jsbarcode-fontSize', 11);
        svg.className.baseVal = "barcode";
        divDOM.appendChild(svg);

        JsBarcode(".barcode").init();
    }

    beep1.volume = 0.5;
    beep1.play();

    let containerGeneral = document.getElementById("tabla-venta-productos");
    containerGeneral.scrollTop = containerGeneral.scrollHeight;

    setTimeout(() => { $("#producto").focus() }, 1000);

    return cantidad;
}

function stockProductoAgregarInput(idParent, productoBuscado, productoNombre = null, precio = 0) {

    document.getElementById(idParent).innerHTML = "";
    document.getElementById(idParent).innerHTML = loading();

    let producto = [];

    //let base = document.querySelector("#companiaProductoLista li[id='7794529041424']");

    let productoEncontrado = null;

    if (productoBuscado != null && productoBuscado != "") {
        if (isNaN(parseInt(productoBuscado)) && !productoBuscado.includes("PFC-")) {
            console.log("es nan");
            productoEncontrado = document.querySelectorAll("#companiaProductoLista li[data-producto-nombre*='" + productoBuscado + "' i]");
        } else {
            console.log("es num");
            productoEncontrado = document.querySelector("#companiaProductoLista li[data-producto-codigoBarra='" + productoBuscado + "' i]");
        }

        //console.log(productoEncontrado);
        if (productoEncontrado != null) {
            if (isNaN(parseInt(productoBuscado)) && !productoBuscado.includes("PFC-")) {
                for (let index in productoEncontrado) {
                    if (!isNaN(parseInt(index))) {
                        producto.push({
                            data: {
                                id: productoEncontrado[index].dataset.productoId,
                                nombre: productoEncontrado[index].dataset.productoNombre,
                                codigoBarra: productoEncontrado[index].dataset.productoCodigobarra
                            },
                            stock: {
                                id: productoEncontrado[index].dataset.stockId,
                                stock: productoEncontrado[index].dataset.stockStock,
                                precio: productoEncontrado[index].dataset.stockPrecio,
                                precioMayorista: productoEncontrado[index].dataset.stockPreciomayorista,
                                precioKiosco: productoEncontrado[index].dataset.stockPreciokiosco
                            }
                        })
                    }
                }
            } else {
                producto.push({
                    data: {
                        id: productoEncontrado.dataset.productoId,
                        nombre: productoEncontrado.dataset.productoNombre,
                        codigoBarra: productoEncontrado.dataset.productoCodigobarra
                    },
                    stock: {
                        id: productoEncontrado.dataset.stockId,
                        stock: productoEncontrado.dataset.stockStock,
                        precio: productoEncontrado.dataset.stockPrecio,
                        precioMayorista: productoEncontrado.dataset.stockPreciomayorista,
                        precioKiosco: productoEncontrado.dataset.stockPreciokiosco
                    }
                })
                if (producto[0].data.codigoBarra !== null) {
                    if (producto[0].stock == null || producto[0].stock.stock == null || producto[0].stock.stock <= 0) {
                        ventanaAlertaFlotante("Advertencia!", "El producto " + producto[0].data.nombre + " se encuentra sin stock.");
                    }

                    if (producto[0].stock.precio === null || producto[0].stock.precio <= 0 || producto[0].stock.precio == "") {
                        ventanaAlertaFlotante("Advertencia!", "El producto " + producto[0].data.nombre + " no tiene un precio registrado.");
                    }
                }
            }
            //producto = (productoBuscado.substring(0, 3) == "PFC") ? baseProducto.producto[Math.floor(productoEncontrado[0] / productoChunkLimit)].noCodificado.lista[productoEncontrado[0]] : baseProducto.producto[Math.floor(productoEncontrado[0] / productoChunkLimit)].codificado.lista[productoEncontrado[0]];
        } else {
            document.getElementById(idParent).innerHTML = "Producto no encontrado.";
            $("#buscador-input").focus();
            return;
        }
    } else {
        producto.push({
            data: {
                id: 0,
                nombre: (productoNombre == null || productoNombre == "") ? "VARIOS" : productoNombre,
                codigoBarra: null
            },
            stock: {
                id: 0,
                stock: 500,
                precio: precio,
                precioMayorista: 0,
                precioKiosco: 0,
            }
        });
    }

    /*
    console.log("Lista productos");
    console.log(producto);
    */

    document.getElementById(idParent).innerHTML = "";

    let accordionContainer = document.createElement("div");
    accordionContainer.id = "accordion-1";



    producto.map((productoData, i) => {
        console.log(productoData);
        setTimeout(() => {

            let containerGeneral = document.createElement("div");

            // INPUT CODIGO =======================

            let inputCodigo = document.createElement("input");
            inputCodigo.type = "text";
            inputCodigo.className = "form-control";
            inputCodigo.value = productoData.data.codigoBarra;
            inputCodigo.setAttribute("readonly", true);

            let inputCodigoLabel = document.createElement("label");
            inputCodigoLabel.className = "col-form-label";
            inputCodigoLabel.setAttribute("for", "codigoBarra");
            inputCodigoLabel.innerHTML = "<i class='fa fa-barcode'></i> Código";

            let inputCodigoContainer = document.createElement("div");
            inputCodigoContainer.className = "form-group";
            // =====================================

            // INPUT NOMBRE ======================= 
            let inputNombre = document.createElement("input");
            inputNombre.type = "text";
            inputNombre.className = "form-control";
            inputNombre.id = "nombre";
            inputNombre.name = "nombre";
            inputNombre.value = productoData.data.nombre;

            let inputNombreLabel = document.createElement("label");
            inputNombreLabel.className = "col-form-label";
            inputNombreLabel.setAttribute("for", "nombre");
            inputNombreLabel.innerHTML = "<i class='fa fa-tag'></i> Nombre";

            let inputNombreContainer = document.createElement("div");
            inputNombreContainer.className = "form-group flex-grow-1 mr-2";
            // =====================================

            // INPUT ID ======================= 
            let inputIdLabel = document.createElement("label");
            inputIdLabel.className = "col-form-label";
            inputIdLabel.setAttribute("for", "idProducto");
            inputIdLabel.innerHTML = "Identificador";

            let inputId = document.createElement("input");
            inputId.type = "text";
            inputId.className = "form-control";
            inputId.id = "idProducto";
            inputId.name = "idProducto";
            inputId.value = productoData.data.id;
            inputId.setAttribute("readonly", true);

            let inputIdContainer = document.createElement("div");
            inputIdContainer.className = "form-group mr-2 d-none";
            // =====================================


            let productoInformacionContainer = document.createElement("div");
            productoInformacionContainer.className = "d-flex flex-wrap";
            productoInformacionContainer.style.padding = ".25rem 2.5rem";

            let productoTitulo = document.createElement("h5");
            productoTitulo.className = "font-weight-bold";
            productoTitulo.innerText = "Información básica";
            let productoTituloContainer = document.createElement("div");

            let containerProducto = document.createElement("div");
            containerProducto.className = "mb-2";
            containerProducto.style.padding = ".5rem 7rem";

            let containerProcess = document.createElement("div");
            containerProcess.id = "stock-producto-" + productoData.data.id + "-stock-" + productoData.stock.id + "-process";
            containerProcess.style.display = "none";


            let containerForm = document.createElement("form");
            containerForm.id = "stock-producto-" + productoData.data.id + "-stock-" + productoData.stock.id + "-form";
            containerForm.action = "./engine/compania/producto-stock-editar-formulario-registro.php";
            containerForm.setAttribute("form", "#stock-producto-" + productoData.data.id + "-stock-" + productoData.stock.id + "-form");
            containerForm.setAttribute("process", "#stock-producto-" + productoData.data.id + "-stock-" + productoData.stock.id + "-process");
            containerForm.setAttribute("data-codigo-barra", productoData.data.codigoBarra);
            containerForm.setAttribute("data-tipo", (productoData.data.codigoBarra.substring(0, 3) != "PFC") ? "codificado" : "noCodificado");


            inputIdContainer.appendChild(inputIdLabel);
            inputIdContainer.appendChild(inputId);

            inputNombreContainer.appendChild(inputNombreLabel);
            inputNombreContainer.appendChild(inputNombre);

            inputCodigoContainer.appendChild(inputCodigoLabel);
            inputCodigoContainer.appendChild(inputCodigo);

            productoInformacionContainer.appendChild(inputIdContainer);
            productoInformacionContainer.appendChild(inputNombreContainer);
            productoInformacionContainer.appendChild(inputCodigoContainer);

            productoTituloContainer.appendChild(productoTitulo);

            containerProducto.appendChild(productoTituloContainer);

            containerProducto.appendChild(productoInformacionContainer);

            /* FORMULARIO STOCK */
            let stockInformacionContainer = document.createElement("div");
            stockInformacionContainer.className = "d-flex justify-content-start flex-wrap";
            stockInformacionContainer.style.padding = ".25rem 2.5rem";

            let stockTitulo = document.createElement("h5");
            stockTitulo.className = "font-weight-bold";
            stockTitulo.innerText = "Stock";
            let stockTituloContainer = document.createElement("div");

            let containerStock = document.createElement("div");
            containerStock.className = "mb-2";
            containerStock.style.padding = ".5rem 7rem";

            // INPUT STOCK ID ======================= 
            let inputStockIdLabel = document.createElement("label");
            inputStockIdLabel.className = "col-form-label";
            inputStockIdLabel.setAttribute("for", "idStock");
            inputStockIdLabel.innerHTML = "Identificador Stock";

            let inputStockId = document.createElement("input");
            inputStockId.type = "text";
            inputStockId.className = "form-control";
            inputStockId.id = "idStock";
            inputStockId.name = "idStock";
            inputStockId.value = productoData.stock.id;
            inputStockId.setAttribute("readonly", true);

            let inputStockIdContainer = document.createElement("div");
            inputStockIdContainer.className = "form-group mr-2 d-none";
            inputStockIdContainer.appendChild(inputStockIdLabel);
            inputStockIdContainer.appendChild(inputStockId);

            // ========================================

            // INPUT STOCK DISPONIBLE ======================= 
            let inputStockLabel = document.createElement("label");
            inputStockLabel.className = "col-form-label";
            inputStockLabel.setAttribute("for", "stock");
            inputStockLabel.innerText = "Disponible";

            let inputStock = document.createElement("input");
            inputStock.type = "text";
            inputStock.className = "form-control";
            inputStock.id = "stock";
            inputStock.name = "stock";
            inputStock.value = productoData.stock.stock;
            inputStock.setAttribute("min", 0);

            let inputStockContainer = document.createElement("div");
            inputStockContainer.className = "form-group mr-2";
            inputStockContainer.appendChild(inputStockLabel);
            inputStockContainer.appendChild(inputStock);
            // ========================================

            // INPUT STOCK PRECIO MINORISTA ======================= 
            let inputStockPrecioLabel = document.createElement("label");
            inputStockPrecioLabel.className = "control-label";
            inputStockPrecioLabel.setAttribute("for", "stock");
            inputStockPrecioLabel.innerText = "Precio minorista";

            let inputStockPrecioContainer = document.createElement("div");
            inputStockPrecioContainer.className = "form-group";

            let inputStockPrecioContainerInner = document.createElement("div");
            inputStockPrecioContainerInner.className = "input-group mb-3";

            let inputStockPrecioContainerInnerPrependContainer = document.createElement("div");
            inputStockPrecioContainerInnerPrependContainer.className = "input-group-prepend";

            let inputStockPrecioContainerInnerPrependContainerSpan = document.createElement("span");
            inputStockPrecioContainerInnerPrependContainerSpan.className = "input-group-text";
            inputStockPrecioContainerInnerPrependContainerSpan.innerText = "$";

            inputStockPrecioContainerInnerPrependContainer.appendChild(inputStockPrecioContainerInnerPrependContainerSpan);

            let inputStockPrecio = document.createElement("input");
            inputStockPrecio.type = "text";
            inputStockPrecio.className = "form-control";
            inputStockPrecio.id = "precio";
            inputStockPrecio.name = "precio";
            inputStockPrecio.value = (isNaN(parseInt(productoData.stock.precio))) ? 0 : parseInt(productoData.stock.precio);
            inputStock.setAttribute("min", 0);

            let inputStockPrecioContainerInnerAppendContainer = document.createElement("div");
            inputStockPrecioContainerInnerAppendContainer.className = "input-group-append";

            let inputStockPrecioContainerInnerAppendContainerSpan = document.createElement("span");
            inputStockPrecioContainerInnerAppendContainerSpan.className = "input-group-text";
            inputStockPrecioContainerInnerAppendContainerSpan.innerText = ".00";

            inputStockPrecioContainerInnerAppendContainer.appendChild(inputStockPrecioContainerInnerAppendContainerSpan);

            let inputStockPrecioContainerGeneral = document.createElement("div");
            inputStockPrecioContainerGeneral.className = "form-group";

            inputStockPrecioContainerInner.appendChild(inputStockPrecioContainerInnerPrependContainer);
            inputStockPrecioContainerInner.appendChild(inputStockPrecio);
            inputStockPrecioContainerInner.appendChild(inputStockPrecioContainerInnerAppendContainer);
            inputStockPrecioContainer.appendChild(inputStockPrecioContainerInner);
            inputStockPrecioContainerGeneral.appendChild(inputStockPrecioLabel);
            inputStockPrecioContainerGeneral.appendChild(inputStockPrecioContainer);
            // ========================================

            // INPUT STOCK PRECIO MAYORISTA ======================= 
            let inputStockPrecioMayoristaLabel = document.createElement("label");
            inputStockPrecioMayoristaLabel.className = "control-label";
            inputStockPrecioMayoristaLabel.setAttribute("for", "stock");
            inputStockPrecioMayoristaLabel.innerText = "Precio mayorista";

            let inputStockPrecioMayoristaContainer = document.createElement("div");
            inputStockPrecioMayoristaContainer.className = "form-group";

            let inputStockPrecioMayoristaContainerInner = document.createElement("div");
            inputStockPrecioMayoristaContainerInner.className = "input-group mb-3";

            let inputStockPrecioMayoristaContainerInnerPrependContainer = document.createElement("div");
            inputStockPrecioMayoristaContainerInnerPrependContainer.className = "input-group-prepend";

            let inputStockPrecioMayoristaContainerInnerPrependContainerSpan = document.createElement("span");
            inputStockPrecioMayoristaContainerInnerPrependContainerSpan.className = "input-group-text";
            inputStockPrecioMayoristaContainerInnerPrependContainerSpan.innerText = "$";

            inputStockPrecioMayoristaContainerInnerPrependContainer.appendChild(inputStockPrecioMayoristaContainerInnerPrependContainerSpan);

            let inputStockPrecioMayorista = document.createElement("input");
            inputStockPrecioMayorista.type = "text";
            inputStockPrecioMayorista.className = "form-control";
            inputStockPrecioMayorista.id = "precioMayorista";
            inputStockPrecioMayorista.name = "precioMayorista";
            inputStockPrecioMayorista.value = (isNaN(parseInt(productoData.stock.precioMayorista))) ? 0 : parseInt(productoData.stock.precioMayorista);
            inputStockPrecioMayorista.setAttribute("min", 0);

            let inputStockPrecioMayoristaContainerInnerAppendContainer = document.createElement("div");
            inputStockPrecioMayoristaContainerInnerAppendContainer.className = "input-group-append";

            let inputStockPrecioMayoristaContainerInnerAppendContainerSpan = document.createElement("span");
            inputStockPrecioMayoristaContainerInnerAppendContainerSpan.className = "input-group-text";
            inputStockPrecioMayoristaContainerInnerAppendContainerSpan.innerText = ".00";

            inputStockPrecioMayoristaContainerInnerAppendContainer.appendChild(inputStockPrecioMayoristaContainerInnerAppendContainerSpan);

            let inputStockPrecioMayoristaContainerGeneral = document.createElement("div");
            inputStockPrecioMayoristaContainerGeneral.className = "form-group";

            inputStockPrecioMayoristaContainerInner.appendChild(inputStockPrecioMayoristaContainerInnerPrependContainer);
            inputStockPrecioMayoristaContainerInner.appendChild(inputStockPrecioMayorista);
            inputStockPrecioMayoristaContainerInner.appendChild(inputStockPrecioMayoristaContainerInnerAppendContainer);
            inputStockPrecioMayoristaContainer.appendChild(inputStockPrecioMayoristaContainerInner);
            inputStockPrecioMayoristaContainerGeneral.appendChild(inputStockPrecioMayoristaLabel);
            inputStockPrecioMayoristaContainerGeneral.appendChild(inputStockPrecioMayoristaContainer);
            // ========================================

            // INPUT STOCK PRECIO KIOSCO ======================= 
            let inputStockPrecioKioscoLabel = document.createElement("label");
            inputStockPrecioKioscoLabel.className = "control-label";
            inputStockPrecioKioscoLabel.setAttribute("for", "stock");
            inputStockPrecioKioscoLabel.innerText = "Precio kiosco";

            let inputStockPrecioKioscoContainer = document.createElement("div");
            inputStockPrecioKioscoContainer.className = "form-group";

            let inputStockPrecioKioscoContainerInner = document.createElement("div");
            inputStockPrecioKioscoContainerInner.className = "input-group mb-3";

            let inputStockPrecioKioscoContainerInnerPrependContainer = document.createElement("div");
            inputStockPrecioKioscoContainerInnerPrependContainer.className = "input-group-prepend";

            let inputStockPrecioKioscoContainerInnerPrependContainerSpan = document.createElement("span");
            inputStockPrecioKioscoContainerInnerPrependContainerSpan.className = "input-group-text";
            inputStockPrecioKioscoContainerInnerPrependContainerSpan.innerText = "$";

            inputStockPrecioKioscoContainerInnerPrependContainer.appendChild(inputStockPrecioKioscoContainerInnerPrependContainerSpan);

            let inputStockPrecioKiosco = document.createElement("input");
            inputStockPrecioKiosco.type = "text";
            inputStockPrecioKiosco.className = "form-control";
            inputStockPrecioKiosco.id = "precioKiosco";
            inputStockPrecioKiosco.name = "precioKiosco";
            inputStockPrecioKiosco.value = (isNaN(parseInt(productoData.stock.precioKiosco))) ? 0 : parseInt(productoData.stock.precioKiosco);
            inputStockPrecioKiosco.setAttribute("min", 0);

            let inputStockPrecioKioscoContainerInnerAppendContainer = document.createElement("div");
            inputStockPrecioKioscoContainerInnerAppendContainer.className = "input-group-append";

            let inputStockPrecioKioscoContainerInnerAppendContainerSpan = document.createElement("span");
            inputStockPrecioKioscoContainerInnerAppendContainerSpan.className = "input-group-text";
            inputStockPrecioKioscoContainerInnerAppendContainerSpan.innerText = ".00";

            inputStockPrecioKioscoContainerInnerAppendContainer.appendChild(inputStockPrecioKioscoContainerInnerAppendContainerSpan);

            let inputStockPrecioKioscoContainerGeneral = document.createElement("div");
            inputStockPrecioKioscoContainerGeneral.className = "form-group";

            inputStockPrecioKioscoContainerInner.appendChild(inputStockPrecioKioscoContainerInnerPrependContainer);
            inputStockPrecioKioscoContainerInner.appendChild(inputStockPrecioKiosco);
            inputStockPrecioKioscoContainerInner.appendChild(inputStockPrecioKioscoContainerInnerAppendContainer);
            inputStockPrecioKioscoContainer.appendChild(inputStockPrecioKioscoContainerInner);
            inputStockPrecioKioscoContainerGeneral.appendChild(inputStockPrecioKioscoLabel);
            inputStockPrecioKioscoContainerGeneral.appendChild(inputStockPrecioKioscoContainer);
            // ========================================


            // CONTAINER STOCK PRECIOS =======================
            let stockInformacionPreciosContainer = document.createElement("div");
            stockInformacionPreciosContainer.className = "d-flex justify-content-between w-100 flex-wrap";

            stockInformacionPreciosContainer.appendChild(inputStockPrecioContainerGeneral);

            stockInformacionPreciosContainer.appendChild(inputStockPrecioMayoristaContainerGeneral);

            stockInformacionPreciosContainer.appendChild(inputStockPrecioKioscoContainerGeneral);
            // =========================================

            // BOTON GUARDADO
            let boton = document.createElement("button");
            boton.type = "button";
            boton.className = "btn btn-success";
            boton.onclick = () => {
                compañiaStockEditarFormularioRegistro(productoData.data.id, productoData.stock.id);
            }
            boton.innerHTML = "<i class='fa fa-pencil-square-o'></i> Guardar cambios";

            let botonContainer = document.createElement("div");
            botonContainer.className = "form-group d-flex justify-content-center";
            botonContainer.appendChild(boton);
            // =========================================

            stockInformacionContainer.appendChild(inputStockIdContainer);
            stockInformacionContainer.appendChild(inputStockContainer);
            stockInformacionContainer.appendChild(stockInformacionPreciosContainer);

            stockTituloContainer.appendChild(stockTitulo);

            containerStock.appendChild(stockTituloContainer);
            containerStock.appendChild(stockInformacionContainer);
            containerStock.appendChild(botonContainer);


            // WRAP FINAL 

            containerForm.appendChild(containerProducto);
            containerForm.appendChild(containerStock);

            containerGeneral.appendChild(containerProcess);
            containerGeneral.appendChild(containerForm);


            let accordionCard = document.createElement("div");
            accordionCard.className = "card";

            let accordionCardHeader = document.createElement("div");
            accordionCardHeader.className = "card-header";
            accordionCardHeader.id = "heading-" + i;

            let accordionCardHeaderH5 = document.createElement("h5");
            accordionCardHeaderH5.className = "mb-0";

            let accordionCardHeaderBtn = document.createElement("button");
            accordionCardHeaderBtn.className = "btn btn-link font-weight-bold d-flex justify-content-between w-100";
            accordionCardHeaderBtn.innerHTML = "<span>" + productoData.data.nombre.toUpperCase() + "</span><i class='fa fa-chevron-down'></i>";
            accordionCardHeaderBtn.setAttribute("data-toggle", "collapse");
            accordionCardHeaderBtn.setAttribute("data-target", "#collapse-" + i);
            accordionCardHeaderBtn.setAttribute("aria-expanded", false);
            accordionCardHeaderBtn.setAttribute("aria-controls", "collapse-" + i);

            accordionCardHeaderH5.appendChild(accordionCardHeaderBtn);
            accordionCardHeader.appendChild(accordionCardHeaderH5)

            let accordionCardCollapse = document.createElement("div");
            accordionCardCollapse.className = "collapse";
            accordionCardCollapse.id = "collapse-" + i;
            accordionCardCollapse.setAttribute("aria-labelledby", "heading-" + i);
            accordionCardCollapse.setAttribute("data-parent", "#accordion-1");

            let accordionCardCollapseBody = document.createElement("div");
            accordionCardCollapseBody.className = "card-body";

            accordionCardCollapseBody.appendChild(containerGeneral);
            accordionCardCollapse.appendChild(accordionCardCollapseBody);

            accordionCard.appendChild(accordionCardHeader);
            accordionCard.appendChild(accordionCardCollapse);
            accordionContainer.appendChild(accordionCard);

            setTimeout(() => {
                //document.getElementById(idParent).appendChild(containerGeneral);
                document.getElementById(idParent).appendChild(accordionContainer);
                setTimeout(() => {
                    $("#stock-producto-form #nombre").focus();
                }, 150);
            }, 250);
        }, 100);
    })

}

function agregarInput(idParent, value, placeholder = null) {
    let data = value.split(",");
    data = data.map((input) => { return input.trim().replace(/\W/g, '') });
    data = data.filter((input) => { return input.length > 0 });
    let cantidad = document.getElementById(idParent + "-agregadas").childElementCount;
    data.map((input, i) => {
        var field = document.createElement("input")
        field.className = "form-control d-none";
        field.value = input.trim();
        field.type = "text";
        field.setAttribute("readonly", true);
        field.id = idParent + "-" + (cantidad + i) + input;
        field.name = idParent + "[]";
        field.placeholder = (placeholder != null) ? placeholder : "";
        document.getElementById(idParent + "-agregadas").appendChild(field);
    });
    data.map((input, i) => {
        var icon = document.createElement("i");
        icon.className = "fa fa-times";

        var btn = document.createElement("button");
        btn.type = "button";
        btn.id = "btn-act-" + (cantidad + i);
        btn.onclick = () => {
            $("#badge-" + (cantidad + i) + input).remove();
            $("#" + idParent + "-" + (cantidad + i) + input).remove();
        };
        btn.setAttribute("key", (cantidad + i));
        btn.className = "btn btn-sm btn-outline-danger border-0 ml-2";
        btn.style['zoom'] = "70%";
        btn.appendChild(icon);

        var span = document.createElement("span");
        span.id = "badge-" + (cantidad + i) + input;
        span.className = "badge border p-2 mr-2";
        span.innerHTML = input;
        span.appendChild(btn);

        document.getElementById(idParent + "-badge").appendChild(span);
    });
}

const ventaRegistrarFormularioUpdatetipoProducto = () => {
    if ($('#tipoProducto').is(':checked')) {
        $("#container-producto").fadeIn("slow").find("*").prop("disabled", false).val("");
        $("#container-producto-no-codificado").val("").fadeOut(100).find("*").prop("disabled", true);
        $("#container-producto-lista").find("li").css({ display: "none" });
        $("#tipoProductoLabel").html("producto codificado");
        $("#container-producto #producto").focus();
    } else {
        $("#container-producto").fadeOut(100).find("*").prop("disabled", true).val("");
        $("#container-producto-no-codificado").fadeIn("slow").find("*").prop("disabled", false).val("");
        $("#tipoProductoLabel").html("producto no codificado");
        $("#container-producto-no-codificado #precio").focus();
    }
}

const ventaRegistrarFormularioUpdateBusquedaCliente = (total = null) => {
    if ($('#tipoCliente').is(':checked')) {
        $("#container-cliente:not(.tail-select)").fadeOut(100).find("*").prop("disabled", true);
        $("#tipoClienteLabel").html("Comprador ocasional");
    } else {
        $("#container-cliente:not(.tail-select)").fadeIn(100).find("*").prop("disabled", false);
        $("#tipoClienteLabel").html("Cliente");
    }
    $("#pago").val("");
    updatePago();
    ventaPagoReset(total);
}

const ventaRegistrarFormularioUpdateBusqueda = () => {
    console.log("cancelada!");
    return;
    if ($('#tipoCliente1').is(':checked')) {
        $("#container-cliente").prop("selected", () => { return this.defaultSelected; }).fadeOut(100).find("*").prop("disabled", true);
    }
    if ($('#tipoCliente2').is(':checked')) {
        $("#container-cliente").prop("selected", () => { return this.defaultSelected; }).fadeIn("slow").find("*").prop("disabled", false);
    }
}

const clienteBuscarFormularioUpdateBusqueda = () => {
    if ($('#filtroOpcion1').is(':checked')) {
        $("#container-nombre").val("").fadeIn("slow").find("*").prop("disabled", false);
        $("#container-documento").val("").fadeOut(100).find("*").prop("disabled", true);
        $("#cliente-buscar-form #nombre").focus();
    }
    if ($('#filtroOpcion2').is(':checked')) {
        $("#container-nombre").val("").fadeOut(100).find("*").prop("disabled", true);
        $("#container-documento").val("").fadeIn("slow").find("*").prop("disabled", false);
        $("#cliente-buscar-form #documento").focus();
    }
}

const compañiaRegistroProductoUpdateBusqueda = () => {
    if ($('#filtroOpcion1').is(':checked')) {
        $("#container-tag").val("").fadeIn("slow").find("*").prop("disabled", false);
        $("#container-codigo").val("").fadeOut(100).find("*").prop("disabled", true);
        $("#tag").focus();
    }
    if ($('#filtroOpcion2').is(':checked')) {
        $("#container-tag").val("").fadeOut(100).find("*").prop("disabled", true);
        $("#container-codigo").val("").fadeIn("slow").find("*").prop("disabled", false);
        $("#codigo").focus();
    }
}

const barCode = (div, input) => {
    const format = ["CODE128", "CODE39", "EAN13", "EAN8", "EAN5", "EAN2", "UPC", "ITF", "ITF-14", "MSI", "MSI10", "MSI11", "MSI1010", "MSI1110", "Pharmacode", "Codabar"];
    switch (true) {
        case (input.length <= 2):
            setFormat = format[5];
            break;
        case (input.length > 2 && input.length <= 5):
            setFormat = format[4];
            break;
        case (input.length > 5 && input.length <= 8):
            setFormat = format[3];
            break;
        case (input.length > 8 && input.length < 13):
            setFormat = format[6];
            break;
        case (input.length == 13):
            setFormat = format[2];
            break;
        default:
            setFormat = format[0];
            break;
    }
    JsBarcode(div, input, {
        format: setFormat,
        lineColor: "#000",
        width: 1,
        height: 45,
        displayValue: true,
        fontSize: 11
    });
}

const swapClass = (obj, cssClass) => {
    ($(obj).hasClass(cssClass)) ? $(obj).removeClass(cssClass): $(obj).addClass(cssClass);
}

const replaceClass = (obj, hasClass, replaceClass) => {
    ($(obj).hasClass(hasClass)) ? $(obj).removeClass(hasClass).addClass(replaceClass): console.log("info", "mine-action.js - replaceClass - El objeto no posee la clase indicada [" + hasClass + "].");
}

const removeElement = (obj) => {
    $(obj).remove();
}

const loadUsuarioTareasPendientes = () => {
    if (document.body.contains(document.getElementById("container-tareas-pendientes"))) {
        tareasPendientesLoadHeader(true);
    } else {
        appendElement('./includes/componente/usuario-tareas-pendientes.php', 'body');
    }
}

const loadUsuarioAlerta1 = () => {
    headerUsuarioAlerta(1);
}

const loadUsuarioAlerta2 = () => {
    headerUsuarioAlerta(2);
}

const loadUsuarioAlerta = () => {
    loadUsuarioAlerta1();
    loadUsuarioAlerta2();
}

const unloadUsuarioTareasPendientes = () => {
    tareasPendientesListaOption();
    removeElement("#container-tareas-pendientes");
}

const retry = (func, secs = 5) => {
    console.log('function ' + arguments.callee.caller.toString() + ' delayed...');
    setTimeout(func(), (secs * 1000));
}

const appendElement = (objUrl, objToAppend, data = {}, clear = false, loadingBar = false) => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = objToAppend;
    let divForm = "";
    $.ajax({
        type: "POST",
        url: objUrl,
        timeout: 45000,
        beforeSend: function() {
            if (loadingBar) { $(divProcess).html(loading()) }
            //$(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: { data: data },
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                if (clear) { $(divProcess).html(""); }
                $(divProcess).append(data);
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const headerUsuarioMainData = () => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = "#container-header-usuario";
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/componente/header-usuario.php",
        timeout: 45000,
        beforeSend: function() {
            //$(divProcess).load("./includes/loading.php");
            //$(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: {},
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).html(data);
            }, 1000);
            setTimeout(() => {
                headerUsuarioMainData();
            }, (2 * 60 * 1000));
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const headerUsuarioAlerta = (id) => {
    let divProcess = "#container-header-usuario-alerta-" + id;
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/componente/header-usuario-alerta.php",
        timeout: 45000,
        beforeSend: function() {
            //$(divProcess).load("./includes/loading.php");
            //$(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: { id: id },
        complete: function() {},
        success: function(data) {
            setTimeout(function() {
                $(divProcess).html(data);
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
    });
}

const sistemaTest = (id) => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = "#right-content-process";
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "includes/sistema/test.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            $(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: { id: id },
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const compañiaStockEditarFormularioRegistro = (idProducto, idStock) => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let form = $("#stock-producto-" + idProducto + "-stock-" + idStock + "-form");
    let data = form.serializeArray();
    data.push({ name: "idProducto2", value: idProducto });
    data.push({ name: "idStock2", value: idStock });
    data.push({ name: "codigoBarra", value: form.attr("data-codigo-barra") });
    data.push({ name: "tipo", value: form.attr("data-tipo") });
    data.push({ name: "exceptions", value: ["exceptions"] });
    data.push({ name: "form", value: form.attr("form") });
    data.push({ name: "process", value: form.attr("process") });
    let divProcess = form.attr("process");
    let divForm = form.attr("form");
    $.ajax({
        type: "POST",
        url: form.attr("action"),
        timeout: 45000,
        beforeSend: function() {
            $(divForm).hide(350);
            $(divProcess).show(350);
            $(divProcess).html(loading());
        },
        data: data,
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const sistemaConsultaProductoNuevoActualizado = (force = false, alerta = false) => {
    console.log("Sistema: consultando base de productos actualizados.");
    let divProcess = "#right-content-alerts";
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "engine/compania/consulta-producto-nuevo-actualizado.php",
        timeout: 45000,
        beforeSend: function() {
            //$(divProcess).html(loading());
            //$(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: { venta: ((document.getElementById("container-venta-formulario") !== null) ? true : false), force: force, alerta: alerta },
        complete: function() {},
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
    });
}

const caTicketFormulario = () => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = "#right-content-process";
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "includes/sistema/ca/ticket-formulario.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            $(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: {},
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const caTicketFormularioRegistro = () => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let form = $("#ticket-form");
    let data = form.serializeArray();
    data.push({ name: "form", value: form.attr("form") });
    data.push({ name: "process", value: form.attr("process") });
    let divProcess = form.attr("process");
    let divForm = form.attr("form");
    $.ajax({
        type: "POST",
        url: form.attr("action"),
        timeout: 45000,
        beforeSend: function() {
            $(divForm).hide(350);
            $(divProcess).show(350);
            $(divProcess).html(loading());
        },
        data: data,
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const caTicketVisualizarFormulario = (idTicket, hash, admin = false) => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = "#ca-main-ticket-lista-process";
    let divForm = "#ca-main-ticket-lista";
    $.ajax({
        type: "POST",
        url: "includes/sistema/ca/ticket-visualizar-formulario.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            $(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: { idTicket: idTicket, hash: hash, admin: admin, process: divProcess, form: divForm },
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const caTicketComentarioFormularioRegistro = (idTicket) => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let form = $("#ticket-comentario-form");
    let data = form.serializeArray();
    data.push({ name: "idTicket", value: idTicket });
    data.push({ name: "form", value: form.attr("form") });
    data.push({ name: "process", value: form.attr("process") });
    let divProcess = form.attr("process");
    let divForm = form.attr("form");
    $.ajax({
        type: "POST",
        url: form.attr("action"),
        timeout: 45000,
        beforeSend: function() {
            //$(divForm).hide(350);
            $(divProcess).show(350);
            $(divProcess).html(loading());
        },
        data: data,
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
                goToByScroll(divForm);
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const caTicketComentarioRecarga = (idTicket, fecha = null) => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = "#ca-main-ticket-lista-process .ca-ticket-body";
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "engine/sistema/ca/ticket-comentario-recarga.php",
        timeout: 45000,
        beforeSend: function() {
            //$(divProcess).append(loading());
            //$(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: { idTicket: idTicket, fecha: fecha, process: divProcess },
        complete: function() {
            me.data('requestRunning', false);
            //$(divProcess + " .loader-container").remove();
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).append(data).fadeIn("slow");
                $("html, body").animate({ scrollTop: $(document).height() }, 1000);
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const caTicketTieneNuevaActividad = () => {
    this.serverRequest =
        axios({
            method: "post",
            url: "./engine/sistema/ca/ticket-tiene-nueva-actividad.php",
            data: []
        })
        .then((result) => {
                if (debug) console.log(result);
                if (result.status === 200) {
                    if (debug) console.log("caTicketTieneNuevaActividad status 200 ok");
                    if (result.data["status"] === true) {
                        if (debug) console.log("caTicketTieneNuevaActividad status true ok");
                        if (result.data["data"]["count"] != null && result.data["data"]["count"] > 0) {
                            $("#left-content .navbar #mesaAyudaActividad").html(result.data["data"]["count"]).toggleClass('d-none');
                            for (key in result.data["data"]["array"]) {
                                if ($(".ca-container.ca-ticket-container")["length"] > 0 && $(".ca-container.ca-ticket-container")[0]["id"] == result.data["data"]["array"][key]["id"]) {
                                    var ultimaActividad = new Date(result.data["data"]["array"][key]["ultimaActividad"]);
                                    ultimaActividad.setSeconds(ultimaActividad.getSeconds() - 1)
                                    var ultimaActividadFormateada = [ultimaActividad.getFullYear(),
                                        (ultimaActividad.getMonth() + 1).padLeft(),
                                        ultimaActividad.getDate().padLeft()
                                    ].join('-') + ' ' + [ultimaActividad.getHours().padLeft(),
                                        ultimaActividad.getMinutes().padLeft(),
                                        ultimaActividad.getSeconds().padLeft()
                                    ].join(':');
                                    setTimeout(() => { caTicketComentarioRecarga(result.data["data"]["array"][key]["id"], ultimaActividadFormateada) }, 350);
                                    setTimeout(() => { caTicketNuevaActividadMensajeAudio.play() }, 1150);
                                }
                            }
                        }
                    } else {
                        handleFail(result.data["mensajeUser"]);
                        console.log(result.data["mensajeAdmin"]);
                    }
                }
            },
            (error) => {
                handleFail("Ocurrió un error al consultar la actividad del centro de ayuda. <br><br>Request error, " + error);
            }
        )
        .catch(function(error) {
            handleFail("Ocurrió un error al consultar la actividad del centro de ayuda. <br><br>Catch request error, " + error);
        });
}

function getRand(min, max) {
    //console.log(Math.floor(Math.random() * max) + min)
    let rand = Math.floor(Math.random() * max) + min;
    return rand;
}

const caTicketNuevaActividadIntervalo = setInterval(() => { caTicketTieneNuevaActividad(); }, (getRand(5, 15) * 60 * 1000)),
    caTicketNuevaActividadMensajeAudio = new Audio('includes/sistema/ca/spl-msg.mp3');

const mesaDeAyuda = () => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = "#right-content-data";
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "includes/sistema/mesa-ayuda.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            $(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: {},
        complete: function() {
            me.data('requestRunning', false);
            if (!$('.main-menu #mesaAyudaActividad').hasClass('d-none')) {
                $('.main-menu #mesaAyudaActividad').toggleClass('d-none');
            }
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const inicio = () => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = "#right-content-data";
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "includes/usuario/inicio.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            $(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: {},
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const compañiaAdministracion = (div = "#right-content-data") => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = div;
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/compania/administracion/gestionar.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            //$(divForm).hide(350);
            //$(divProcess).show(350);
        },
        data: {},
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const compañiaAdministracionUsuario = (div = "#right-content-data") => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = div;
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/compania/administracion/usuario.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            //$(divForm).hide(350);
            //$(divProcess).show(350);
        },
        data: {},
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const compañiaAdministracionSucursal = (div = "#right-content-data") => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = div;
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/compania/administracion/sucursal.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            //$(divForm).hide(350);
            //$(divProcess).show(350);
        },
        data: {},
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const compañiaFacturacion = (div = "#right-content-data") => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = div;
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/compania/facturacion.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            //$(divForm).hide(350);
            //$(divProcess).show(350);
        },
        data: {},
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const requestLogout = () => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = "#right-content";
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./engine/logout.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            //$(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: {},
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const compañiaProductoHistorial = () => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = "#right-content-data";
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "includes/compania/producto-historial.php",
        timeout: 45000,
        beforeSend: function() {
            //$(divForm).hide(350);
            $(divProcess).show(350);
            $(divProcess).html(loading());
        },
        data: {},
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const compañiaProductoHistorialFormulario = (div = "#right-content-data", codigoBarra) => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    productoEncontrado = document.querySelector("#companiaProductoLista li[data-producto-codigoBarra='" + codigoBarra + "']");
    console.log(productoEncontrado);
    producto = [];
    if (productoEncontrado != null) {
        producto = {
                data: {
                    id: productoEncontrado.dataset.productoId,
                    nombre: productoEncontrado.dataset.productoNombre,
                    codigoBarra: productoEncontrado.dataset.productoCodigobarra,
                    fechaUpdate: productoEncontrado.dataset.productoFechaupdate
                },
                stock: {
                    id: productoEncontrado.dataset.stockId,
                    productoId: productoEncontrado.dataset.stockProductoid,
                    productoNCId: productoEncontrado.dataset.stockProductoncid,
                    stock: productoEncontrado.dataset.stockStock,
                    precio: productoEncontrado.dataset.stockPrecio,
                    precioMayorista: productoEncontrado.dataset.stockPreciomayorista,
                    precioKiosco: productoEncontrado.dataset.stockPreciokiosco,
                    operador: productoEncontrado.dataset.stockOperador,
                    fechaModificacion: productoEncontrado.dataset.stockFechamodificacion
                }
            }
            //producto = (productoBuscado.substring(0, 3) == "PFC") ? baseProducto.producto[Math.floor(productoEncontrado[0] / productoChunkLimit)].noCodificado.lista[productoEncontrado[0]] : baseProducto.producto[Math.floor(productoEncontrado[0] / productoChunkLimit)].codificado.lista[productoEncontrado[0]];
    } else {
        ventanaAlertaFlotante("Advertencia!", "El producto no se encontró en la base de productos.");
        return;
    }
    let divProcess = div;
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "engine/compania/producto-historial-formulario.php",
        timeout: 45000,
        beforeSend: function() {
            //$(divForm).hide(350);
            $(divProcess).show(350);
            $(divProcess).html(loading());
        },
        data: { codigoBarra, codigoBarra, producto: producto },
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const compañiaStockRegistroProductoListaFormulario = (div = null) => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let form = $("#compania-stock-registro-producto-form");
    let data = form.serializeArray();
    data.push({ name: "form", value: form.attr("form") });
    data.push({ name: "process", value: form.attr("process") });
    let divProcess = (div != null) ? div : form.attr("process");
    let divForm = form.attr("form");
    $.ajax({
        type: "POST",
        url: form.attr("action"),
        timeout: 45000,
        beforeSend: function() {
            //$(divForm).hide(350);
            $(divProcess).show(350);
            $(divProcess).html(loading());
        },
        data: data,
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const compañiaStockRegistroProductoFormulario = () => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let data = $("#buscador-input").val();
    let divProcess = "#right-content-data";
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/compania/stock-registro-producto-formulario.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            //$(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: { data: data },
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const productoContenido = (idProducto, tipo, productoTipo = "codificado") => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    console.log("exe test");
    let divProcess = "#producto-" + idProducto + " #" + tipo;
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/producto/contenido.php",
        timeout: 45000,
        beforeSend: function() {
            //$(divProcess).html(loading());
            //$(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: { idProducto: idProducto, tipo: tipo, productoTipo: productoTipo },
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const compañiaStockContenidoData = (idProducto, tipo, productoTipo = "codificado") => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    console.log("exe test");
    let divProcess = "#producto-" + idProducto + " #" + tipo;
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/compania/stock-contenido.php",
        timeout: 45000,
        beforeSend: function() {
            //$(divProcess).html(loading());
            //$(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: { idProducto: idProducto, tipo: tipo, productoTipo: productoTipo },
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const successAction = (div, callback = null, loader = "loader") => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = div;
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading(loader));
            //$(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: {},
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                callback();
            }, 750);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const cajaUpdateMonto = (monto) => {
    let caja = document.getElementById("caja-monto");
    if (caja) {
        caja.innerHTML = monto;
    }
}

const tareaAgregarData = (tarea, input, value, div) => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let inputType = $("#" + input).attr("type");
    if ($("#" + input).attr("type") === "checkbox") {
        if (!$("#" + input).is(":checked")) {
            value = 0;
        }
    }
    let divProcess = div;
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./engine/usuario/tarea-agregar-data.php",
        timeout: 45000,
        beforeSend: function() {
            //$(divProcess).html(loading());
            //$(divForm).hide(350);
            //$(divProcess).show(350);
        },
        data: { tarea: tarea, input: input, value: value, inputType: inputType },
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const compañiaStockEditarContenido = (idProducto, tipo, productoTipo = "codificado") => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let form = $("#producto-" + idProducto + "-stock-editar-" + tipo + "-form");
    let data = form.serializeArray();
    data.push({ name: "form", value: form.attr("form") });
    data.push({ name: "process", value: form.attr("process") });
    data.push({ name: "idProducto2", value: idProducto });
    data.push({ name: "productoTipo", value: productoTipo });
    data.push({ name: "tipo2", value: tipo });
    data.push({ name: "exceptions", value: ["exceptions"] });
    let divProcess = form.attr("process");
    let divForm = form.attr("form");
    $.ajax({
        type: "POST",
        url: form.attr("action"),
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            $(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: data,
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const productoRegistro = (codificado = true) => {

    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let form = $("#producto-registro-formulario-form");
    let data = form.serializeArray();
    data.push({ name: "form", value: form.attr("form") });
    data.push({ name: "process", value: form.attr("process") });
    data.push({ name: "codificado", value: codificado });
    data.push({ name: "exceptions", value: ["subcategoria", "stock", "minimo", "maximo", "precio", "precioMayorista", "precioKiosco"] });
    let divProcess = form.attr("process");
    let divForm = form.attr("form");
    $.ajax({
        type: "POST",
        url: form.attr("action"),
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            $(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: data,
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const productoRegistroFormulario = (corroborar = true, codigo = 0, tarea = null) => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = "#right-content-data";
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/producto/registrar-formulario.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            //$(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: { corroborar: corroborar, codigo: codigo, tarea: tarea },
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const productoNoCodifRegistroFormulario = () => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = "#right-content-data";
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/producto/nocodif-registrar-formulario.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            //$(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: {},
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const productoEditarFormulario = (idProducto) => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = "#right-content-data";
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/producto/editar-formulario.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            //$(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: { idProducto: idProducto },
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const productoInventario = (idSucursal = null) => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = "#right-content-data";
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/producto/inventario.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            //$(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: { idSucursal: idSucursal },
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const compañiaStockEditarContenidoFormulario = (producto, tipo, cantidad = 0) => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = "#producto-" + producto + " #" + tipo;
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/compania/stock-editar-contenido-formulario.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading("loader-circle-1"));
            //$(divForm).hide(350);
            //$(divProcess).show(350);
        },
        data: { producto: producto, tipo: tipo, cantidad: cantidad },
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const productoEditarContenidoFormulario = (producto, tipo, value = null, productoTipo = "codificado") => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    console.log("#producto-" + producto + " #" + tipo);
    let divProcess = "#producto-" + producto + " #" + tipo;
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/producto/editar-contenido-formulario.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading("loader-circle-1"));
            //$(divForm).hide(350);
            //$(divProcess).show(350);
        },
        data: { producto: producto, tipo: tipo, value: value, productoTipo: productoTipo },
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const productoEditarContenidoFormularioRegistro = (producto, tipo, value = null) => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let form = $("#producto-" + producto + "-editar-" + tipo + "-form");
    let data = form.serializeArray();
    data.push({ name: "form", value: form.attr("form") });
    data.push({ name: "process", value: form.attr("process") });
    data.push({ name: "idProducto2", value: producto });
    data.push({ name: "tipo2", value: tipo });
    data.push({ name: "exceptions", value: ["exceptions"] });
    let divProcess = form.attr("process");
    let divForm = form.attr("form");
    $.ajax({
        type: "POST",
        url: form.attr("action"),
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            $(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: data,
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const productoInventarioEditarContenidoFormulario = (producto, tipo, cantidad = 0, productoTipo = "codificado") => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = "#producto-" + producto + " #" + tipo;
    let divForm = "";
    console.log(divProcess);
    $.ajax({
        type: "POST",
        url: "./includes/compania/stock-editar-contenido-formulario.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading("loader-circle-1"));
            //$(divForm).hide(350);
            //$(divProcess).show(350);
        },
        data: { producto: producto, tipo: tipo, cantidad: cantidad, productoTipo: productoTipo },
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const productoCorroboraExistencia = () => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let form = $("#producto-corrobora-existencia-form");
    let data = form.serializeArray();
    data.push({ name: "form", value: form.attr("form") });
    data.push({ name: "process", value: form.attr("process") });
    let divProcess = form.attr("process");
    let divForm = form.attr("form");
    $.ajax({
        type: "POST",
        url: form.attr("action"),
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            $(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: data,
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const clienteBuscarFormulario = () => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = "#right-content-data";
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/cliente/buscar-formulario.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            //$(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: {},
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const clienteRegistroFormulario = () => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = "#right-content-data";
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/cliente/registro-formulario.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            //$(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: {},
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const clienteRegistro = () => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let form = $("#cliente-registrar-form");
    let data = form.serializeArray();
    data.push({ name: "form", value: form.attr("form") });
    data.push({ name: "process", value: form.attr("process") });
    data.push({ name: "exceptions", value: ["telefono", "domicilio", "email"] });
    let divProcess = form.attr("process");
    let divForm = form.attr("form");
    $.ajax({
        type: "POST",
        url: form.attr("action"),
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            $(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: data,
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const compañiaStock = () => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = "#right-content-data";
    let divForm = "";
    console.log(divProcess);
    $.ajax({
        type: "POST",
        url: "./includes/compania/stock.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            //$(divForm).hide(350);
            //$(divProcess).show(350);
        },
        data: {},
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const clienteBuscar = () => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let form = $("#cliente-buscar-form");
    let data = form.serializeArray();
    data.push({ name: "form", value: form.attr("form") });
    data.push({ name: "process", value: form.attr("process") });
    let divProcess = form.attr("process");
    let divForm = form.attr("form");
    $.ajax({
        type: "POST",
        url: form.attr("action"),
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            $(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: data,
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const gotoClienteLegajo = (idCliente) => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = "#right-content-data";
    let divForm = "";
    console.log(divProcess);
    $.ajax({
        type: "POST",
        url: "./includes/cliente/legajo.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            //$(divForm).hide(350);
            //$(divProcess).show(350);
        },
        data: { idCliente: idCliente },
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const clienteEditarFormulario = (idCliente = null) => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let form = $("#cliente-edita-form");
    let data = form.serializeArray();
    let divProcess = form.attr("process");
    let divForm = form.attr("form");
    $.ajax({
        type: "POST",
        url: form.attr("action"),
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            $(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: data,
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const cajaPagoFormulario = (idCaja, monto = null, idVenta = null, div = "#right-content-process") => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = div;
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/caja/pago-formulario.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            $(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: { idCaja: idCaja, monto: monto, idVenta: idVenta },
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const cajaActividadFormulario = (div = null) => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = (div !== null) ? div : "#right-content-data";
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/venta/historial.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            $(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: {},
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const cajaActividadRegistrar = () => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let form = $("#caja-actividad-form");
    let data = form.serializeArray();
    data.push({ name: "form", value: form.attr("form") });
    data.push({ name: "process", value: form.attr("process") });
    let divProcess = form.attr("process");
    let divForm = form.attr("form");
    $.ajax({
        type: "POST",
        url: form.attr("action"),
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            $(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: data,
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const cajaAccionRegistrar = (idCaja) => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let form = $("#caja-accion-form");
    let data = form.serializeArray();
    data.push({ name: "form", value: form.attr("form") });
    data.push({ name: "process", value: form.attr("process") });
    data.push({ name: "idCaja", value: idCaja });
    let divProcess = form.attr("process");
    let divForm = form.attr("form");
    $.ajax({
        type: "POST",
        url: form.attr("action"),
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            $(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: data,
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const actividadJornadaVisualizar = (idJornada, div = null) => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = (div !== null) ? div : "#right-content-data";
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/caja/actividad-jornada-visualizar.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            $(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: { idJornada: idJornada },
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const cajaActividadCerrar = (div = null) => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = (div !== null) ? div : "#right-content-data";
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./engine/caja/actividad-cerrar.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            $(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: {},
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const cajaRefreshUI = (monto, idCaja) => {
    let promise1 = cajaUpdateMonto(monto);
    let promise2 = cajaHistorial(idCaja, "#container-caja-historial", true);
    let promise3 = ventaHistorial(idCaja, "#container-ventas-historial", true);
    Promise.all([promise1, promise2, promise3]).catch(function(err) {
        console.log("Promise error: " + err);
    });
}

const clienteRefreshUI = (idCliente, div, small) => {
    let clientePromise1 = clienteCompraLista(idCliente, div, small);
    Promise.all([clientePromise1]).catch(function(err) {
        console.log("Promise error: " + err);
    });
}

const clienteCompraLista = (idCliente, div = "#right-content-data", small = false) => {
    let divProcess = div;
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/cliente/compra-lista.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            $(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: { idCliente: idCliente, small: small },
        complete: function() {},
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
    });
}

const ventaHistorial = (idCaja = null, div = "#right-content-data", small = false) => {
    let divProcess = div;
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/venta/historial.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            $(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: { idCaja: idCaja, small: small },
        complete: function() {},
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
    });
}

const ventaAnularFormulario = (idVenta, div = "#right-content-process") => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = div;
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/venta/anular-formulario.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            //$(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: { idVenta: idVenta },
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const ventaAnular = (idVenta, div = "#right-content-data") => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let form = $("#venta-anular-form");
    let data = form.serializeArray();
    data.push({ name: "form", value: form.attr("form") });
    data.push({ name: "process", value: form.attr("process") });
    data.push({ name: "idVenta2", value: idVenta });
    data.push({ name: "exceptions", value: ["observacion"] });
    let divProcess = form.attr("process");
    let divForm = form.attr("form");
    $.ajax({
        type: "POST",
        url: form.attr("action"),
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            $(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: data,
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const cajaPagoRegistrar = (idVenta) => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let form = $("#caja-pago-form");
    let data = form.serializeArray();
    data.push({ name: "form", value: form.attr("form") });
    data.push({ name: "process", value: form.attr("process") });
    data.push({ name: "idVenta2", value: idVenta });
    let divProcess = form.attr("process");
    let divForm = form.attr("form");
    $.ajax({
        type: "POST",
        url: form.attr("action"),
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            $(divForm).hide(150);
            $(divProcess).show(150);
        },
        data: data,
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const ventaRegistrar = (idCaja) => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let form = $("#venta-registro-form");
    let data = form.serializeArray();
    data.push({ name: "form", value: form.attr("form") });
    data.push({ name: "process", value: form.attr("process") });
    data.push({ name: "idCaja", value: idCaja });
    let divProcess = form.attr("process");
    let divForm = form.attr("form");
    $.ajax({
        type: "POST",
        url: form.attr("action"),
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            $(divForm).hide(150);
            $(divProcess).show(150);
        },
        data: data,
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const ventaRegistrarFormulario = (div = null, small = false) => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = (div !== null) ? div : "#right-content-data";
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/venta/registrar-formulario.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            $(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: { small: small },
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const cajaAccionRegistrarFormulario = (div = null, small = false) => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = (div !== null) ? div : "#container-caja-accion";
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/caja/accion-registrar-formulario.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            $(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: { small: small },
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const facturaVisualizar = (idVenta, nComprobante = null, div = null) => {
    let divProcess = (div !== null) ? div : "#right-content-process";
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/compania/factura-visualizar.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            //$(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: { idVenta: idVenta, nComprobante: nComprobante },
        complete: function() {},
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
    });
}

const sistemaCompañiaSucursalCajaUpdate = (data) => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = "#right-content-process";
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./engine/control/compania/sucursal/caja-update.php",
        timeout: 45000,
        beforeSend: function() {
            //$(divProcess).html(loading());
            //$(divForm).hide(350);
            //$(divProcess).show(350);
        },
        data: { data: data },
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).html(data);
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const sistemaReloadStaticData = (force = false) => {
    let divProcess = "#right-content-process";
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./engine/control/reload-static-data.php",
        timeout: 45000,
        beforeSend: function() {
            //$(divProcess).html(loading());
            //$(divForm).hide(350);
            //$(divProcess).show(350);
        },
        data: { force: force },
        complete: function() {},
        success: function(data) {
            setTimeout(function() {
                $(divProcess).html(data);
                //reloadStaticData = setTimeout(() => { sistemaReloadStaticData() }, 1800000); //1800000
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
    });
}

const jornadaFormulario = (idJornada = null, div = "#right-content-data") => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    if (!$(div).length === 0) {
        me.data('requestRunning', false);
        return;
    }
    let divProcess = div;
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/caja/jornada.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            //$(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: { idJornada: idJornada },
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const cajaHistorial = (idCaja, div = "#container-caja-historial", small = false) => {
    let divProcess = div;
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/caja/historial.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            //$(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: { idCaja: idCaja, small: small },
        complete: function() {},
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
    });
}

const cajaGestion = (idCaja = null, actividad = null) => {
    var me = $(this);
    if (me.data('requestRunning')) {
        console.log("cajaGestion: SALI;");
        return;
    }
    me.data('requestRunning', true);
    let divProcess = "#right-content-data";
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/caja/gestion.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            $(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: { idCaja: idCaja, actividad: actividad },
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
            me.data('requestRunning', false);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const clienteEditar = (idCliente) => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let form = $("#cliente-editar-form");
    let data = form.serializeArray();
    data.push({ name: "form", value: form.attr("form") });
    data.push({ name: "process", value: form.attr("process") });
    data.push({ name: "idCliente2", value: idCliente });
    data.push({ name: "exceptions", value: ["telefono", "domicilio", "email"] });
    let divProcess = form.attr("process");
    let divForm = form.attr("form");
    $.ajax({
        type: "POST",
        url: form.attr("action"),
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            $(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: data,
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}
const adminUsuarioGestionar = () => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = "#right-content-data";
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/administracion/usuario/gestionar.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            //$(divForm).hide(350);
            //$(divProcess).show(350);
        },
        data: {},
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const configurarCompañia = () => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = "#right-content-data";
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/administracion/compania/gestionar.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            //$(divForm).hide(350);
            //$(divProcess).show(350);
        },
        data: {},
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const adminProductoGestionar = () => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = "#right-content-data";
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/administracion/producto/gestionar.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            //$(divForm).hide(350);
            //$(divProcess).show(350);
        },
        data: {},
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const administracionCliente = () => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = "#right-content-data";
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/administracion/cliente/buscar-formulario.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            //$(divForm).hide(350);
            //$(divProcess).show(350);
        },
        data: {},
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}



const administracionClienteFacturacionRegistro = (idCompania) => {
    let me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let form = $("#administracion-cliente-facturacion-form");
    let data = new FormData();
    let formData = form.serializeArray();
    let file = document.getElementById("file").files;
    formData.map((info) => data.append(info.name, info.value));
    $.each(file, (i, fileData) => data.append(i, fileData));
    data.append("idCompañia2", idCompania);
    let divProcess = form.attr("process");
    let divForm = form.attr("form");
    $.ajax({
        type: "POST",
        url: form.attr("action"),
        timeout: 180000,
        beforeSend: function() {
            $(divProcess).html(loading());
            $(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: data,
        enctype: 'multipart/form-data',
        contentType: false,
        cache: false,
        processData: false,
        xhr: function() {
            var jqXHR = null;
            if (window.ActiveXObject) {
                jqXHR = new window.ActiveXObject("Microsoft.XMLHTTP");
            } else {
                jqXHR = new window.XMLHttpRequest();
            }
            jqXHR.upload.addEventListener("progress", function(evt) {
                if (evt.lengthComputable) {
                    var percentComplete = Math.round((evt.loaded * 100) / evt.total);
                    $("#archivo-load-progress-bar").attr('aria-valuenow', percentComplete).css('width', percentComplete + "%");
                    console.log('Uploaded percent', percentComplete);
                }
            }, false);
            return jqXHR;
        },
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        $(divProcess).load("./includes/error.php");
        me.data('requestRunning', false);
    });
}

const administracionClienteFacturacionFormulario = (idCompania) => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = "#administracion-cliente-process";
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/administracion/cliente/facturacion-formulario.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            //$(divForm).hide(350);
            //$(divProcess).show(350);
        },
        data: { idCompania: idCompania },
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const administracionFacturacionGestion = (idCompania) => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = "#administracion-cliente-process";
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/administracion/cliente/facturacion-gestion.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            //$(divForm).hide(350);
            //$(divProcess).show(350);
        },
        data: { idCompania: idCompania },
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const administracionClienteBuscar = () => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let form = $("#compania-buscar-form");
    let data = form.serializeArray();
    data.push({ name: "form", value: "#compania-buscar-form" });
    data.push({ name: "process", value: "#right-content-data" });
    data.push({ name: "compania", value: $("#compania").val() });
    let divProcess = "#right-content-data";
    let divForm = "#compania-buscar-form";
    $.ajax({
        type: "POST",
        url: "./includes/administracion/cliente/gestion.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            $(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: data,
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const compañiaSucursalPedidoFormularioProductoAgregaralCarro = (tag, process = "#sucursal-pedido-producto-lista-process") => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let form = $("#sucursal-pedido-form");
    let data = form.serializeArray();
    data.push({ name: "tag-container", value: tag });
    let tags = document.getElementById(tag);
    if (!tags.hasChildNodes()) {
        alert("Ingrese palabras claves para filtrar");
        me.data('requestRunning', false);
        return;
    }
    let divProcess = process;
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/compania/sucursal-pedido-formulario-producto-filtrar.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            //$(divForm).hide(350);
            //$(divProcess).show(350);
        },
        data: data,
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const compañiaSucursalPedidoFormularioProductoFiltrar = (tag, process = "#sucursal-pedido-producto-lista-process") => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let form = $("#sucursal-pedido-form");
    let data = form.serializeArray();
    data.push({ name: "tag-container", value: tag });
    let tags = document.getElementById(tag);
    if (!tags.hasChildNodes()) {
        alert("Ingrese palabras claves para filtrar");
        me.data('requestRunning', false);
        return;
    }
    let divProcess = process;
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/compania/sucursal-pedido-formulario-producto-filtrar.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            //$(divForm).hide(350);
            //$(divProcess).show(350);
        },
        data: data,
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const sistemaFacturaImpagaAlerta = () => {
    let divProcess = "body";
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/compania/administracion/factura-alerta.php",
        timeout: 45000,
        beforeSend: function() {
            //$(divProcess).html(loading());
            //$(divForm).hide(350);
            //$(divProcess).show(350);
        },
        data: {},
        complete: function() {},
        success: function(data) {
            setTimeout(function() {
                $(divProcess).append(data);
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
    });
}

const compañiaSucursalPedido = () => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = "#right-content-data";
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/compania/sucursal-pedido.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            //$(divForm).hide(350);
            //$(divProcess).show(350);
        },
        data: {},
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const compañiaSucursalPedidoCarritoFormularioRegistrar = () => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let form = $("#carrito-pedido-form");
    let data = form.serializeArray();
    data.push({ name: "process", value: form.attr("process") });
    data.push({ name: "form", value: form.attr("form") });
    data.push({ name: "exceptions", value: ["exceptions,observacion"] });
    let divProcess = form.attr("process");
    let divForm = form.attr("form");
    $.ajax({
        type: "POST",
        url: form.attr("action"),
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            $(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: data,
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const compañiaSucursalPedidoCarritoFormulario = () => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let form = $("#sucursal-pedido-form");
    let data = form.serializeArray();
    cart.map((cartData, i) => {
        if (i > 0) {
            data.push({ name: "producto[" + i + "][nombre]", value: cartData[0]["nombre"] });
            data.push({ name: "producto[" + i + "][idProducto]", value: cartData[0]["idProducto"] });
            data.push({ name: "producto[" + i + "][idStock]", value: cartData[0]["idStock"] });
            data.push({ name: "producto[" + i + "][stock]", value: cartData[0]["stock"] });
            data.push({ name: "producto[" + i + "][codigoBarra]", value: cartData[0]["codigoBarra"] });
            data.push({ name: "producto[" + i + "][precio]", value: cartData[0]["precio"] });
            data.push({ name: "producto[" + i + "][precioTipo]", value: cartData[0]["precioTipo"] });
            data.push({ name: "producto[" + i + "][productoTipo]", value: cartData[0]["productoTipo"] });
            data.push({ name: "producto[" + i + "][cantidad]", value: cartData[0]["cantidad"] });
        } else {
            data.push({ name: "cliente[id]", value: cartData["value"][0]["value"] });
            data.push({ name: "cliente[nombre]", value: cartData["value"][1]["value"] });
        }
    });
    let divProcess = "#right-content-process";
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/compania/sucursal-pedido-carrito-formulario.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            //$(divForm).hide(350);
            //$(divProcess).show(350);
        },
        data: data,
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const compañiaSucursalPedidoFormulario = () => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    cart = [];
    let divProcess = "#right-content-data";
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/compania/sucursal-pedido-formulario.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            //$(divForm).hide(350);
            //$(divProcess).show(350);
        },
        data: {},
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

const nuevaCompania = () => {
    console.log("execute");
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let form = $("#nueva-compania-form");
    let data = form.serializeArray();
    let divProcess = form.attr("process");
    let divForm = form.attr("form");
    $.ajax({
        type: "POST",
        url: form.attr("action"),
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).html(loading());
            $(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: data,
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
                $(divProcess).hide().html(data).fadeIn("slow");
            }, 1000);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.statusText);
        me.data('requestRunning', false);
    });
}

setTimeout(() => { sistemaLoadBaseData(); }, 1500);


Number.prototype.padLeft = function(base, chr) {
    var len = (String(base || 10).length - String(this).length) + 1;
    return len > 0 ? new Array(len).join(chr || '0') + this : this;
}