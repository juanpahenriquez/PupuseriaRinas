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
    'pedidos_recientes'  => [],
    'total_pedidos'      => 0,
];
$hoy      = $hoy      ?? null;   // fila de horarios del día (array|null)
$rangoHoy = $rangoHoy ?? '';
$abierta  = $abierta  ?? false;
$ventasSemanaTotal = array_sum($mock['ventas_semana']);
$trendVentas = $mock['ventas_hoy'] - $mock['ventas_ayer'];
$trendPct = $mock['ventas_ayer'] > 0 ? ($trendVentas / $mock['ventas_ayer'] * 100) : 0;
$estadoBadge = ['pendiente' => 'badge-pendiente', 'cocina' => 'badge-cocina', 'listo' => 'badge-listo', 'entregado' => 'badge-entregado'];
$estadoLabel = ['pendiente' => 'Pendiente', 'cocina' => 'En cocina', 'listo' => 'Listo', 'entregado' => 'Entregado'];
?>

<!-- Toolbar rápida -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <span class="d-inline-flex align-items-center gap-2 small fw-bold" style="background:#fff;border:1px solid #000;padding:0.35rem 0.7rem;box-shadow:0 4px 12px rgba(0,0,0,0.06)">
            <span style="width:8px;height:8px;border-radius:50%;background:#0a7a42;display:inline-block;box-shadow:0 0 0 4px rgba(10,122,66,0.2)"></span>
            Conectado a MySQL · <?php echo (int) $mock['total_pedidos']; ?> pedido<?php echo $mock['total_pedidos'] == 1 ? '' : 's'; ?> en total
        </span>
        <span class="small text-muted d-none d-md-inline">Actualizado <?php echo date('H:i'); ?></span>
    </div>
    <div class="d-flex gap-2">
        <a href="pedidos.php" class="btn btn-sm btn-admin btn-admin--black">Ver pedidos →</a>
    </div>
</div>

<!-- KPIs -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="admin-kpi admin-kpi--premium">
            <div class="admin-kpi-top">
                <div>
                    <div class="admin-kpi-label">Ventas hoy</div>
                    <div class="admin-kpi-value">$<?php echo number_format($mock['ventas_hoy'], 2); ?></div>
                </div>
                <span class="admin-kpi-icon naranja"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 8c-2 0-4 1-4 3s2 3 4 3 4-1 4-3-2-3-4-3z"/><path d="M12 14v4"/><path d="M8 10V9a4 4 0 0 1 8 0v1"/><path d="M8 14c0 2 1.8 4 4 4s4-2 4-4"/></svg></span>
            </div>
            <span class="admin-kpi-trend <?php echo $trendVentas >= 0 ? 'up' : 'down'; ?>"><?php echo ($trendVentas >= 0 ? '↑ ' : '↓ ') . number_format(abs($trendPct), 1) . '% vs ayer'; ?></span>
            <div id="kpiSparkVentas" class="admin-kpi-spark" aria-hidden="true"></div>
            <div class="small text-muted" style="font-size:0.72rem">Total semana: $<?php echo number_format($ventasSemanaTotal, 2); ?></div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="admin-kpi">
            <div class="admin-kpi-top">
                <div>
                    <div class="admin-kpi-label">Pedidos pendientes</div>
                    <div class="admin-kpi-value"><?php echo $mock['pedidos_pendientes']; ?></div>
                </div>
                <span class="admin-kpi-icon azul"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6h6"/><path d="M9 10h6"/><path d="M9 14h6"/><rect x="4" y="3" width="16" height="18" rx="2"/></svg></span>
            </div>
            <span class="admin-kpi-trend"><?php echo $mock['pedidos_llevar']; ?> para llevar · <?php echo $mock['pedidos_recoger']; ?> recoger</span>
            <div id="kpiMiniDonut" style="height:38px" aria-hidden="true"></div>
            <div class="small text-muted" style="font-size:0.72rem">Reparto de pedidos</div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="admin-kpi">
            <div class="admin-kpi-top">
                <div>
                    <div class="admin-kpi-label">Ticket promedio</div>
                    <div class="admin-kpi-value">$<?php echo number_format($mock['ticket'], 2); ?></div>
                </div>
                <span class="admin-kpi-icon crema"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
            </div>
            <span class="admin-kpi-trend">Meta $<?php echo number_format($mock['ticket_meta'], 2); ?> · <?php echo $mock['ticket_meta_pct']; ?>%</span>
            <div id="kpiTicketRadial" style="height:38px" aria-hidden="true"></div>
            <div class="small text-muted" style="font-size:0.72rem">Avance hacia la meta</div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="admin-kpi">
            <div class="admin-kpi-top">
                <div>
                    <div class="admin-kpi-label">Productos activos</div>
                    <div class="admin-kpi-value"><?php echo $mock['productos_activos']; ?></div>
                </div>
                <span class="admin-kpi-icon naranja"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 8h15l-1.5 12.5a1 1 0 0 1-1 .5H5.5a1 1 0 0 1-1-.5L3 8h3z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></svg></span>
            </div>
            <span class="admin-kpi-trend">En el menú hoy</span>
            <div class="d-flex gap-1 flex-wrap mt-1">
                <span class="badge-rinas" style="background:#fff;border:1px solid #000;color:#6c757d"><?php echo $mock['productos_activos']; ?> ok</span>
            </div>
        </div>
    </div>
