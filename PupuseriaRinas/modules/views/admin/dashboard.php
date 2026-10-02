<?php
require_once __DIR__ . '/../../../php/enlaces.php';
// Datos reales de MySQL — calculados en admin/index.php y pasados en $mock
$mock = $mock ?? [];
$mock += [
    'ventas_hoy'         => 0,
    'ventas_ayer'        => 0,
    'pedidos_pendientes' => 0,
    'pedidos_llevar'     => 0,
    'pedidos_recoger'    => 0,
    'canal_llevar'       => 0,
    'canal_recoger'      => 0,
    'ticket'             => 0,
    'productos_activos'  => 0,
    'ventas_semana'      => [],
    'pedidos_semana'     => [],
    'ventas_mes'         => [],
    'pedidos_mes'        => [],
    'dias'               => ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
    'semanas'            => ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4'],
    'ticket_meta'        => 0,
    'ticket_meta_pct'    => 0,
    'top_productos'      => [],
    'horas'              => [],
    'ventas_hora'        => [],
    'pedidos_hora'       => [],
    'estados_hoy'        => [],
    'total_pedidos'      => 0,
];
$abierta  = $abierta  ?? false;   // lo usa la píldora de la barra de estado

$ventasSemanaTotal = array_sum($mock['ventas_semana']);
$trendVentas = $mock['ventas_hoy'] - $mock['ventas_ayer'];
$trendPct = $mock['ventas_ayer'] > 0 ? ($trendVentas / $mock['ventas_ayer'] * 100) : 0;

$totalUds = 0;
foreach ($mock['top_productos'] as $p) {
    $totalUds += (int) $p['uds'];
}

// Un color por estado, reutilizado en la leyenda y en el donut
$estadoColor = ['pendiente' => '#D97706', 'cocina' => '#F59E0B', 'listo' => '#0a7a42', 'entregado' => '#0B0B45'];

// Pestañas: clave => [etiqueta, icono]
$pestanas = [
    'ventas' => ['Ventas', 'fa-chart-area'],
    'hora'   => ['Por hora', 'fa-clock'],
    'canal'  => ['Canal', 'fa-store'],
    'top'    => ['Top productos', 'fa-ranking-star'],
    'estados' => ['Estados', 'fa-list-check'],
];
?>

