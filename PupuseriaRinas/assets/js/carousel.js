/* Carrusel Especialidades - vanilla, sin dependencias.
   Loop infinito por reciclaje DOM con margen + secuencia viajar-luego-expandir:
   1) la activa se colapsa pegada al centro, 2) la pista viaja rigida y lento,
   3) al llegar (transitionend real, no ms asumidos) la card se expande pegada
   al centro y muestra la info, 4) audit() garantiza el reposo centrado al pixel.
   La compensacion +-delta/2 conserva el ultimo valor bueno entre secuencias. */
(function () {
  var track = document.getElementById("expoTrack");
  if (!track) return;
  var viewport = track.parentElement;

  var order = Array.prototype.slice.call(track.children);
  var n = order.length;
  if (!n) return;

  var idx = n > 2 ? 2 : 0; // arranque en la card central
  var timers = [];
  var seqDelta = 0; // diferencia ancho activa - normal; se conserva el ultimo valor bueno
  var seqId = 0; // invalida fases de secuencias superadas (clics mid-flight)
  var pendingH = null; // listener transitionend pendiente

  function clearTimers() {
    timers.forEach(function (t) { clearTimeout(t); });
    timers = [];
  }
  function later(ms, fn) { timers.push(setTimeout(fn, ms)); }

  function slotPitch(el) {
    var cs = window.getComputedStyle(el);
    return el.offsetWidth + parseFloat(cs.marginLeft) + parseFloat(cs.marginRight);
  }

  // Posicion visual real del track (valida incluso a media animacion CSS)
  function visualX() {
    var t = window.getComputedStyle(track).transform;
    if (!t || t === "none") return 0;
    if (typeof DOMMatrix !== "undefined") {
      try { return new DOMMatrix(t).m41; } catch (e) { /* fallback abajo */ }
    }
    var m = t.match(/matrix\(([^)]+)\)/);
    if (m) {
      var p = m[1].split(",");
      return parseFloat(p[4]) || 0;
    }
    return 0;
  }

  // Centro de order[pos] medido en vivo (llamar con anchos asentados)
  function centerFor(pos) {
    var left = 0;
    for (var k = 0; k < pos; k++) left += slotPitch(order[k]);
    return viewport.clientWidth / 2 - (left + slotPitch(order[pos]) / 2);
  }

  // durMs omitido = salto instantaneo (transition none + reflow)
  function setTransform(x, durMs) {
    if (durMs) {
      track.style.transition = "transform " + durMs + "ms ease-in-out";
    } else {
      track.style.transition = "none";
      void track.offsetWidth;
    }
    track.style.transform = "translateX(" + x + "px)";
  }

  function setActive(pos) {
    order.forEach(function (el, k) {
      el.classList.toggle("active", k === pos);
    });
  }

  // Rotaciones invisibles desde la posicion visual real (exactas siempre)
  function rotateFirstToEnd() {
    var first = order.shift();
    order.push(first);
    track.appendChild(first);
    setTransform(visualX() + slotPitch(first));
  }

  function rotateLastToFront() {
    var last = order.pop();
    order.unshift(last);
    track.insertBefore(last, track.firstChild);
    setTransform(visualX() - slotPitch(last));
  }

  // Garantia de simetria: ningun reposo puede quedar descentrado.
  // Si el centro real difiere del exacto, lo corrige y lo reporta.
  function audit() {
    var target = centerFor(idx);
    var err = target - visualX();
    if (Math.abs(err) > 1.5) {
      if (window.console) console.log("[carousel] audit corrige err=" + (Math.round(err * 10) / 10) + "px en idx=" + idx);
      setTransform(target, 350);
    }
  }

  function goto(pos) {
    clearTimers();
    if (pendingH) { track.removeEventListener("transitionend", pendingH); pendingH = null; }
    var my = ++seqId;
    var prevEl = order[idx]; // activa actual (ancha); la referencia sigue valida tras rotar
    // Doble margen: el reposo queda en [2, n-3] para tener siempre
    // 2+ cards visibles por lado (composicion simetrica en toda pantalla)
    if (n > 4) {
      while (pos > n - 3) { rotateFirstToEnd(); pos--; }
      while (pos < 2) { rotateLastToFront(); pos++; }
    } else if (n > 2) {
      if (pos === n - 1) { rotateFirstToEnd(); pos = n - 2; }
      else if (pos === 0) { rotateLastToFront(); pos = 1; }
    }
    idx = pos;
    // Delta en vivo, pero sin resetear a 0: una medicion a medio vuelo
    // (clic durante animacion) nunca desactiva la compensacion
    var refEl = order[0] === prevEl ? order[1] : order[0];
    var d = refEl ? slotPitch(prevEl) - slotPitch(refEl) : 0;
    if (d > 0) seqDelta = d;
    setActive(-1); // 1. colapsa la actual...
    if (seqDelta) setTransform(visualX() + seqDelta / 2, 400); // ...pegada al centro (0.4s = CSS)
    later(450, function () {
      if (my !== seqId) return;
      var advanced = false;
      function arrived() {
        if (advanced || my !== seqId) return;
        advanced = true;
        if (pendingH) { track.removeEventListener("transitionend", pendingH); pendingH = null; }
        setActive(idx); // 3. expande + muestra info...
        if (seqDelta) setTransform(visualX() - seqDelta / 2, 400); // ...pegada al centro: final exacto
        later(500, function () { if (my === seqId) audit(); }); // 4. verifica el reposo
      }
      // Avanza al terminar el viaje real (no por ms asumidos); watchdog por si el evento no llega
      pendingH = function (e) {
        if (e.target !== track || e.propertyName !== "transform") return;
        arrived();
      };
      track.addEventListener("transitionend", pendingH);
      setTransform(centerFor(idx), 1100); // 2. viaja rigida y lento (1.1s)
      later(1400, arrived); // watchdog
    });
  }

  function next() { goto(idx + 1 < n ? idx + 1 : idx); }
  function prev() { goto(idx - 1 >= 0 ? idx - 1 : idx); }

  track.addEventListener("click", function (e) {
    var item = e.target.closest(".item");
    if (!item) return;
    var k = order.indexOf(item);
    if (k !== -1) goto(k);
    restart();
  });

  // Swipe tactil
  var startX = null;
  track.addEventListener("touchstart", function (e) {
    startX = e.touches[0].clientX;
    stop();
  }, { passive: true });
  track.addEventListener("touchend", function (e) {
    if (startX === null) return;
    var dx = e.changedTouches[0].clientX - startX;
    if (dx < -40) next();
    else if (dx > 40) prev();
    startX = null;
    restart();
  });

  // Autoplay cada 3.2s (secuencia completa ~= 2.7s: sin solapes), pausa en hover
  var timer = null;
  function stop() { if (timer) { clearInterval(timer); timer = null; } }
  function restart() {
    stop();
    timer = setInterval(function () { if (!document.hidden) next(); }, 3200);
  }
  viewport.addEventListener("mouseenter", stop);
  viewport.addEventListener("mouseleave", restart);

  window.addEventListener("resize", function () {
    clearTimers();
    setActive(idx);
    setTransform(centerFor(idx));
    later(450, function () { setTransform(centerFor(idx)); }); // re-centra si el breakpoint dejo anchos a medio vuelo
  });

  setActive(idx);
  setTransform(centerFor(idx));
  restart();
})();