</div>

<!-- Ventas + Top productos -->
<div class="row g-3 mb-4">
    <div class="col-12 col-lg-8">
        <div class="admin-panel admin-panel--elevated h-100">
            <div class="admin-panel-head">
                <div>
                    <h2 class="admin-panel-title">Ventas · Pedidos</h2>
                    <div class="small text-muted" style="font-size:0.78rem">Últimos 7 días — datos reales</div>
                </div>
                <div class="d-flex gap-2" id="ventasToggle">
                    <button class="admin-filter active" data-range="semana" type="button">Semana</button>
                    <button class="admin-filter" data-range="mes" type="button">Mes</button>
                </div>
            </div>
            <div id="ventasChartApex" style="min-height:260px" class="d-flex align-items-center justify-content-center">
                <span class="small text-muted" style="border:1px dashed #000;background:#FFFBEB;padding:0.6rem 0.9rem">Sin ventas para mostrar</span>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="admin-panel admin-panel--elevated h-100">
            <div class="admin-panel-head">
                <h2 class="admin-panel-title">Top productos</h2>
                <span class="small text-muted">Unidades</span>
            </div>
            <div id="topDonutApex" style="min-height:190px" class="d-flex align-items-center justify-content-center mb-2">
                <span class="small text-muted text-center" style="border:1px dashed #000;background:#fff;padding:0.6rem 0.9rem">Sin productos vendidos aún</span>
            </div>
            <!-- Top productos calculado desde pedido_items -->
            <div class="d-flex flex-column gap-2">
                <?php if (empty($mock['top_productos'])): ?>
                <p class="small text-muted mb-0">Sin ventas registradas — crea pedidos y este listado se llenará.</p>
                <?php else: foreach ($mock['top_productos'] as $p): ?>
                <div class="d-flex align-items-center gap-2 small">
                    <span class="fw-bold" style="min-width:130px"><?php echo $p['nombre']; ?></span>
                    <div class="progress flex-grow-1" style="min-width:60px">
                        <div class="progress-bar <?php echo $p['pct'] >= 74 ? 'progress-bar--naranja' : 'progress-bar--azul'; ?>" style="width:<?php echo $p['pct']; ?>%"></div>
                    </div>
                    <span class="badge-rinas" style="min-width:34px;text-align:center"><?php echo $p['uds']; ?></span>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Canal -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="admin-panel h-100">
            <div class="admin-panel-head">
                <h2 class="admin-panel-title">Pedidos por canal</h2>
                <span class="small text-muted">Hoy</span>
            </div>
            <div id="canalDonutApex" style="min-height:180px" class="d-flex align-items-center justify-content-center">
                <span class="small text-muted" style="border:1px dashed #000;background:#fff;padding:0.6rem 0.9rem">Sin pedidos por canal</span>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-8">
        <div class="admin-panel h-100">
            <div class="admin-panel-head">
                <h2 class="admin-panel-title">Pedidos recientes</h2>
                <a href="pedidos.php" class="btn btn-sm btn-admin btn-admin--black">Ver todos →</a>
            </div>
            <!-- Pedidos recientes desde MySQL -->
            <?php if (empty($mock['pedidos_recientes'])): ?>
            <div class="text-center py-4" style="border:1px dashed #000;background:#FFF6D3">
                <p class="small text-muted mb-2">Aún no hay pedidos — cuando se creen aparecerán aquí.</p>
                <a href="pedidos.php" class="btn btn-sm btn-admin btn-admin--black">Crear primer pedido</a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table admin-table mb-0">
                    <thead>
                        <tr><th>Folio</th><th>Tipo</th><th>Detalle</th><th>Total</th><th>Estado</th><th>Hora</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mock['pedidos_recientes'] as $ped): ?>
                        <tr>
                            <td class="fw-bold"><?php echo $ped[0]; ?></td>
                            <td><?php echo $ped[1]; ?></td>
                            <td class="text-muted"><?php echo $ped[2]; ?></td>
                            <td class="fw-bold">$<?php echo number_format($ped[3], 2); ?></td>
                            <td><span class="badge-rinas <?php echo $estadoBadge[$ped[4]]; ?>"><?php echo $estadoLabel[$ped[4]]; ?></span></td>
                            <td class="text-muted"><?php echo $ped[5]; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="d-flex flex-column gap-3 h-100">
            <div class="admin-panel">
                <h2 class="admin-panel-title mb-3">Estado de hoy</h2>
                <?php if ($hoy === null || ($rangoHoy === '' && empty($hoy['cerrado']))): ?>
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="width:48px;height:48px;border-radius:50%;background:#FFF4DC;border:1px solid rgba(245,158,11,0.4);display:flex;align-items:center;justify-content:center;color:#D97706"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></div>
                    <div>
                        <div class="fw-bold">Sin horario definido</div>
                        <div class="small text-muted" style="font-size:0.78rem">Configura los horarios para ver aquí el estado</div>
                    </div>
                </div>
                <?php else: ?>
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="width:48px;height:48px;border-radius:50%;background:<?= $abierta ? '#E6F4EA' : '#F5F5F5' ?>;border:1px solid <?= $abierta ? 'rgba(10,122,66,0.35)' : 'rgba(0,0,0,0.15)' ?>;display:flex;align-items:center;justify-content:center;color:<?= $abierta ? '#0a7a42' : '#6c757d' ?>"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="9"/></svg></div>
                    <div>
                        <div class="fw-bold"><?= $abierta ? 'Abierto ahora' : 'Cerrado ahora' ?> <span class="badge-rinas <?= $abierta ? 'badge-listo' : 'badge-pendiente' ?>"><?= $abierta ? 'Abierto' : 'Cerrado' ?></span></div>
                        <div class="small text-muted" style="font-size:0.78rem"><?= $rangoHoy !== '' ? htmlspecialchars($rangoHoy) : 'Cerrado hoy' ?></div>
                    </div>
                </div>
                <?php endif; ?>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="horarios.php" class="small fw-bold" style="color:var(--rinas-azul)">Editar horarios →</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= LINK_APEXCHARTS_JS ?>"></script>