<div class="admin-dash">

    <!-- ===== Tira de estado ===== -->
    <div class="dash-toolbar">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="dash-status">
                <span class="dash-status-dot"></span>
                MySQL conectado · <?php echo (int) $mock['total_pedidos']; ?> pedido<?php echo $mock['total_pedidos'] == 1 ? '' : 's'; ?> en total
            </span>
            <span class="small text-muted d-none d-md-inline">Actualizado <?php echo date('H:i'); ?></span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="small fw-bold badge-rinas badge-<?php echo $abierta ? 'listo' : 'pendiente'; ?>">
                <?php echo $abierta ? 'Abierto ahora' : 'Cerrado ahora'; ?>
            </span>
            <a href="pedidos.php" class="btn btn-sm btn-admin btn-admin--black">Ver pedidos →</a>
        </div>
    </div>

    <!-- ===== KPIs compactos ===== -->
    <div class="dash-kpis">
        <div class="dash-kpi dash-kpi--accent">
            <div class="dash-kpi-main">
                <div class="admin-kpi-label">Ventas hoy</div>
                <div class="admin-kpi-value">$<?php echo number_format($mock['ventas_hoy'], 2); ?></div>
            </div>
            <span class="admin-kpi-icon naranja"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 8c-2 0-4 1-4 3s2 3 4 3 4-1 4-3-2-3-4-3z"/><path d="M12 14v4"/><path d="M8 10V9a4 4 0 0 1 8 0v1"/><path d="M8 14c0 2 1.8 4 4 4s4-2 4-4"/></svg></span>
            <div class="dash-kpi-foot">
                <span class="admin-kpi-trend <?php echo $trendVentas >= 0 ? 'up' : 'down'; ?>"><?php echo ($trendVentas >= 0 ? '↑ ' : '↓ ') . number_format(abs($trendPct), 1) . '% vs ayer'; ?></span>
                <span class="small text-muted ms-auto">Semana $<?php echo number_format($ventasSemanaTotal, 2); ?></span>
                <div id="kpiSparkVentas" class="dash-kpi-spark" aria-hidden="true"></div>
            </div>
        </div>

        <div class="dash-kpi">
            <div class="dash-kpi-main">
                <div class="admin-kpi-label">Pedidos pendientes</div>
                <div class="admin-kpi-value"><?php echo (int) $mock['pedidos_pendientes']; ?></div>
            </div>
            <span class="admin-kpi-icon azul"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6h6"/><path d="M9 10h6"/><path d="M9 14h6"/><rect x="4" y="3" width="16" height="18" rx="2"/></svg></span>
            <div class="dash-kpi-foot">
                <span class="admin-kpi-trend"><?php echo (int) $mock['pedidos_llevar']; ?> llevar · <?php echo (int) $mock['pedidos_recoger']; ?> recoger</span>
                <div id="kpiMiniDonut" style="height:34px;flex:0 0 60px" aria-hidden="true"></div>
            </div>
        </div>

        <div class="dash-kpi">
            <div class="dash-kpi-main">
                <div class="admin-kpi-label">Ticket promedio</div>
                <div class="admin-kpi-value">$<?php echo number_format($mock['ticket'], 2); ?></div>
            </div>
            <span class="admin-kpi-icon crema"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
            <div class="dash-kpi-foot">
                <span class="admin-kpi-trend">Meta $<?php echo number_format($mock['ticket_meta'], 2); ?></span>
                <div id="kpiTicketRadial" style="height:34px;flex:0 0 60px" aria-hidden="true"></div>
            </div>
        </div>

        <div class="dash-kpi">
            <div class="dash-kpi-main">
                <div class="admin-kpi-label">Productos activos</div>
                <div class="admin-kpi-value"><?php echo (int) $mock['productos_activos']; ?></div>
            </div>
            <span class="admin-kpi-icon naranja"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 8h15l-1.5 12.5a1 1 0 0 1-1 .5H5.5a1 1 0 0 1-1-.5L3 8h3z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></svg></span>
            <div class="dash-kpi-foot">
                <span class="admin-kpi-trend"><?php echo (int) $mock['productos_activos']; ?> en el menú hoy</span>
            </div>
        </div>
    </div>

    <!-- ===== Contenedor grande: una gráfica por pestaña ===== -->
    <section class="admin-panel admin-panel--elevated dash-tabs-panel" aria-label="Gráficas del panel">
        <div class="dash-tabs" role="tablist" id="dashTabs">
            <?php foreach ($pestanas as $clave => $p): ?>
            <button class="dash-tab" type="button" role="tab" id="tab-<?php echo $clave; ?>"
                    aria-controls="panel-<?php echo $clave; ?>" aria-selected="<?php echo $clave === 'ventas' ? 'true' : 'false'; ?>"
                    data-tab="<?php echo $clave; ?>">
                <i class="fa-solid <?php echo $p[1]; ?>" aria-hidden="true"></i><?php echo htmlspecialchars($p[0]); ?>
            </button>
            <?php endforeach; ?>
        </div>

        <div class="dash-tabpanels">
            <!-- 1. Ventas (semana / mes) -->
            <div class="dash-tabpanel is-active" role="tabpanel" id="panel-ventas" aria-labelledby="tab-ventas" tabindex="0">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                    <div class="d-flex align-items-baseline gap-2 flex-wrap">
                        <span class="admin-panel-title">Ventas · Pedidos</span>
                        <span class="small text-muted" id="dashVentasSub">Últimos 7 días — datos reales</span>
                    </div>
                    <div class="d-flex gap-2" id="ventasToggle">
                        <button class="admin-filter active" data-range="semana" type="button">Semana</button>
                        <button class="admin-filter" data-range="mes" type="button">Mes</button>
                    </div>
                </div>
                <div id="dashChartVentas" class="dash-chart"></div>
            </div>

            <!-- 2. Pedidos por hora -->
            <div class="dash-tabpanel" role="tabpanel" id="panel-hora" aria-labelledby="tab-hora" tabindex="0">
                <div class="d-flex align-items-center-baseline gap-2 mb-2">
                    <span class="admin-panel-title">Pedidos por hora</span>
                    <span class="small text-muted">Hoy · distribución de las 24 horas del día</span>
                </div>
                <div id="dashChartHora" class="dash-chart"></div>
            </div>

            <!-- 3. Canal (llevar / recoger) -->
            <div class="dash-tabpanel" role="tabpanel" id="panel-canal" aria-labelledby="tab-canal" tabindex="0">
                <div class="dash-split">
                    <div id="dashChartCanal" class="dash-chart"></div>
                    <div class="dash-side">
                        <p class="dash-side-title">Reparto de hoy</p>
                        <div class="dash-legend">
                            <span class="dash-legend-dot" style="background:#0B0B45"></span>
                            Para llevar
                            <span class="dash-legend-value"><?php echo (int) $mock['canal_llevar']; ?></span>
                        </div>
                        <div class="dash-legend">
                            <span class="dash-legend-dot" style="background:#F59E0B"></span>
                            Recoger en tienda
                            <span class="dash-legend-value"><?php echo (int) $mock['canal_recoger']; ?></span>
                        </div>
                        <p class="small text-muted mt-2 mb-0" style="font-size:0.78rem">
                            Solo pedidos creados hoy. El histórico completo está en
                            <a href="pedidos.php?tipo=llevar" class="fw-bold">Pedidos</a>.
                        </p>
                    </div>
                </div>
            </div>

            <!-- 4. Top productos -->
            <div class="dash-tabpanel" role="tabpanel" id="panel-top" aria-labelledby="tab-top" tabindex="0">
                <div class="dash-split">
                    <div id="dashChartTop" class="dash-chart"></div>
                    <div class="dash-side">
                        <p class="dash-side-title">Unidades vendidas</p>
                        <?php if (empty($mock['top_productos'])): ?>
                            <p class="small text-muted mb-0">Sin ventas registradas todavía.</p>
                        <?php else: foreach ($mock['top_productos'] as $p): ?>
                            <div class="dash-rank">
                                <span class="dash-rank-name" title="<?php echo htmlspecialchars($p['nombre']); ?>"><?php echo htmlspecialchars($p['nombre']); ?></span>
                                <div class="progress flex-grow-1" style="height:8px;min-width:40px">
                                    <div class="progress-bar <?php echo $p['pct'] >= 74 ? 'progress-bar--naranja' : 'progress-bar--azul'; ?>" style="width:<?php echo (int) $p['pct']; ?>%"></div>
                                </div>
                                <span class="dash-rank-value"><?php echo (int) $p['uds']; ?></span>
                            </div>
                        <?php endforeach; endif; ?>
                        <p class="small text-muted mt-2 mb-0" style="font-size:0.78rem">
                            Total del top: <strong><?php echo (int) $totalUds; ?></strong> unidades.
                        </p>
                    </div>
                </div>
            </div>

            <!-- 5. Embudo de estados: cuenta todos los pedidos, no solo los de hoy,
                 para que cuadre con el KPI de "Pedidos pendientes" -->
            <div class="dash-tabpanel" role="tabpanel" id="panel-estados" aria-labelledby="tab-estados" tabindex="0">
                <div class="dash-split dash-split--wide">
                    <div id="dashChartEstados" class="dash-chart"></div>
                    <div class="dash-side">
                        <p class="dash-side-title">Todos los pedidos</p>
                        <?php foreach ($mock['estados_hoy'] as $e): ?>
                        <div class="dash-legend">
                            <span class="dash-legend-dot" style="background:<?php echo $estadoColor[$e['estado']] ?? '#6c757d'; ?>"></span>
                            <?php echo htmlspecialchars($e['label']); ?>
                            <span class="dash-legend-value"><?php echo (int) $e['n']; ?></span>
                        </div>
                        <?php endforeach; ?>
                        <a href="pedidos.php" class="btn btn-sm btn-admin btn-admin--black mt-1 align-self-start">Gestionar pedidos →</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="<?= LINK_APEXCHARTS_JS ?>"></script>
