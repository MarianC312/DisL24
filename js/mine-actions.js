const loading = (tipo = "loader") => { return ('<span class="' + tipo + '"></span>'); }

const beep1 = new Audio("./sound/scanner-beep.mp3");

const stockRegistroPRoductoListaFormularioSetStock = (idProducto, tipo = ['stock', 'minimo', 'maximo']) => {
    tipo.map((data) => {
        console.log("#producto-" + idProducto + " #" + data);
        replaceClass("#producto-" + idProducto + " #" + data, "opacity-0", "");
    })
}

const cajaCalculaTotal = () => {
    let data = document.getElementById("lista-productos-agregados").childNodes;
    let subtotal = 0.00;
    let descuento = $("#descuento").val();
    for (let i = 1; i <= (data.length - 1); i++) {
        let idProducto = data[i].dataset.idProducto;
        let pos = data[i].dataset.pos;
        let cantidad = $("#producto-" + pos + "-" + idProducto + "-cantidad").val();
        subtotal += cantidad * data[i].dataset.precio;
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
        prodsubtotal += cantidad * data[i].dataset.precio;
    }
    prodsubtotal = parseFloat(prodsubtotal);
    let subtotal = prodsubtotal - ((prodsubtotal / 100) * iva);
    $("#subtotal").html(subtotal.toFixed(2));
    cajaCalculaTotal();
}

const cajaCalculaProductoPrecioBruto = (pos, idProducto) => {
    let newValue = ($('#producto-' + pos + '-' + idProducto + '-cantidad').val() * parseFloat($('#producto-' + pos + '-' + idProducto + '-precio').text())).toFixed(2);
    $('#producto-' + pos + '-' + idProducto + '-total-bruto').html(newValue);
}

