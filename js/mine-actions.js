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

const unloadUsuarioTareasPendientes = () => {
    tareasPendientesListaOption();
    removeElement("#container-tareas-pendientes");
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
            //$(divProcess).load("./includes/loading.php");
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
            $(divProcess).load("./includes/loading.php");
            $(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: data,
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
            $(divProcess).load("./includes/loading.php");
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
    let divProcess = div;
    let divForm = "";
    $.ajax({
        type: "POST",
        url: "./engine/usuario/tarea-agregar-data.php",
        timeout: 45000,
        beforeSend: function() {
            $(divProcess).load("./includes/loading.php");
            //$(divForm).hide(350);
            //$(divProcess).show(350);
        },
        data: { tarea: tarea, input: input, value: value },
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
            $(divProcess).load("./includes/loading.php");
            //$(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: { corroborar: corroborar, codigo: codigo, tarea: tarea },
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
            $(divProcess).load("./includes/loading.php");
            $(divForm).hide(350);
            $(divProcess).show(350);
        },
        data: data,
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