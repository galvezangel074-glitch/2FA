// Validaciones del lado del frontend para el formulario de registro.
(function () {
    'use strict';

    var form = document.getElementById('form-registro');
    if (!form) { return; }

    var campos = {
        nombre:       form.elements['nombre'],
        apellido:     form.elements['apellido'],
        correo:       form.elements['correo'],
        nombre_usuario: form.elements['nombre_usuario'],
        clave:       form.elements['clave'],
        confirmacion: form.elements['confirmacion']
    };
    var boton = document.getElementById('btn-registrar');
    var medidor = document.getElementById('medidor');
    var estado = { nombre: false, apellido: false, correo: false, nombre_usuario: false, clave: false, confirmacion: false, sexo: false };
    var temporizador = null;
    var secuencia = 0;
    var temporizadorUsuario = null;
    var secuenciaUsuario = 0;

    function marcar(nombre, ok, mensaje) {
        var cont = document.getElementById('campo-' + nombre);
        var msg = document.getElementById('msg-' + nombre);
        estado[nombre] = ok;
        cont.classList.toggle('valido', ok);
        cont.classList.toggle('invalido', !ok && mensaje !== '');
        msg.textContent = mensaje || '';
        actualizarBoton();
    }

    function actualizarBoton() {
        var todos = Object.keys(estado).every(function (k) { return estado[k]; });
        boton.disabled = !todos;
    }

    function validarTexto(nombre) {
        var v = campos[nombre].value.trim();
        if (v === '') { return marcar(nombre, false, 'Este campo es obligatorio.'); }
        if (!/^[\p{L}\s'\-]+$/u.test(v)) { return marcar(nombre, false, 'Solo letras, espacios, apóstrofo y guion.'); }
        marcar(nombre, true, '');
    }

    function validarCorreo() {
        var v = campos.correo.value.trim().toLowerCase();
        var patron = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
        if (v === '') { return marcar('correo', false, 'Este campo es obligatorio.'); }
        if (!patron.test(v)) { return marcar('correo', false, 'Correo electrónico no válido.'); }
        estado.correo = false;
        actualizarBoton();
        document.getElementById('msg-correo').textContent = 'Verificando disponibilidad…';
        clearTimeout(temporizador);
        var mi = ++secuencia;
        temporizador = setTimeout(function () {
            fetch('ajax/verificar_usuario.php?usuario=' + encodeURIComponent(v), { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    if (mi !== secuencia) { return; }   // llegó una respuesta vieja
                    if (d.existe) { marcar('correo', false, 'Este correo ya está registrado.'); }
                    else { marcar('correo', true, 'Correo disponible.'); }
                })
                .catch(function () {
                    // Si falla la consulta, el servidor igual valida al guardar
                    if (mi === secuencia) { marcar('correo', true, ''); }
                });
        }, 350);
    }

    function validarNombreUsuario() {
        var v = campos.nombre_usuario.value.trim();
        if (v === '') { return marcar('nombre_usuario', false, 'Este campo es obligatorio.'); }
        if (!/^[A-Za-z0-9._\-]{3,30}$/.test(v)) { return marcar('nombre_usuario', false, '3 a 30 caracteres: letras, números, punto, guion.'); }
        estado.nombre_usuario = false;
        actualizarBoton();
        document.getElementById('msg-nombre_usuario').textContent = 'Verificando disponibilidad…';
        clearTimeout(temporizadorUsuario);
        var mi = ++secuenciaUsuario;
        temporizadorUsuario = setTimeout(function () {
            fetch('ajax/verificar_usuario.php?tipo=usuario&usuario=' + encodeURIComponent(v.toLowerCase()), { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    if (mi !== secuenciaUsuario) { return; }
                    if (d.existe) { marcar('nombre_usuario', false, 'Este nombre de usuario ya está en uso.'); }
                    else { marcar('nombre_usuario', true, 'Nombre de usuario disponible.'); }
                })
                .catch(function () {
                    if (mi === secuenciaUsuario) { marcar('nombre_usuario', true, ''); }
                });
        }, 350);
    }

    function nivelClave(v) {
        var puntos = 0;
        if (v.length >= 8) { puntos++; }
        if (/[a-z]/.test(v) && /[A-Z]/.test(v)) { puntos++; }
        if (/\d/.test(v) && /[^A-Za-z0-9]/.test(v)) { puntos++; }
        return puntos;
    }

    function validarClave() {
        var v = campos.clave.value;
        var n = nivelClave(v);
        medidor.className = 'medidor nivel-' + n;
        medidor.firstElementChild.style.width = (v === '' ? 0 : Math.max(n, 1) * 33.4) + '%';
        if (v === '') { marcar('clave', false, 'Este campo es obligatorio.'); }
        else if (v.length < 8) { marcar('clave', false, 'Mínimo 8 caracteres.'); }
        else { marcar('clave', true, ['', 'Seguridad básica.', 'Seguridad media.', 'Seguridad alta.'][n] || ''); }
        if (campos.confirmacion.value !== '') { validarConfirmacion(); }
    }

    function validarConfirmacion() {
        var v = campos.confirmacion.value;
        if (v === '') { return marcar('confirmacion', false, 'Este campo es obligatorio.'); }
        if (v !== campos.clave.value) { return marcar('confirmacion', false, 'Las contraseñas no coinciden.'); }
        marcar('confirmacion', true, 'Las contraseñas coinciden.');
    }

    function validarSexo() {
        var elegido = form.querySelector('input[name="sexo"]:checked');
        estado.sexo = !!elegido;
        document.getElementById('msg-sexo').textContent = elegido ? '' : 'Selecciona M o F.';
        actualizarBoton();
    }

    campos.nombre.addEventListener('input', function () { validarTexto('nombre'); });
    campos.apellido.addEventListener('input', function () { validarTexto('apellido'); });
    campos.correo.addEventListener('input', validarCorreo);
    campos.nombre_usuario.addEventListener('input', validarNombreUsuario);
    campos.clave.addEventListener('input', validarClave);
    campos.confirmacion.addEventListener('input', validarConfirmacion);
    Array.prototype.forEach.call(form.querySelectorAll('input[name="sexo"]'), function (r) {
        r.addEventListener('change', validarSexo);
    });

    Array.prototype.forEach.call(document.querySelectorAll('.ver-clave'), function (b) {
        b.addEventListener('click', function () {
            var input = document.getElementById(b.getAttribute('data-objetivo'));
            var oculto = input.type === 'password';
            input.type = oculto ? 'text' : 'password';
            b.textContent = oculto ? 'Ocultar' : 'Ver';
        });
    });

    form.addEventListener('submit', function (ev) {
        if (!Object.keys(estado).every(function (k) { return estado[k]; })) {
            ev.preventDefault();
        }
    });

    // Si el servidor devolvió el formulario con datos (error), se revalidan
    ['nombre', 'apellido'].forEach(function (c) { if (campos[c].value) { validarTexto(c); } });
    if (campos.correo.value) { validarCorreo(); }
    if (campos.nombre_usuario.value) { validarNombreUsuario(); }
    if (campos.clave.value) { validarClave(); }
    if (campos.confirmacion.value) { validarConfirmacion(); }
    validarSexo();
    actualizarBoton();
})();
