const loading = (tipo = "loader") => { return ('<span class="' + tipo + '"></span>'); }

const stockRegistroPRoductoListaFormularioSetStock = (idProducto, tipo = ['stock', 'minimo', 'maximo']) => {
    tipo.map((data) => {
        console.log("#producto-" + idProducto + " #" + data);
        replaceClass("#producto-" + idProducto + " #" + data, "opacity-0", "");
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
        $("#compania-stock-registro-producto-form #tag").focus();
    }
    if ($('#filtroOpcion2').is(':checked')) {
        $("#container-tag").val("").fadeOut(100).find("*").prop("disabled", true);
        $("#container-codigo").val("").fadeIn("slow").find("*").prop("disabled", false);
        $("#compania-stock-registro-producto-form #codigo").focus();
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
    if ((input.length == 3) || (input.length == 5) || (input.length == 8)) {
        appendElement("./engine/producto/cantidad-por-prefijo.php", "#producto-corrobora-cantidad", { 'prefijo': input }, true, true);
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

const ventaRegistroFormulario = () => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = "#right-content-data";
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/ventas/nueva.php",
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

const gestionUsuario = () => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = "#right-content-data";
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/administracion/usuario/gestionUsuario.php",
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

const gestionProducto = () => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = "#right-content-data";
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/administracion/usuario/gestionProducto.php",
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

const gestionCliente = () => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let divProcess = "#right-content-data";
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./includes/administracion/cliente/gestionCliente.php",
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