<script>
(function(){
  if(typeof ApexCharts === 'undefined') return;

  const dias = <?php echo json_encode($mock['dias']); ?>;
  const semanas = <?php echo json_encode($mock['semanas']); ?>;
  const dataSemana = <?php echo json_encode($mock['ventas_semana']); ?>;
  const pedidosSemana = <?php echo json_encode($mock['pedidos_semana']); ?>;
  const dataMes = <?php echo json_encode($mock['ventas_mes']); ?>;
  const pedidosMes = <?php echo json_encode($mock['pedidos_mes']); ?>;
  const topProductos = <?php echo json_encode($mock['top_productos']); ?>;
  const llevar = <?php echo (int)$mock['pedidos_llevar']; ?>;
  const recoger = <?php echo (int)$mock['pedidos_recoger']; ?>;
  const canalLlevar = <?php echo (int)$mock['canal_llevar']; ?>;
  const canalRecoger = <?php echo (int)$mock['canal_recoger']; ?>;
  const ticketMetaPct = <?php echo (int)$mock['ticket_meta_pct']; ?>;
  const fmtMoney = v => '$' + Number(v).toFixed(2);

  const chartColors = ['#F59E0B', '#0B0B45'];

  // ===== Ventas (área + columnas) con toggle Semana/Mes =====
  const elVentas = document.getElementById('ventasChartApex');
  let ventasChart = null;
  if(elVentas){
    elVentas.innerHTML = '';
    elVentas.classList.remove('d-flex','align-items-center','justify-content-center');
    ventasChart = new ApexCharts(elVentas, {
      chart: { type: 'area', height: 260, toolbar: { show: true, tools: { download: true, selection: false, zoom: false, zoomin: false, zoomout: false, pan: false, reset: false } }, fontFamily: 'inherit', background: 'transparent', animations: { enabled: true, easing: 'easeinout', speed: 700 }, dropShadow: { enabled: true, top: 6, left: 0, blur: 8, opacity: 0.12 } },
      series: [
        { name: 'Ventas $', type: 'area', data: dataSemana },
        { name: 'Pedidos', type: 'column', data: pedidosSemana }
      ],
      stroke: { width: [3, 0], curve: 'smooth', lineCap: 'round' },
      fill: { type: ['gradient', 'solid'], gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.02, colorStops: [{ offset: 0, color: '#F59E0B', opacity: 0.45 }, { offset: 100, color: '#FFF6D3', opacity: 0.02 }] } },
      colors: chartColors,
      markers: { size: 5, strokeWidth: 2, strokeColors: '#fff', colors: ['#F59E0B'], hover: { size: 7 } },
      xaxis: { categories: dias, labels: { style: { colors: '#111', fontWeight: 700 } }, axisBorder: { show: false }, axisTicks: { show: false } },
      yaxis: [
        { labels: { formatter: v => '$' + v, style: { colors: '#6c757d' } } },
        { opposite: true, labels: { formatter: v => v + ' ped', style: { colors: '#6c757d' } } }
      ],
      grid: { borderColor: 'rgba(11,11,69,0.07)', strokeDashArray: 4, padding: { top: 8 } },
      plotOptions: { bar: { borderRadius: 6, columnWidth: '34%', borderRadiusApplication: 'end' } },
      tooltip: { shared: true, intersect: false, theme: 'dark', y: { formatter: (v, { seriesIndex }) => seriesIndex === 0 ? fmtMoney(v) : v + ' pedidos' } },
      legend: { show: false },
      noData: { text: 'Sin ventas para mostrar' }
    });
    ventasChart.render();

    const toggle = document.getElementById('ventasToggle');
    if(toggle){
      toggle.addEventListener('click', e => {
        const btn = e.target.closest('[data-range]');
        if(!btn) return;
        toggle.querySelectorAll('.admin-filter').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        if(btn.getAttribute('data-range') === 'mes'){
          ventasChart.updateOptions({ xaxis: { categories: semanas } });
          ventasChart.updateSeries([{ data: dataMes }, { data: pedidosMes }]);
        } else {
          ventasChart.updateOptions({ xaxis: { categories: dias } });
          ventasChart.updateSeries([{ data: dataSemana }, { data: pedidosSemana }]);
        }
      });
    }
  }

  // ===== Sparkline de ventas (KPI) =====
  const elSpark = document.getElementById('kpiSparkVentas');
  if(elSpark){
    new ApexCharts(elSpark, {
      chart: { type: 'area', height: 42, sparkline: { enabled: true }, animations: { enabled: false } },
      series: [{ name: 'Ventas', data: dataSemana }],
      colors: ['#F59E0B'],
      stroke: { curve: 'smooth', width: 2 },
      fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.02 } },
      markers: { size: 0 },
      tooltip: { theme: 'dark', y: { formatter: fmtMoney } },
      dataLabels: { enabled: false }
    }).render();
  }

  // ===== Mini donut — reparto llevar/recoger =====
  const elMiniDonut = document.getElementById('kpiMiniDonut');
  if(elMiniDonut){
    new ApexCharts(elMiniDonut, {
      chart: { type: 'donut', height: 38, sparkline: { enabled: true }, animations: { enabled: false } },
      series: [llevar, recoger],
      labels: ['Llevar', 'Recoger'],
      colors: ['#0B0B45', '#F59E0B'],
      stroke: { width: 0 },
      legend: { show: false },
      tooltip: { theme: 'dark', y: { formatter: v => v + ' pedidos' } },
      dataLabels: { enabled: false }
    }).render();
  }

  // ===== Radial — ticket vs meta =====
  const elRadial = document.getElementById('kpiTicketRadial');
  if(elRadial){
    new ApexCharts(elRadial, {
      chart: { type: 'radialBar', height: 38, sparkline: { enabled: true }, animations: { enabled: false } },
      series: [ticketMetaPct],
      colors: ['#F59E0B'],
      labels: ['Meta'],
      plotOptions: { radialBar: { hollow: { size: '62%' }, track: { background: '#FFF6D3' }, dataLabels: { name: { show: false }, value: { show: true, fontSize: '11px', fontWeight: 700, color: '#0B0B45', offsetY: 2, formatter: v => v + '%' } } } },
      tooltip: { theme: 'dark' }
    }).render();
  }

  // ===== Top productos — donut =====
  const elTop = document.getElementById('topDonutApex');
  if(elTop){
    elTop.innerHTML = '';
    elTop.classList.remove('d-flex','align-items-center','justify-content-center');
    new ApexCharts(elTop, {
      chart: { type: 'donut', height: 190, animations: { enabled: true, easing: 'easeinout', speed: 600 } },
      series: topProductos.map(p => p.uds),
      labels: topProductos.map(p => p.nombre),
      colors: ['#F59E0B', '#0B0B45', '#D97706', '#1B1B6B', '#E8E8F5'],
      stroke: { width: 2, colors: ['#fff'] },
      dataLabels: { enabled: true, formatter: v => Math.round(v) + '%', style: { fontWeight: 700 } },
      legend: { show: false },
      tooltip: { theme: 'dark', y: { formatter: v => v + ' uds' } },
      plotOptions: { pie: { donut: { size: '62%', labels: { show: true, name: { fontWeight: 700 }, total: { label: 'Total', show: true, fontWeight: 700, formatter: () => topProductos.reduce((a, p) => a + p.uds, 0) + ' uds' } } } } },
      noData: { text: 'Sin productos vendidos aún' }
    }).render();
  }

  // ===== Canal — donut llevar vs recoger =====
  const elCanal = document.getElementById('canalDonutApex');
  if(elCanal){
    elCanal.innerHTML = '';
    elCanal.classList.remove('d-flex','align-items-center','justify-content-center');
    new ApexCharts(elCanal, {
      chart: { type: 'donut', height: 180 },
      series: [canalLlevar, canalRecoger],
      labels: ['Para llevar', 'Recoger en tienda'],
      colors: ['#0B0B45', '#F59E0B'],
      stroke: { width: 2, colors: ['#fff'] },
      dataLabels: { enabled: true, formatter: v => Math.round(v) + '%', style: { fontWeight: 700 } },
      legend: { position: 'right', horizontalAlign: 'center', labels: { colors: '#111', fontWeight: 700 } },
      tooltip: { theme: 'dark', y: { formatter: v => v + ' pedidos' } },
      plotOptions: { pie: { donut: { size: '55%' } } },
      noData: { text: 'Sin pedidos por canal' }
    }).render();
  }
})();
</script>