const tailSelectSet = (componente, search = true, classNames = ["flex-grow-1, w-100"]) => {
    tail.select(componente, {
        search: search,
        classNames: classNames
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

function ventaProductoAgregarInput(idParent, dataset) {
    if (dataset.stock <= 0) {
        alert("El producto " + dataset.producto + " se encuentra sin stock.");
        return;
    }

    if (dataset.precio <= 0 || dataset.precio === null || dataset.precio == "") {
        alert("El producto " + dataset.producto + " no tiene un precio registrado.");
        return;
    }

    let cantidad = document.getElementById(idParent + "-agregados").childElementCount;
    let container = document.createElement("tr");
    container.id = "producto-" + cantidad + "-" + dataset.idProducto;
    container.setAttribute("data-id-producto", dataset.idProducto);
    container.setAttribute("data-producto", dataset.producto);
    container.setAttribute("data-stock", dataset.stock);
    container.setAttribute("data-precio", dataset.precio);
    container.setAttribute("data-bar-code", dataset.barCode);
    container.setAttribute("data-pos", cantidad);

    var icon = document.createElement("i");
    icon.className = "fa fa-trash-o";

    var btn = document.createElement("button");
    btn.type = "button";
    btn.id = "btn-acc-" + dataset.idProducto;
    btn.onclick = () => {
        $("#producto-" + cantidad + "-" + dataset.idProducto).remove();
        cajaCalculaTotalBruto();
    };
    btn.className = "btn btn-outline-danger";
    btn.appendChild(icon);

    let inputContainer0 = document.createElement("td");
    inputContainer0.className = "align-middle";
    inputContainer0.appendChild(btn);

    let inputContainer1 = document.createElement("td");
    inputContainer1.id = "producto-" + cantidad + "-" + dataset.idProducto + "-barcode";

    let inputContainer2 = document.createElement("td");
    inputContainer2.className = "align-middle";
    inputContainer2.innerHTML = dataset.producto;

    let inputContainer3 = document.createElement("td");
    inputContainer3.className = "text-center align-middle";
    inputContainer3.innerHTML = '$<span id="producto-' + cantidad + '-' + dataset.idProducto + '-precio">' + dataset.precio + '</span>';

    let input1 = document.createElement("input");
    input1.type = "number";
    input1.className = "form-control";
    input1.id = "producto-" + cantidad + "-" + dataset.idProducto + "-cantidad";
    input1.name = "producto-cantidad[]";
    input1.max = dataset.stock;
    input1.min = 0;
    input1.value = 1;
    input1.onchange = () => {
        cajaCalculaProductoPrecioBruto();
        cajaCalculaTotalBruto();
        cajaCalculaTotal();
    }
    input1.onkeyup = (e) => {
        console.log(e.keyCode);
        //cajaCalculaProductoPrecioBruto(cantidad, dataset.idProducto);
        if ((document.activeElement === document.getElementById(e.currentTarget.id))) {
            var keycode = (e.keyCode ? e.keyCode : e.which);
            if (keycode == '9' || keycode == '17' || keycode == '39' || keycode == '13') {
                $("#container-producto #producto").focus();
            } else if (keycode == '32') {
                ventaRegistrar();
            } else if (!isNaN(e.key)) {
                let inputVal = parseInt(e.currentTarget.value),
                    min = 0,
                    max = dataset.stock;
                let totalBruto = (inputVal * dataset.precio).toFixed(2);
                if (inputVal < min) {
                    totalBruto = (min * dataset.precio).toFixed(2);
                    console.log(totalBruto);
                    $("#" + e.currentTarget.id).val(min);
                    alert("El valor ingresado es incorrecto.");
                } else if (inputVal > max) {
                    totalBruto = (max * dataset.precio).toFixed(2);
                    console.log(totalBruto);
                    $("#" + e.currentTarget.id).val(max);
                    alert("El valor ingresado supera el stock del producto. Stock disponible: " + max);
                }
                $('#producto-' + cantidad + '-' + dataset.idProducto + '-total-bruto').html(totalBruto);
                setTimeout(cajaCalculaTotalBruto(), 1000);
            }
        } else {
            console.log(document.activeElement);
            console.log(document.getElementById(e.currentTarget.id));
        }
    };

    let input2 = document.createElement("input");
    input2.type = "number";
    input2.className = "form-control d-none";
    input2.setAttribute("readonly", true);
    input2.id = "producto-" + cantidad + "-" + dataset.idProducto + "-identificador";
    input2.name = "producto-identificador[]";
    input2.value = dataset.idProducto;

    let inputContainer4 = document.createElement("td");
    inputContainer4.className = "align-middle";
    inputContainer4.appendChild(input1);

    let inputContainer5 = document.createElement("td");
    inputContainer5.className = "align-middle";
    inputContainer5.innerHTML = '$<span id="producto-' + cantidad + '-' + dataset.idProducto + '-total-bruto">' + dataset.precio + '</span>';

    container.appendChild(inputContainer0);
    container.appendChild(input2);
    container.appendChild(inputContainer1);
    container.appendChild(inputContainer2);
    container.appendChild(inputContainer3);
    container.appendChild(inputContainer4);
    container.appendChild(inputContainer5);

    document.getElementById(idParent + "-agregados").appendChild(container);

    const format = ["CODE128", "CODE39", "EAN13", "EAN8", "EAN5", "EAN2", "UPC", "ITF", "ITF-14", "MSI", "MSI10", "MSI11", "MSI1010", "MSI1110", "Pharmacode", "Codabar"];
    switch (true) {
        case (dataset.barCode.length <= 2):
            setFormat = format[5];
            break;
        case (dataset.barCode.length > 2 && dataset.barCode.length <= 5):
            setFormat = format[4];
            break;
        case (dataset.barCode.length > 5 && dataset.barCode.length <= 8):
            setFormat = format[3];
            break;
        case (dataset.barCode.length > 8 && dataset.barCode.length < 13):
            setFormat = format[6];
            break;
        case (dataset.barCode.length == 13):
            setFormat = format[2];
            break;
        default:
            setFormat = format[2];
            break;
    }

    let divDOM = document.getElementById("producto-" + cantidad + "-" + dataset.idProducto + "-barcode");
    let svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
    svg.setAttribute('jsbarcode-format', setFormat);
    svg.setAttribute('jsbarcode-value', dataset.barCode);
    svg.setAttribute('jsbarcode-width', 1);
    svg.setAttribute('jsbarcode-height', 45);
    svg.setAttribute('jsbarcode-fontSize', 11);
    svg.className.baseVal = "barcode";
    divDOM.appendChild(svg);

    JsBarcode(".barcode").init();

    beep1.volume = 0.5;
    beep1.play();

    let containerGeneral = document.getElementById("tabla-venta-productos");
    containerGeneral.scrollTop = containerGeneral.scrollHeight;

    setTimeout($("#producto-" + cantidad + "-" + dataset.idProducto + "-cantidad").focus(), 1000);

    return cantidad;
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

const ventaRegistrarFormularioUpdateBusqueda = () => {
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

const requestLogin = () => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let form = $("#login-form");
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

const compañiaStockRegistroProductoListaFormulario = () => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let form = $("#compania-stock-registro-producto-form");
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
    console.log("exe test");
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

const compañiaStockContenidoData = (idProducto, tipo) => {
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
        data: { idProducto: idProducto, tipo: tipo },
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

const compañiaStockEditarContenido = (idProducto, tipo) => {
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

const productoRegistro = () => {

    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let form = $("#producto-registro-formulario-form");
    let data = form.serializeArray();
    data.push({ name: "form", value: form.attr("form") });
    data.push({ name: "process", value: form.attr("process") });
    data.push({ name: "exceptions", value: ["sucursal", "subcategoria", "fabricante", "venta", "compra", "proveedor"] });
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

const productoInventarioEditarContenidoFormulario = (producto, tipo, cantidad = 0) => {
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

const cajaAccionRegistrar = () => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let form = $("#caja-accion-form");
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

const ventaRegistrar = () => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let form = $("#venta-registro-form");
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

const ventaRegistrarFormulario = (div = null) => {
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

const cajaAccionRegistrarFormulario = (div = null) => {
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

const facturaVisualizar = (idVenta, nComprobante = null, div = null) => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = (div !== null) ? div : "#right-content-data";
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

const cajaHistorial = (div = "#container-caja-historial") => {
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
        url: "./includes/caja/historial.php",
        timeout: 45000,
        beforeSend: function() {
            //$(divProcess).html(loading());
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

const cajaGestion = () => {
    var me = $(this);
    if (me.data('requestRunning')) {
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

const adminClienteGestionar = () => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = "#right-content-data";
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/administracion/cliente/gestionar.php",
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

const buscarCompañiaFormulario = () => {
    console.log("execute");
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let form = $("#compania-buscar-form");
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