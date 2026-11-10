{{-- Autor: Marbin --}}
@extends('layouts.app')

@section('content')
<div class="container">

    <div class="cmp-head">
        <h1 class="mb-1">{{ __('messages.comparator_title') }}</h1>
        <p class="text-muted mb-0">{{ __('messages.comparator_subtitle') }}</p>
    </div>

    {{-- ============ Comparacion directa 2 a 2 ============ --}}
    <div class="cmp-panel">
        <h2 class="h5 mb-1">{{ __('messages.direct_comparison') }}</h2>
        <p class="text-muted small mb-3">{{ __('messages.direct_comparison_help') }}</p>

        <form action="{{ route('comparator.index') }}" method="GET" class="row g-2 align-items-end" id="form-comparacion">
            @foreach(['a' => $productoA, 'b' => $productoB] as $letra => $seleccionado)
                <div class="col-md-5">
                    <label class="form-label small fw-semibold mb-1">{{ $letra === 'a' ? __('messages.product_a') : __('messages.product_b') }}</label>
                    <select name="product_{{ $letra }}_id" id="sel-{{ $letra }}" class="form-select" required>
                        <option value="">{{ __('messages.select_option') }}</option>
                        @foreach($productos as $producto)
                            <option value="{{ $producto->getId() }}" data-cat="{{ $producto->getCategoryId() }}"
                                    @selected($seleccionado?->getId() === $producto->getId())>
                                {{ $producto->getNombre() }} ({{ $producto->getNombreCategoria() }})
                            </option>
                        @endforeach
                    </select>
                </div>
            @endforeach
            <div class="col-md-2"><button class="btn btn-primary w-100">{{ __('messages.compare') }}</button></div>
        </form>
    </div>

    @if($errorComparacion)
        <div class="alert alert-warning">{{ $errorComparacion }}</div>
    @endif

    @if($comparacion)
        @php($v = $comparacion['veredicto'])
        @php($ganA = $v['ganador'] === 'a')
        @php($ganB = $v['ganador'] === 'b')

        {{-- Cabecera de versus con los dos productos --}}
        <div class="cmp-versus">
            <div class="cmp-side {{ $ganA ? 'win' : '' }}">
                @if($ganA)<span class="cmp-crown">{{ __('messages.best_in_specs') }}</span>@endif
                <div class="cmp-emoji">{!! $catIcon($productoA->getCategorySlug()) !!}</div>
                <div class="cmp-brand">{{ $productoA->getMarca() }}</div>
                <div class="cmp-name">{{ $productoA->getNombre() }}</div>
                @if($rendimiento && $rendimiento['indice_a'] !== null)
                    <div class="cmp-score">{{ $rendimiento['indice_a'] }}<small>/100</small></div>
                    <div class="cmp-score-label">{{ __('messages.performance_index') }}</div>
                @endif
            </div>

            <div class="cmp-vs">VS</div>

            <div class="cmp-side {{ $ganB ? 'win' : '' }}">
                @if($ganB)<span class="cmp-crown">{{ __('messages.best_in_specs') }}</span>@endif
                <div class="cmp-emoji">{!! $catIcon($productoB->getCategorySlug()) !!}</div>
                <div class="cmp-brand">{{ $productoB->getMarca() }}</div>
                <div class="cmp-name">{{ $productoB->getNombre() }}</div>
                @if($rendimiento && $rendimiento['indice_b'] !== null)
                    <div class="cmp-score">{{ $rendimiento['indice_b'] }}<small>/100</small></div>
                    <div class="cmp-score-label">{{ __('messages.performance_index') }}</div>
                @endif
            </div>
        </div>

        {{-- Veredicto --}}
        <div class="card mb-4">
            <div class="card-body">
                <h3 class="h5 mb-2">{{ $v['titulo'] }}</h3>
                @foreach($v['frases'] as $frase)
                    <p class="mb-1">{{ $frase }}</p>
                @endforeach
                <hr class="my-2">
                @foreach($comparacion['avisos'] as $aviso)
                    <div class="small text-muted">{{ $aviso }}</div>
                @endforeach
            </div>
        </div>

        {{-- ============ Graficos ============ --}}
        @if($rendimiento && ($rendimiento['indice_a'] !== null || $rendimiento['indice_b'] !== null))
            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="card h-100"><div class="card-body">
                        <h3 class="h6 mb-1">{{ __('messages.performance_estimated') }}</h3>
                        <p class="text-muted small mb-3">{{ __('messages.global_index_help') }}</p>
                        <div class="cmp-chart cmp-chart-sm"><canvas id="chartGlobal"></canvas></div>
                    </div></div>
                </div>
                <div class="col-lg-6">
                    <div class="card h-100"><div class="card-body">
                        <h3 class="h6 mb-1">{{ __('messages.profile_by_feature') }}</h3>
                        <p class="text-muted small mb-3">{{ __('messages.profile_help') }}</p>
                        <div class="cmp-chart"><canvas id="chartRadar"></canvas></div>
                    </div></div>
                </div>
                <div class="col-12">
                    <div class="card"><div class="card-body">
                        <h3 class="h6 mb-1">{{ __('messages.comparison_by_spec') }}</h3>
                        <p class="text-muted small mb-3">{{ __('messages.comparison_by_spec_help') }}</p>
                        <div class="cmp-chart cmp-chart-wide"><canvas id="chartBarras"></canvas></div>
                    </div></div>
                </div>
            </div>
        @endif

        {{-- Tabla detallada --}}
        <h3 class="h5 mb-2">{{ __('messages.detailed_specs') }}</h3>
        <div class="table-responsive mb-5">
            <table class="table table-bordered bg-white cmp-table">
                <thead class="table-dark">
                    <tr>
                        <th style="width:32%">{{ __('messages.feature') }}</th>
                        <th>{{ $productoA->getNombre() }}</th>
                        <th>{{ $productoB->getNombre() }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($comparacion['filas'] as $fila)
                        <tr>
                            <th class="text-muted fw-normal">{{ $fila['etiqueta'] }}</th>
                            <td class="{{ $fila['ganador'] === 'a' ? 'cmp-win' : '' }}">
                                {{ $fila['a'] }}
                                @if($fila['ganador'] === 'a' && $fila['ventaja'])<span class="cmp-adv">{{ $fila['ventaja'] }}</span>@endif
                            </td>
                            <td class="{{ $fila['ganador'] === 'b' ? 'cmp-win' : '' }}">
                                {{ $fila['b'] }}
                                @if($fila['ganador'] === 'b' && $fila['ventaja'])<span class="cmp-adv">{{ $fila['ventaja'] }}</span>@endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- ============ Mi comparador (lista personal) ============ --}}
    <h2 class="h5 mt-4 mb-3">{{ __('messages.my_comparison_list') }}</h2>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card h-100"><div class="card-body">
                <h3 class="h6">{{ __('messages.add_products') }}</h3>
                <div class="cmp-add-list">
                    @foreach($productos as $producto)
                        <div class="cmp-add-row">
                            <div>
                                <div class="fw-semibold small">{{ $producto->getNombre() }}</div>
                                <div class="text-muted" style="font-size:.75rem">{{ $producto->getNombreCategoria() }}
                                    · {{ $producto->esVendible() ? '$'.number_format($producto->getPrecio(), 2) : __('messages.price_tbd') }}</div>
                            </div>
                            <form action="{{ route('comparator.add', $producto) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-primary">+ {{ __('messages.add') }}</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div></div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100"><div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h3 class="h6 mb-0">{{ __('messages.in_comparator') }}</h3>
                    @if(! $cuadro->isEmpty())
                        <form action="{{ route('comparator.clear') }}" method="POST">
                            @csrf @method('DELETE')
                            <button class="btn btn-outline-danger btn-sm">{{ __('messages.clear') }}</button>
                        </form>
                    @endif
                </div>

                @if($cuadro->isEmpty())
                    <p class="text-muted small mb-0">{{ __('messages.empty_comparator') }}</p>
                @else
                    <table class="table table-sm align-middle mb-0">
                        <tbody>
                            @foreach($cuadro as $fila)
                                <tr>
                                    <td>
                                        <div class="fw-semibold small">{{ $fila['nombre'] }}</div>
                                        <div class="text-muted" style="font-size:.72rem">{{ $fila['categoria'] }}</div>
                                    </td>
                                    <td class="text-end">
                                        {{ $fila['disponible'] || $fila['precio'] > 0 ? '$'.number_format($fila['precio'], 2) : __('messages.price_tbd') }}
                                    </td>
                                    <td class="text-end">
                                        <form action="{{ route('comparator.remove', $fila['id']) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('messages.remove') }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div></div>
        </div>
    </div>

    {{-- ============ Compatibilidad del armado (lista) ============ --}}
    @unless($cuadro->isEmpty())
        <h2 class="h5 mt-5 mb-3">{{ __('messages.compatibility_list_title') }}</h2>

        @if(count($compatibilidad) === 0)
            <p class="text-muted">{{ __('messages.add_two_to_check') }}</p>
        @else
            <div class="table-responsive">
                <table class="table table-bordered bg-white">
                    <thead class="table-light">
                        <tr><th>{{ __('messages.component_a') }}</th><th>{{ __('messages.component_b') }}</th><th>{{ __('messages.status') }}</th><th>{{ __('messages.reason') }}</th></tr>
                    </thead>
                    <tbody>
                        @foreach($compatibilidad as $par)
                            <tr>
                                <td>{{ $par['producto_a'] }}</td>
                                <td>{{ $par['producto_b'] }}</td>
                                <td>
                                    @if($par['estado'] === 'warn')
                                        <span class="badge bg-warning text-dark">{{ __('messages.compat_warn') }}</span>
                                    @elseif($par['estado'] === 'na')
                                        <span class="badge bg-secondary">{{ __('messages.compat_na') }}</span>
                                    @elseif($par['estado'] === 'ninguna')
                                        <span class="badge bg-secondary">{{ __('messages.compat_none') }}</span>
                                    @elseif($par['compatible'])
                                        <span class="badge bg-success">{{ __('messages.compat_ok') }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ __('messages.compat_bad') }}</span>
                                    @endif
                                </td>
                                <td class="small">{{ $par['mensaje'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if($consumo)
            <div class="alert alert-{{ ['ok' => 'success', 'warn' => 'warning', 'bad' => 'danger'][$consumo['estado']] }}">
                <strong>{{ __('messages.equipment_consumption') }}:</strong> {{ $consumo['mensaje'] }}
            </div>
        @endif
    @endunless

    <div class="cmp-builder-cta">
        <div>
            <div class="fw-bold">{{ __('messages.builder_cta_title') }}</div>
            <div class="small text-muted">{{ __('messages.builder_cta_subtitle') }}</div>
        </div>
        <a href="{{ route('builder.index') }}" class="btn btn-primary">{{ __('messages.go_to_builder') }}</a>
    </div>
</div>

{{-- Filtrado de categoria del selector B --}}
<script>
    (function () {
        const a = document.getElementById('sel-a'), b = document.getElementById('sel-b');
        if (!a || !b) return;
        function filtrar() {
            const cat = a.selectedOptions[0]?.dataset.cat;
            Array.from(b.options).forEach(o => { if (o.value) o.hidden = !!cat && o.dataset.cat !== cat; });
            if (b.selectedOptions[0]?.hidden) b.value = '';
        }
        a.addEventListener('change', filtrar); filtrar();
    })();
</script>

@if($comparacion && $rendimiento && ($rendimiento['indice_a'] !== null || $rendimiento['indice_b'] !== null))
    <script defer src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
    (function () {
        const data = @json($rendimiento);
        const nombreA = @json($productoA->getNombre());
        const nombreB = @json($productoB->getNombre());
        const COLOR_A = '#1d1d1b', COLOR_B = '#f2c900';
        const COLOR_A_SOFT = 'rgba(29,29,27,.18)', COLOR_B_SOFT = 'rgba(242,201,0,.35)';

        // Espera a que Chart.js (cargado con defer) esté disponible antes de dibujar.
        function iniciar() {
            if (typeof Chart === 'undefined') { return setTimeout(iniciar, 50); }

            Chart.defaults.font.family = "'Poppins','Segoe UI',system-ui,sans-serif";
            // Los contenedores .cmp-chart definen la altura; el canvas la respeta.
            Chart.defaults.responsive = true;
            Chart.defaults.maintainAspectRatio = false;

        // Barras horizontales: indice global
        new Chart(document.getElementById('chartGlobal'), {
            type: 'bar',
            data: {
                labels: [nombreA, nombreB],
                datasets: [{
                    label: 'Índice',
                    data: [data.indice_a ?? 0, data.indice_b ?? 0],
                    backgroundColor: [COLOR_A, COLOR_B],
                    borderRadius: 8,
                    barThickness: 42,
                }]
            },
            options: {
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, max: 100, ticks: { callback: v => v } } },
            }
        });

        // Radar por caracteristica
        const ejes = data.ejes || [];
        if (ejes.length >= 3) {
            new Chart(document.getElementById('chartRadar'), {
                type: 'radar',
                data: {
                    labels: ejes.map(e => e.etiqueta),
                    datasets: [
                        { label: nombreA, data: ejes.map(e => e.a), borderColor: COLOR_A, backgroundColor: COLOR_A_SOFT, borderWidth: 2, pointBackgroundColor: COLOR_A },
                        { label: nombreB, data: ejes.map(e => e.b), borderColor: COLOR_B, backgroundColor: COLOR_B_SOFT, borderWidth: 2, pointBackgroundColor: COLOR_B },
                    ]
                },
                options: {
                    plugins: { legend: { position: 'bottom' } },
                    scales: { r: { min: 0, max: 100, ticks: { stepSize: 25, backdropColor: 'transparent' }, pointLabels: { font: { size: 10 } } } },
                }
            });
        } else {
            document.getElementById('chartRadar').insertAdjacentHTML('afterend',
                '<p class="text-muted small mb-0">No hay suficientes características numéricas para el radar.</p>');
            document.getElementById('chartRadar').remove();
        }

        // Barras verticales por caracteristica
        if (ejes.length) {
            new Chart(document.getElementById('chartBarras'), {
                type: 'bar',
                data: {
                    labels: ejes.map(e => e.etiqueta),
                    datasets: [
                        { label: nombreA, data: ejes.map(e => e.a), backgroundColor: COLOR_A, borderRadius: 5 },
                        { label: nombreB, data: ejes.map(e => e.b), backgroundColor: COLOR_B, borderRadius: 5 },
                    ]
                },
                options: {
                    plugins: { legend: { position: 'bottom' } },
                    scales: { y: { beginAtZero: true, max: 100 } },
                }
            });
        }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', iniciar);
        } else {
            iniciar();
        }
    })();
    </script>
@endif
@endsection
