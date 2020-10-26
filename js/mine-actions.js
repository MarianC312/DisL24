const loading = (tipo = "loader") => { return ('<span class="' + tipo + '"></span>'); }

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

const appendElement = (objUrl, objToAppend, data = []) => {
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
            //$(divProcess).html(loading());
            //$(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: { data: data },
        complete: function() {
            me.data('requestRunning', false);
        },
        success: function(data) {
            setTimeout(function() {
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

const productoInventarioContenidoData = (idProducto, tipo) => {
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
        url: "./includes/producto/inventario-contenido.php",
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

const productoInventarioEditarContenido = (idProducto, tipo) => {
    var me = $(this);
    if (me.data('requestRunning')) {
        return;
    }
    me.data('requestRunning', true);
    let form = $("#producto-" + idProducto + "-inventario-editar-" + tipo + "-form");
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
    console.log("execute");
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
        url: "./includes/producto/inventario-editar-contenido-formulario.php",
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
            $(divProcess).show(350);
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