<script>
(function () {
  if (typeof ApexCharts === 'undefined') return;

  const D = <?php echo json_encode([
      'dias'            => $mock['dias'],
      'semanas'         => $mock['semanas'],
      'horas'           => $mock['horas'],
      'ventasSemana'    => $mock['ventas_semana'],
      'pedidosSemana'   => $mock['pedidos_semana'],
      'ventasMes'       => $mock['ventas_mes'],
      'pedidosMes'      => $mock['pedidos_mes'],
      'ventasHora'      => $mock['ventas_hora'],
      'pedidosHora'     => $mock['pedidos_hora'],
      'topProductos'    => $mock['top_productos'],
      'estados'         => $mock['estados_hoy'],
      'llevar'          => (int) $mock['pedidos_llevar'],
      'recoger'         => (int) $mock['pedidos_recoger'],
      'canalLlevar'     => (int) $mock['canal_llevar'],
      'canalRecoger'    => (int) $mock['canal_recoger'],
      'ticketMetaPct'   => (int) $mock['ticket_meta_pct'],
  ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP); ?>;

  const AMARILLO = '#F59E0B';
  const AZUL     = '#0B0B45';
  const money    = v => '$' + Number(v).toFixed(2);
  const colores  = [AMARILLO, AZUL, '#D97706', '#1B1B6B', '#E8E8F5'];

  /* Monta una gráfica tomando la altura REAL del contenedor en píxeles.
     No se usa height:'100%' porque ApexCharts resuelve ese porcentaje antes
     de que el panel tenga layout y acaba dibujando un SVG de altura 0 que ya
     no se corrige (el donut salía invisible). Medir aquí lo evita.
     La altura queda luego vigilada con un ResizeObserver (ver `observar`). */
  function montar(el, opciones) {
    const alto = Math.max(200, Math.round(el.getBoundingClientRect().height));
    opciones.chart = Object.assign({ height: alto }, opciones.chart || {});
    const ch = new ApexCharts(el, opciones);
    ch.render();
    return ch;
  }

  /* ============================================================
     KPIs (sparklines): siempre visibles, se pintan de inmediato
     ============================================================ */
  if (document.getElementById('kpiSparkVentas')) {
    new ApexCharts(document.getElementById('kpiSparkVentas'), {
      chart: { type: 'area', height: 34, sparkline: { enabled: true }, animations: { enabled: false } },
      series: [{ name: 'Ventas', data: D.ventasSemana }],
      colors: [AMARILLO],
      stroke: { curve: 'smooth', width: 2 },
      fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.02 } },
      markers: { size: 0 },
      tooltip: { theme: 'dark', y: { formatter: money } },
      dataLabels: { enabled: false }
    }).render();
  }

  if (document.getElementById('kpiMiniDonut')) {
    new ApexCharts(document.getElementById('kpiMiniDonut'), {
      chart: { type: 'donut', height: 34, sparkline: { enabled: true }, animations: { enabled: false } },
      series: [D.llevar, D.recoger],
      labels: ['Llevar', 'Recoger'],
      colors: [AZUL, AMARILLO],
      stroke: { width: 0 },
      legend: { show: false },
      tooltip: { theme: 'dark', y: { formatter: v => v + ' pedidos' } },
      dataLabels: { enabled: false }
    }).render();
  }

  if (document.getElementById('kpiTicketRadial')) {
    new ApexCharts(document.getElementById('kpiTicketRadial'), {
      chart: { type: 'radialBar', height: 34, sparkline: { enabled: true }, animations: { enabled: false } },
      series: [D.ticketMetaPct],
      plotOptions: { radialBar: { hollow: { size: '62%' }, track: { background: '#FFF6D3' }, dataLabels: { name: { show: false }, value: { show: true, fontSize: '10px', fontWeight: 700, color: AZUL, offsetY: 2, formatter: v => v + '%' } } } },
      tooltip: { theme: 'dark' }
    }).render();
  }

  /* ============================================================
     Fábricas de las gráficas grandes. Se registran sin dibujar:
     cada una monta su ApexCharts la primera vez que se muestra su
     pestaña (dibujar en un panel oculto daría ancho/alto 0).
     ============================================================ */
  const fabricas = {
    ventas: function (el) {
      const ch = montar(el, {
        chart: { type: 'area', fontFamily: 'inherit', background: 'transparent',
                 toolbar: { show: true, tools: { download: true, selection: false, zoom: false, zoomin: false, zoomout: false, pan: false, reset: false } },
                 animations: { enabled: true, easing: 'easeinout', speed: 700 },
                 dropShadow: { enabled: true, top: 6, left: 0, blur: 8, opacity: 0.12 } },
        series: [
          { name: 'Ventas $', type: 'area', data: D.ventasSemana },
          { name: 'Pedidos', type: 'column', data: D.pedidosSemana }
        ],
        stroke: { width: [3, 0], curve: 'smooth', lineCap: 'round' },
        fill: { type: ['gradient', 'solid'], gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.02, colorStops: [{ offset: 0, color: AMARILLO, opacity: 0.45 }, { offset: 100, color: '#FFF6D3', opacity: 0.02 }] } },
        colors: [AMARILLO, AZUL],
        markers: { size: 5, strokeWidth: 2, strokeColors: '#fff', colors: [AMARILLO], hover: { size: 7 } },
        xaxis: { categories: D.dias, labels: { style: { colors: '#111', fontWeight: 700 } }, axisBorder: { show: false }, axisTicks: { show: false } },
        yaxis: [
          { labels: { formatter: v => '$' + v, style: { colors: '#6c757d' } } },
          { opposite: true, labels: { formatter: v => v + ' ped', style: { colors: '#6c757d' } } }
        ],
        grid: { borderColor: 'rgba(11,11,69,0.07)', strokeDashArray: 4, padding: { top: 8 } },
        plotOptions: { bar: { borderRadius: 6, columnWidth: '34%', borderRadiusApplication: 'end' } },
        tooltip: { shared: true, intersect: false, theme: 'dark', y: { formatter: (v, ctx) => ctx.seriesIndex === 0 ? money(v) : v + ' pedidos' } },
        legend: { show: false },
        noData: { text: 'Sin ventas para mostrar' }
      });

      // Toggle Semana / Mes dentro de la pestaña
      const toggle = document.getElementById('ventasToggle');
      const sub = document.getElementById('dashVentasSub');
      if (toggle) {
        toggle.addEventListener('click', e => {
          const btn = e.target.closest('[data-range]');
          if (!btn) return;
          toggle.querySelectorAll('.admin-filter').forEach(b => b.classList.remove('active'));
          btn.classList.add('active');
          const mes = btn.getAttribute('data-range') === 'mes';
          ch.updateOptions({ xaxis: { categories: mes ? D.semanas : D.dias } });
          ch.updateSeries([{ data: mes ? D.ventasMes : D.ventasSemana }, { data: mes ? D.pedidosMes : D.pedidosSemana }]);
          if (sub) sub.textContent = mes ? 'Mes actual agrupado por semanas' : 'Últimos 7 días — datos reales';
        });
      }
      return ch;
    },

    hora: function (el) {
      const ch = montar(el, {
        chart: { type: 'bar', stacked: false, fontFamily: 'inherit', background: 'transparent',
                 toolbar: { show: true, tools: { download: true, selection: false, zoom: false, zoomin: false, zoomout: false, pan: false, reset: false } },
                 animations: { enabled: true, speed: 600 },
                 dropShadow: { enabled: true, top: 4, left: 0, blur: 6, opacity: 0.1 } },
        series: [
          { name: 'Pedidos', type: 'bar', data: D.pedidosHora },
          { name: 'Ventas $', type: 'line', data: D.ventasHora }
        ],
        colors: [AMARILLO, AZUL],
        stroke: { width: [0, 3], curve: 'smooth', lineCap: 'round' },
        plotOptions: { bar: { borderRadius: 4, columnWidth: '62%', borderRadiusApplication: 'end' } },
        markers: { size: 4, strokeWidth: 2, strokeColors: '#fff', hover: { size: 6 } },
        xaxis: { categories: D.horas, tickAmount: 12, labels: { style: { colors: '#6c757d', fontSize: '11px' } }, axisBorder: { show: false }, axisTicks: { show: false } },
        yaxis: [
          { title: { text: 'Pedidos', style: { color: '#6c757d', fontSize: '11px', fontWeight: 700 } }, labels: { style: { colors: '#6c757d' } } },
          { opposite: true, title: { text: 'Ventas $', style: { color: '#6c757d', fontSize: '11px', fontWeight: 700 } }, labels: { formatter: v => '$' + v, style: { colors: '#6c757d' } } }
        ],
        grid: { borderColor: 'rgba(11,11,69,0.07)', strokeDashArray: 4, padding: { top: 8 } },
        tooltip: { shared: true, intersect: false, theme: 'dark', y: { formatter: (v, ctx) => ctx.seriesIndex === 0 ? v + ' pedidos' : money(v) } },
        legend: { show: true, position: 'bottom', horizontalAlign: 'right', labels: { colors: '#111', fontWeight: 700 } },
        noData: { text: 'Sin pedidos hoy' }
      });
      return ch;
    },

    canal: function (el) {
      const total = D.canalLlevar + D.canalRecoger;
      const ch = montar(el, {
        chart: { type: 'donut', fontFamily: 'inherit', background: 'transparent', animations: { speed: 600 } },
        series: total > 0 ? [D.canalLlevar, D.canalRecoger] : [0, 0],
        labels: ['Para llevar', 'Recoger en tienda'],
        colors: [AZUL, AMARILLO],
        stroke: { width: 3, colors: ['#fff'] },
        dataLabels: { enabled: total > 0, formatter: (v) => v.toFixed(0) + '%', style: { fontWeight: 700, fontSize: '13px' } },
        legend: { position: 'bottom', labels: { colors: '#111', fontWeight: 700 } },
        tooltip: { theme: 'dark', y: { formatter: v => v + ' pedidos' } },
        plotOptions: { pie: { donut: { size: '64%', labels: { show: true, name: { fontWeight: 700 }, value: { fontWeight: 800, fontSize: '22px' }, total: { show: true, label: 'Pedidos hoy', fontWeight: 700, formatter: () => total } } } } },
        noData: { text: 'Sin pedidos por canal' }
      });
      return ch;
    },

    top: function (el) {
      const hay = D.topProductos.length > 0;
      const ch = montar(el, {
        chart: { type: 'donut', fontFamily: 'inherit', background: 'transparent', animations: { speed: 600 } },
        series: hay ? D.topProductos.map(p => p.uds) : [0],
        labels: hay ? D.topProductos.map(p => p.nombre) : ['Sin datos'],
        colors: colores,
        stroke: { width: 2, colors: ['#fff'] },
        dataLabels: { enabled: hay, formatter: v => Math.round(v) + '%', style: { fontWeight: 700 } },
        legend: { show: false },
        tooltip: { theme: 'dark', y: { formatter: v => v + ' uds' } },
        plotOptions: { pie: { donut: { size: '62%', labels: { show: hay, name: { fontWeight: 700 }, total: { show: hay, label: 'Total', fontWeight: 700, formatter: () => D.topProductos.reduce((a, p) => a + p.uds, 0) + ' uds' } } } } },
        noData: { text: 'Sin productos vendidos aún' }
      });
      return ch;
    },

    estados: function (el) {
      const hay = D.estados.some(e => e.n > 0);
      const totalEstados = D.estados.reduce((a, e) => a + e.n, 0);
      const ch = montar(el, {
        chart: { type: 'bar', fontFamily: 'inherit', background: 'transparent', animations: { speed: 600 },
                 toolbar: { show: true, tools: { download: true, selection: false, zoom: false, zoomin: false, zoomout: false, pan: false, reset: false } } },
        series: [{ name: 'Pedidos', data: D.estados.map(e => e.n) }],
        labels: D.estados.map(e => e.label),
        colors: D.estados.map(e => e.estado === 'pendiente' ? '#D97706' : e.estado === 'cocina' ? AMARILLO : e.estado === 'listo' ? '#0a7a42' : AZUL),
        plotOptions: { bar: { horizontal: true, borderRadius: 6, barHeight: '58%', distributed: true } },
        dataLabels: { enabled: hay, formatter: v => v.toFixed(0), offsetX: -4, position: 'insideEnd', style: { fontWeight: 800, fontSize: '13px', colors: ['#fff'] } },
        // Con pocos pedidos el eje sacaba marcas fraccionarias y repetia
        // ("0 1 1 2 2 3 3"): se fijan tantas marcas como pedidos hay.
        xaxis: { tickAmount: Math.max(1, totalEstados), forceNiceScale: false, labels: { formatter: v => String(Math.round(v)), style: { colors: '#111', fontWeight: 700 } } },
        grid: { borderColor: 'rgba(11,11,69,0.07)', strokeDashArray: 4 },
        legend: { show: false },
        tooltip: { theme: 'dark', y: { formatter: v => v + ' pedidos' } },
        noData: { text: 'Sin pedidos hoy' }
      });
      return ch;
    }
  };

  /* ============================================================
     Cambio de pestaña (paginación de gráficas)
     ============================================================ */
  const instancias = {};   // clave -> ApexCharts ya montada
  const alturas   = {};   // clave -> último alto en píxeles aplicado
  const vigilados = {};   // clave -> ResizeObserver, para no duplicarlos

  /* El alto del panel no se conoce al montar: al cargar la página la rejilla
     todavía se está asentar y la gráfica quedaba más alta que su caja, con las
     leyendas del eje desbordadas encima de los paneles de abajo. Con esto la
     altura se reajusta sola ante cualquier cambio (recarga, sidebar, ventana). */
  function observar(el, clave) {
    if (vigilados[clave] || typeof ResizeObserver === 'undefined') return;
    const ro = new ResizeObserver(function () {
      const c = instancias[clave];
      if (!c) return;
      const alto = Math.round(el.getBoundingClientRect().height);
      if (alto < 80 || Math.abs(alto - (alturas[clave] || 0)) < 3) return;
      alturas[clave] = alto;
      c.updateOptions({ chart: { height: alto } }, false, false);
    });
    ro.observe(el);
    vigilados[clave] = ro;
  }

  function activar(clave) {
    document.querySelectorAll('.dash-tab').forEach(b => {
      const on = b.getAttribute('data-tab') === clave;
      b.setAttribute('aria-selected', on ? 'true' : 'false');
    });
    document.querySelectorAll('.dash-tabpanel').forEach(p => {
      p.classList.toggle('is-active', p.id === 'panel-' + clave);
    });

    const el = document.getElementById('dashChart' + clave.charAt(0).toUpperCase() + clave.slice(1));
    if (!el || !fabricas[clave]) return;

    if (instancias[clave]) {
      // Ya estaba montada: solo hay que reajustarla al nuevo tamaño visible
      const previa = instancias[clave];
      if (previa && typeof previa.resize === 'function') previa.resize();
      observar(el, clave);
      return;
    }

    /* Montaje perezoso y SÍNCRONO a propósito: las clases del panel se
       acaban de cambiar, así que el elemento ya tiene tamaño medible (ApexCharts
       fuerza el reflow al leerlo). Usar requestAnimationFrame aquí lo ataría al
       pintado de la página: con la pestaña en segundo plano los callbacks no se
       ejecutarían y el dashboard se quedaría sin gráficas. */
    instancias[clave] = fabricas[clave](el);
    alturas[clave] = Math.round(el.getBoundingClientRect().height);
    observar(el, clave);
  }

  document.getElementById('dashTabs').addEventListener('click', e => {
    const btn = e.target.closest('[data-tab]');
    if (btn) activar(btn.getAttribute('data-tab'));
  });

  // Flechas izquierda/derecha sobre la barra de pestañas
  document.getElementById('dashTabs').addEventListener('keydown', e => {
    if (e.key !== 'ArrowRight' && e.key !== 'ArrowLeft') return;
    const tabs = Array.prototype.slice.call(document.querySelectorAll('.dash-tab'));
    const i = tabs.indexOf(document.activeElement);
    if (i < 0) return;
    e.preventDefault();
    const sig = tabs[(i + (e.key === 'ArrowRight' ? 1 : tabs.length - 1)) % tabs.length];
    sig.focus();
    activar(sig.getAttribute('data-tab'));
  });

  // Al cambiar el tamaño de la ventana solo la pestaña visible se reajusta
  // (el alto lo lleva el ResizeObserver; aquí el ancho, que ApexCharts
  //  recalcula solo al recibir resize()).
  window.addEventListener('resize', function () {
    const activa = document.querySelector('.dash-tab[aria-selected="true"]');
    if (!activa) return;
    const clave = activa.getAttribute('data-tab');
    const c = instancias[clave];
    if (c && typeof c.resize === 'function') c.resize();
  });

  // Primera pestaña al abrir
  activar('ventas');
})();
</script>