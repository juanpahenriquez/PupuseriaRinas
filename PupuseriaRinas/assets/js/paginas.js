/* ============================================================================
   Nosotros + Ubicacion · capa de movimiento premium
   ----------------------------------------------------------------------------
   Complementa a landing.js (que ya resuelve reveal y parallax) con tres
   detalles que el landing no necesita pero estas paginas si:

     1. progreso  - filete naranja de avance en el techo
     2. lineas    - parte los titulares [data-split] en cajas y los hace subir
                    linea a linea, como una portada
     3. cifras    - las estadisticas [data-count] cuentan de 0 al valor

   Todo esta pensado para fallar sin romper la pagina: si algo no se encuentra,
   el bloque sale y se return. Con prefers-reduced-motion solo se pintan los
   valores finales y las lineas se quedan quietas. Sin IntersectionObserver,
   por el mismo motivo que landing.js: el rect de cada elemento da la misma
   informacion y no depende de un evento externo.
   ========================================================================== */
(function () {
    'use strict';

    var REDUCIDO = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* --------------------------------------------------------- progreso -- */
    (function progreso() {
        var barra = document.querySelector('.px-progress');
        if (!barra) return;
        var relleno = barra.querySelector('.px-progress-fill');
        if (!relleno) return;

        var alto = 0;

        function medir() { alto = document.documentElement.scrollHeight - window.innerHeight; }

        function pintar() {
            if (alto <= 0) { relleno.style.transform = 'scaleX(0)'; return; }
            var t = Math.max(0, Math.min(1, window.scrollY / alto));
            relleno.style.transform = 'scaleX(' + t.toFixed(4) + ')';
        }

        var agendado = false;
        function alScroll() {
            if (agendado) return;
            agendado = true;
            requestAnimationFrame(function () { agendado = false; pintar(); });
        }

        medir();
        pintar();
        window.addEventListener('scroll', alScroll, { passive: true });
        window.addEventListener('resize', function () { medir(); alScroll(); }, { passive: true });
        /* El alto crece al cargar las imagenes: sin esto la barra se queda
           corta y nunca llega al final. */
        window.addEventListener('load', function () { medir(); pintar(); });
    })();

    /* ------------------------------------------------------ lineas/partido */
    /* Cada linea del titular se envuelve en una caja que recorta (.px-line) y
       dentro se mueve el texto (.px-line-in). Al marcar `is-in` en el
       contenedor, cada linea sube con su propio retardo: el escalonado sale de
       ahi, no de un bucle en JS.

       El corte de linea lo mandan los <br> del marcado, no el navegador: asi
       el titular se parte igual en cualquier ancho y el retardo por linea
       siempre corresponde a la linea que el diseñador escribio. */
    function partir(el) {
        var partes = [];
        var actual = [];

        Array.prototype.slice.call(el.childNodes).forEach(function (nodo) {
            if (nodo.nodeType === 1 && nodo.tagName === 'BR') {
                if (actual.length) partes.push(actual);
                actual = [];
                partes.push(null); /* null = salto de linea */
                return;
            }
            actual.push(nodo);
        });
        if (actual.length) partes.push(actual);

        var destino = document.createDocumentFragment();
        var linea = 0;

        partes.forEach(function (parte) {
            if (parte === null) {
                /* El <br> vuelve a colocarse entre cajas: sin el, dos bloques
                   block seguidos no mantienen el ritmo vertical esperado. */
                if (linea) destino.appendChild(document.createElement('br'));
                return;
            }

            var caja = document.createElement('span');
            caja.className = 'px-line';
            var dentro = document.createElement('span');
            dentro.className = 'px-line-in';

            parte.forEach(function (nodo) {
                if (nodo.nodeType === 1) {
                    /* Los <span> de acento se conservan con su clase: se copian
                       sin hijos y se les cuelgan los originales. */
                    var copia = nodo.cloneNode(false);
                    while (nodo.firstChild) copia.appendChild(nodo.firstChild);
                    dentro.appendChild(copia);
                } else {
                    dentro.appendChild(document.createTextNode(nodo.textContent));
                }
            });

            caja.appendChild(dentro);
            caja.style.setProperty('--px-line-delay', (linea * 90) + 'ms');
            linea++;
            destino.appendChild(caja);
        });

        while (el.firstChild) el.removeChild(el.firstChild);
        el.appendChild(destino);
    }

    (function lineas() {
        var titulares = Array.prototype.slice.call(document.querySelectorAll('[data-split]'));
        if (!titulares.length) return;

        titulares.forEach(partir);

        if (REDUCIDO) {
            titulares.forEach(function (el) {
                el.classList.add('is-in');
                var caja = el.closest('[data-reveal]') || el.parentNode;
                if (caja) caja.classList.add('is-in');
            });
            return;
        }

        /* El hero entra en cuanto carga (no espera al scroll): es el primer
           fotograma de la pagina. El resto, al asomar por abajo. */
        var pendientes = titulares.filter(function (el) { return !el.closest('.px-hero'); });
        var heroes = titulares.filter(function (el) { return !!el.closest('.px-hero'); });

        heroes.forEach(function (el) { el.classList.add('is-in'); });
        if (!pendientes.length) return;

        function revisar() {
            var vh = window.innerHeight;
            var quedan = [];
            for (var i = 0; i < pendientes.length; i++) {
                var el = pendientes[i];
                var r = el.getBoundingClientRect();
                if (r.top < vh * 0.9 && r.bottom > 0) el.classList.add('is-in');
                else quedan.push(el);
            }
            pendientes = quedan;
            if (!pendientes.length) {
                window.removeEventListener('scroll', alScroll);
                window.removeEventListener('resize', alScroll);
            }
        }

        var agendado = false;
        function alScroll() {
            if (agendado) return;
            agendado = true;
            requestAnimationFrame(function () { agendado = false; revisar(); });
        }

        window.addEventListener('scroll', alScroll, { passive: true });
        window.addEventListener('resize', alScroll);
        revisar();
    })();

    /* ---------------------------------------------------------- cifras ---- */
    /* Cuenta de 0 al valor final. El numero va en data-count y el sufijo
       ("+", " ★", " años") en data-suffix, para no tener que parsear texto. */
    (function cifras() {
        var nodos = Array.prototype.slice.call(document.querySelectorAll('[data-count]'));
        if (!nodos.length) return;

        function pintar(el, valor) {
            var sufijo = el.getAttribute('data-suffix') || '';
            el.textContent = valor + sufijo;
        }

        if (REDUCIDO) {
            nodos.forEach(function (el) { pintar(el, el.getAttribute('data-count')); });
            return;
        }

        function animar(el) {
            var destino = parseFloat(el.getAttribute('data-count'));
            if (isNaN(destino)) return;
            /* Los decimales se respetan: un 4.9 no debe contar como 4. */
            var decimales = (el.getAttribute('data-count').split('.')[1] || '').length;
            var duracion = 1400;
            var t0 = null;

            function paso(ahora) {
                if (t0 === null) t0 = ahora;
                var p = Math.min(1, (ahora - t0) / duracion);
                /* Salida suave: rapido al principio, frenando al final. */
                var eased = 1 - Math.pow(1 - p, 3);
                pintar(el, (destino * eased).toFixed(decimales));
                if (p < 1) requestAnimationFrame(paso);
                else pintar(el, destino.toFixed(decimales));
            }

            requestAnimationFrame(paso);
        }

        var pendientes = nodos.slice();

        function revisar() {
            var vh = window.innerHeight;
            var quedan = [];
            pendientes.forEach(function (el) {
                var r = el.getBoundingClientRect();
                if (r.top < vh * 0.9 && r.bottom > 0) animar(el);
                else quedan.push(el);
            });
            pendientes = quedan;
            if (!pendientes.length) {
                window.removeEventListener('scroll', alScroll);
                window.removeEventListener('resize', alScroll);
            }
        }

        var agendado = false;
        function alScroll() {
            if (agendado) return;
            agendado = true;
            requestAnimationFrame(function () { agendado = false; revisar(); });
        }

        window.addEventListener('scroll', alScroll, { passive: true });
        window.addEventListener('resize', alScroll);
        revisar();

        /* Red de seguridad: si el visitante no llega a hacer scroll hasta las
           cifras, a los 3 s se muestran igual. Una cifra a cero es peor que
           una cifra sin animacion. */
        setTimeout(function () {
            pendientes.forEach(function (el) { pintar(el, el.getAttribute('data-count')); });
            pendientes = [];
        }, 3000);
    })();
})();