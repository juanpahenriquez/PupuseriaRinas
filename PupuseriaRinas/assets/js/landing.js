/* ============================================================================
   Landing Pupusería Rinas - capa de movimiento
   ----------------------------------------------------------------------------
   Cuatro bloques:
     1. reveals    - entradas al entrar en pantalla, escalonadas
     2. parallax   - las capas [data-parallax] se desplazan mas lento que la
                     pagina (un solo bucle rAF, lecturas antes que escrituras)
     3. galeria    - banner a pantalla total: progreso, miniaturas, Ken Burns
     4. destacado  - selector de unidades y favorito

   Sin IntersectionObserver en ninguna parte: si el navegador no entrega esas
   callbacks el contenido se queda en blanco y el parallax congelado. Los dos
   primeros efectos se resuelven con el rect de cada elemento, que es la misma
   informacion y no depende de un evento externo. Todo se desactiva con
   prefers-reduced-motion: reduce.
   ========================================================================== */
(function () {
    'use strict';

    var REDUCIDO = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ------------------------------------------------------------ reveals - */
    (function reveals() {
        var pendientes = Array.prototype.slice.call(document.querySelectorAll('[data-reveal]'));
        if (!pendientes.length) return;

        /* El escalonado llega en el atributo y se pasa a una variable CSS, que
           es lo que lee el transition-delay de .js [data-reveal]. */
        pendientes.forEach(function (el) {
            var d = el.getAttribute('data-reveal-delay');
            if (d) el.style.setProperty('--nl-reveal', d + 'ms');
        });

        if (REDUCIDO) {
            pendientes.forEach(function (el) { el.classList.add('is-in'); });
            return;
        }

        function revisar() {
            var vh = window.innerHeight;
            var quedan = [];
            for (var i = 0; i < pendientes.length; i++) {
                var el = pendientes[i];
                var r = el.getBoundingClientRect();
                /* Se revela al entrar por el borde inferior con un 12% de
                   margen: entrar un poco antes es lo que se ve bien. */
                if (r.top < vh * 0.88 && r.bottom > 0) {
                    el.classList.add('is-in');
                } else {
                    quedan.push(el);
                }
            }
            pendientes = quedan;
            /* No queda nada por revelar: se sueltan los listeners. */
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
        window.addEventListener('resize', alScroll, { passive: true });
        revisar();
    })();

    /* ----------------------------------------------------------- parallax - */
    (function parallax() {
        var capas = Array.prototype.slice.call(document.querySelectorAll('[data-parallax]'));
        if (!capas.length || REDUCIDO) return;

        /* Cada capa guarda su velocidad y el ultimo valor escrito, para no
           tocar el DOM cuando el numero no ha cambiado. */
        var estado = capas.map(function (el) {
            return { el: el, vel: parseFloat(el.getAttribute('data-parallax')) || 0, y: null };
        });

        var viewportH = window.innerHeight;

        function pintar() {
            for (var i = 0; i < estado.length; i++) {
                var c = estado[i];
                var r = c.el.getBoundingClientRect();

                /* Fuera de escena se limpia el transform: asi no queda una
                   capa promoted al compositor que nadie esta viendo. */
                if (r.bottom < -200 || r.top > viewportH + 200) {
                    if (c.y !== null) { c.y = null; c.el.style.transform = ''; }
                    continue;
                }

                /* Progreso -1..1: -1 cuando la capa entra por abajo, 0 cuando su
                   centro cruza el centro de la pantalla, 1 cuando sale por
                   arriba. */
                var recorrido = r.height + viewportH;
                var p = (viewportH / 2 - (r.top + r.height / 2)) / (recorrido / 2);
                p = Math.max(-1, Math.min(1, p));

                /* El desplazamiento va en % de la ALTURA PROPIA de la capa. Las
                   capas llegan sobredimensionadas al 128% desde el CSS, asi que
                   una velocidad de 10 recorre como maximo 12.8% de la altura
                   del contenedor, y el aire del que dispone la capa es del 14%:
                   nunca destapa un borde en ningun punto del recorrido. */
                var y = Math.round(p * c.vel * 100) / 100;
                if (y === c.y) continue;
                c.y = y;
                c.el.style.transform = 'translate3d(0,' + y + '%,0)';
            }
        }

        var pendiente = false;
        function pedir() {
            if (pendiente) return;
            pendiente = true;
            requestAnimationFrame(function () { pendiente = false; pintar(); });
        }

        window.addEventListener('scroll', pedir, { passive: true });
        window.addEventListener('resize', function () {
            viewportH = window.innerHeight;
            pedir();
        }, { passive: true });
        window.addEventListener('load', pedir);
        pintar();
    })();

    /* ------------------------------------------------------------ galeria - */
    (function galeria() {
        var main = document.getElementById('nlGaleriaMain');
        if (!main || !window.Splide) return;

        /* Ritmo del banner. Con 5,2 s se llegaba a leer como lento, así que
           3,6 s: lo justo para mirar la foto y seguir sin esperar. El fundido
           dura 500 ms, de modo que la imagen nueva entra enseguida. */
        var DURACION = 3600;
        var FUNDIDO = 500;

        var slides = Array.prototype.slice.call(main.querySelectorAll('.splide__slide'));
        var banner = main.closest('.nl-gallery-banner');
        var barras = banner ? banner.querySelectorAll('.nl-gallery-bars button') : [];
        var actual = banner ? banner.querySelector('#nlGaleriaActual') : null;
        var total = banner ? banner.querySelector('#nlGaleriaTotal') : null;

        /* `fade` y no `slide`: con las fotos apiladas al 100% el corte lateral se
           leeria como un panoramico, y el Ken Burns solo tiene sentido si la foto
           activa esta encima. Splide fija el alto del track por JS en fade, por
           eso el CSS lo fuerza a 100%. */
        var galeria = new Splide('#nlGaleriaMain', {
            type: 'fade',
            /* loop es imprescindible: sin el, el autoplay recorre las seis
               fotos y se detiene en la ultima. Con el, el banner nunca se
               queda clavado en una sola imagen. */
            loop: true,
            /* Con movimiento reducido no hay avance automatico: seis fotos
               moviéndose solas son justo lo que molesta a quien tiene esa
               preferencia activada. Se navega con las miniaturas o las barras. */
            autoplay: REDUCIDO ? false : DURACION,
            /* Sin pauseOnHover ni pauseOnFocus. Los dos congelaban el avance
               con el cursor simplemente cerca del banner, y al pulsar una
               barra de progreso esa barra quedaba enfocada y paraba todo: en
               los dos casos el gallery parecia no cambiar. */
            pauseOnHover: false,
            pauseOnFocus: false,
            speed: FUNDIDO,
            arrows: false,
            pagination: false,
            lazyLoad: false
        });

        /* Miniaturas. La banda reparte el ancho completo del bloque crema a
           sangre, asi que el ancho de cada minatura depende de lo que mida ese
           bloque: con un fixedWidth fijo en px se quedaba en 626px dentro de
           uno de 1400 y sobraba crema por los dos lados.

           Splide 4.1.3 NO tiene setOption (su API real es go/mount/sync/
           refresh/destroy), asi que el tamano se fija al construir la instancia.
           Al cambiar el ancho de la ventana se reconstruye, que es lo unico
           que recalcula el tamano de los slides. */
        var wrap = document.querySelector('.nl-gallery-thumbs-wrap');
        var mini = null;
        var anchoPrevio = 0;

        function medidas() {
            var total = wrap ? wrap.clientWidth : 0;
            var n = wrap ? wrap.querySelectorAll('.splide__slide').length : 0;
            if (!n) return { ancho: 96, alto: 64, hueco: 10 };
            var hueco = (total && total < 576) ? 6 : 10;
            var ancho = total ? Math.floor((total - hueco * (n - 1)) / n) : 96;
            if (ancho < 1) ancho = 1;
            /* Proporcion apaisada con topes: ni una tira de 12px en un movil
               ni un bloque desproporcionado en una pantalla grande. */
            var alto = Math.round(Math.min(170, Math.max(58, ancho * 0.58)));
            return { ancho: ancho, alto: alto, hueco: hueco };
        }

        function montarMini() {
            if (!document.getElementById('nlGaleriaThumbs')) return;
            var m = medidas();
            /* Si el ancho no cambia no se toca nada: reconstruir en cada resize
               haria saltar el carrusel sin motivo. */
            if (mini && anchoPrevio === m.ancho) return;
            anchoPrevio = m.ancho;

            if (mini) {
                try { galeria.remove(mini); } catch (e) { /* sin sync previo */ }
                mini.destroy();
            }

            mini = new Splide('#nlGaleriaThumbs', {
                rewind: true,
                fixedWidth: m.ancho,
                fixedHeight: m.alto,
                gap: m.hueco,
                isNavigation: true,
                /* 'left' y no 'center': con 'center' Splide desplaza la pista
                   para centrar el pulgar activo y ese desplazamiento se salia
                   de la pagina. */
                focus: 'left',
                pagination: false,
                /* Sin flechas: las de Splide por defecto son circulos grandes
                   que no encajan con el estilo. Se navega con las miniaturas, las
                   barras de progreso o el avance automatico. */
                arrows: false,
                cover: true
            });
            mini.mount();
            galeria.sync(mini);
        }

        function indice(i) {
            return ('0' + (i + 1)).slice(-2);
        }

        function progreso(i) {
            Array.prototype.forEach.call(barras, function (b, k) {
                var relleno = b.querySelector('span');
                if (!relleno) return;
                b.classList.toggle('is-active', k === i);
                b.setAttribute('aria-current', k === i ? 'true' : 'false');
                if (k !== i) {
                    relleno.style.transition = 'none';
                    relleno.style.width = '0%';
                    return;
                }
                /* Ancho a 0 -> reflow forzado -> animacion hasta 100%. Asi la
                   linea de progreso avanza al mismo ritmo que el autoplay. */
                relleno.style.transition = 'none';
                relleno.style.width = '0%';
                void relleno.offsetWidth;
                relleno.style.transition = 'width ' + (DURACION / 1000) + 's linear';
                relleno.style.width = '100%';
            });
            if (actual) actual.textContent = indice(i);
        }

        /* Splide emite `ready` de forma sincrona dentro del constructor, asi que
           un on('ready') registrado despues se pierde y mount() nunca llega a
           ejecutarse. Por eso el montaje es explicito y en orden: el banner
           primero, las miniaturas despues. */
        if (total) total.textContent = indice(slides.length - 1);
        galeria.mount();
        montarMini();
        progreso(0);

        /* Al cambiar el ancho de la ventana hay que volver a repartir: el ancho
           de cada miniatura depende del bloque crema, no de un valor fijo. */
        var pendienteThumbs = false;
        window.addEventListener('resize', function () {
            if (pendienteThumbs) return;
            pendienteThumbs = true;
            requestAnimationFrame(function () { pendienteThumbs = false; montarMini(); });
        }, { passive: true });
        window.addEventListener('load', montarMini);

        galeria.on('move', function (nuevo) { progreso(nuevo); });

        function reloj() {
            var auto = galeria.Components.Autoplay;
            if (!auto) return;
            auto.pause();
            auto.play();
        }

        Array.prototype.forEach.call(barras, function (b, k) {
            b.addEventListener('click', function () {
                galeria.go(k);
                reloj();
            });
        });
    })();

    /* ------------------------------------------- destacado: precio / fav - */
    (function destacado() {
        var wrap = document.getElementById('nlTamanos');
        var precio = document.getElementById('nlPrecio');
        if (wrap && precio) {
            wrap.addEventListener('click', function (e) {
                var btn = e.target.closest('button');
                if (!btn) return;
                Array.prototype.forEach.call(wrap.querySelectorAll('button'), function (b) {
                    b.classList.remove('is-active');
                });
                btn.classList.add('is-active');
                precio.textContent = '$' + btn.getAttribute('data-price');
            });
        }

        var fav = document.querySelector('.nl-icon-btn[data-fav]');
        if (fav) {
            fav.addEventListener('click', function () {
                var activo = fav.classList.toggle('is-fav');
                fav.setAttribute('aria-pressed', activo ? 'true' : 'false');
                var svg = fav.querySelector('svg');
                if (svg) svg.style.fill = activo ? 'currentColor' : 'none';
            });
        }
    })();
})();
