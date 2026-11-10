{{-- Autor: Kevin --}}
@extends('layouts.app')

@section('content')
<div class="container">

    <div class="cmp-head">
        <h1 class="mb-1">{{ __('messages.builder_title') }}</h1>
        <p class="text-muted mb-0">{{ __('messages.builder_subtitle') }}</p>
    </div>

    <div class="row g-4">
        {{-- ======= Columna de slots ======= --}}
        <div class="col-lg-8">
            <div class="pcb-slots">
                @foreach($slots as $slug => $slot)
                    <div class="pcb-slot" data-slot="{{ $slug }}">
                        <div class="pcb-slot-ico">{!! $catIcon($slug, 30) !!}</div>
                        <div class="pcb-slot-main">
                            <div class="pcb-slot-label">
                                {{ __('messages.slot_'.$slug) }}
                                @if($slot['requerido'])<span class="pcb-req">{{ __('messages.required') }}</span>@endif
                            </div>
                            <select class="form-select pcb-select" data-slot="{{ $slug }}">
                                <option value="">{{ __('messages.none_selected') }}</option>
                                @foreach($slot['productos'] as $p)
                                    <option value="{{ $p->getId() }}"
                                            data-precio="{{ $p->getPrecio() }}"
                                            data-vendible="{{ $p->esVendible() ? 1 : 0 }}"
                                            data-stock="{{ $p->getStock() }}">
                                        {{ $p->getNombre() }}
                                        @if($p->esVendible()) — ${{ number_format($p->getPrecio(), 2) }}@endif
                                    </option>
                                @endforeach
                            </select>
                            <div class="pcb-slot-note" data-note="{{ $slug }}"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ======= Panel resumen (sticky) ======= --}}
        <div class="col-lg-4">
            <div class="pcb-summary">
                <div class="pcb-summary-head">
                    <span id="pcb-status-badge" class="badge bg-secondary">{{ __('messages.choose_components') }}</span>
                </div>

                <div class="pcb-gauge">
                    <div class="pcb-gauge-label">{{ __('messages.build_compatibility') }}</div>
                    <div id="pcb-status-text" class="pcb-gauge-text text-muted">{{ __('messages.add_two_related') }}</div>
                </div>

                <div id="pcb-consumo" class="pcb-consumo" hidden>
                    <div class="pcb-consumo-head">{{ __('messages.consumption_estimated') }}</div>
                    <div class="pcb-consumo-bar"><div id="pcb-consumo-fill"></div></div>
                    <div id="pcb-consumo-text" class="small text-muted"></div>
                </div>

                <div class="pcb-total-row">
                    <span>{{ __('messages.total_sellable') }}</span>
                    <span id="pcb-total" class="pcb-total">$0.00</span>
                </div>
                <div id="pcb-total-note" class="small text-muted mb-3"></div>

                <form action="{{ route('builder.add-to-cart') }}" method="POST" id="pcb-cart-form">
                    @csrf
                    {{-- Inputs ocultos: se rellenan por JS con los ids elegidos --}}
                    @foreach($slots as $slug => $slot)
                        <input type="hidden" name="{{ $slug }}" id="pcb-input-{{ $slug }}">
                    @endforeach
                    <button type="submit" id="pcb-add-btn" class="btn btn-primary w-100" disabled>
                        {{ __('messages.add_all_to_cart') }}
                    </button>
                </form>
                <p class="small text-muted mt-2 mb-0">{{ __('messages.builder_note') }}</p>
            </div>
        </div>
    </div>

    {{-- ======= Detalle de compatibilidad par a par ======= --}}
    <h2 class="h5 mt-5 mb-3">{{ __('messages.compatibility_check') }}</h2>
    <div id="pcb-pairs-empty" class="text-muted">{{ __('messages.select_to_check') }}</div>
    <div class="table-responsive">
        <table class="table table-bordered bg-white" id="pcb-pairs-table" hidden>
            <thead class="table-light">
                <tr><th>{{ __('messages.component_a') }}</th><th>{{ __('messages.component_b') }}</th><th>{{ __('messages.status') }}</th><th>{{ __('messages.detail') }}</th></tr>
            </thead>
            <tbody id="pcb-pairs-body"></tbody>
        </table>
    </div>
</div>

<script>
(function () {
    const EVAL_URL = @json(route('builder.evaluate'));
    const TOKEN = document.querySelector('meta[name="csrf-token"]').content;
    const selects = Array.from(document.querySelectorAll('.pcb-select'));

    // Traducciones inyectadas desde el backend (LANG) para el contenido dinamico.
    @php($tBuilder = [
        'build_ok' => __('messages.build_ok'),
        'build_warn' => __('messages.build_warn'),
        'build_bad' => __('messages.build_bad'),
        'badge_compatible' => __('messages.badge_compatible'),
        'badge_warn' => __('messages.badge_warn'),
        'badge_bad' => __('messages.badge_bad'),
        'choose' => __('messages.choose_components'),
        'add_two' => __('messages.add_two_related'),
        'missing' => __('messages.missing'),
        'buyable_suffix' => __('messages.components_buyable', ['total' => '__TOTAL__']),
        'add_all' => __('messages.add_all_to_cart'),
        'add_anyway' => __('messages.add_anyway'),
        'compat' => __('messages.compat_ok'),
        'warn' => __('messages.compat_warn'),
        'bad' => __('messages.compat_bad'),
        'na' => __('messages.compat_na'),
        'ref_only' => __('messages.ref_only'),
        'no_stock' => __('messages.no_stock_note'),
        'available' => __('messages.available'),
        'in_stock' => __('messages.in_stock'),
    ])
    const T = @json($tBuilder);

    const badge = document.getElementById('pcb-status-badge');
    const statusText = document.getElementById('pcb-status-text');
    const totalEl = document.getElementById('pcb-total');
    const totalNote = document.getElementById('pcb-total-note');
    const addBtn = document.getElementById('pcb-add-btn');
    const pairsTable = document.getElementById('pcb-pairs-table');
    const pairsBody = document.getElementById('pcb-pairs-body');
    const pairsEmpty = document.getElementById('pcb-pairs-empty');
    const consumoBox = document.getElementById('pcb-consumo');
    const consumoFill = document.getElementById('pcb-consumo-fill');
    const consumoText = document.getElementById('pcb-consumo-text');

    const money = n => '$' + Number(n).toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    const ESTADO = {
        ok:   { badge: 'bg-success',       texto: T.build_ok,   clase: 'text-success' },
        warn: { badge: 'bg-warning text-dark', texto: T.build_warn, clase: 'text-warning' },
        bad:  { badge: 'bg-danger',        texto: T.build_bad,  clase: 'text-danger' },
    };

    function seleccionActual() {
        const ids = {};
        selects.forEach(s => { ids[s.dataset.slot] = s.value || null; });
        return ids;
    }

    function pintarNotasLocales() {
        // Nota por slot: avisa si el componente elegido es solo referencia / sin stock.
        selects.forEach(s => {
            const note = document.querySelector('[data-note="' + s.dataset.slot + '"]');
            const opt = s.selectedOptions[0];
            if (!opt || !opt.value) { note.textContent = ''; note.className = 'pcb-slot-note'; return; }
            const vendible = opt.dataset.vendible === '1';
            const stock = parseInt(opt.dataset.stock || '0', 10);
            if (!vendible) { note.textContent = T.ref_only; note.className = 'pcb-slot-note warn'; }
            else if (stock <= 0) { note.textContent = T.no_stock; note.className = 'pcb-slot-note warn'; }
            else { note.textContent = T.available + ' · ' + stock + ' ' + T.in_stock; note.className = 'pcb-slot-note ok'; }
        });
    }

    function render(data) {
        // Inputs ocultos para el POST
        Object.keys(data.componentes || {}).forEach(() => {});
        selects.forEach(s => {
            const input = document.getElementById('pcb-input-' + s.dataset.slot);
            if (input) input.value = s.value || '';
        });

        // Estado global
        const seleccionados = data.cantidad_total || 0;
        if (seleccionados === 0) {
            badge.className = 'badge bg-secondary';
            badge.textContent = T.choose;
            statusText.className = 'pcb-gauge-text text-muted';
            statusText.textContent = T.add_two;
        } else {
            const e = ESTADO[data.estado_global] || ESTADO.ok;
            badge.className = 'badge ' + e.badge;
            badge.textContent = data.estado_global === 'ok' ? T.badge_compatible : (data.estado_global === 'warn' ? T.badge_warn : T.badge_bad);
            statusText.className = 'pcb-gauge-text ' + e.clase;
            let msg = e.texto;
            if (data.faltantes && data.faltantes.length) {
                msg += ' · ' + T.missing + ': ' + data.faltantes.join(', ');
            }
            statusText.textContent = msg;
        }

        // Total
        totalEl.textContent = money(data.total || 0);
        if (data.cantidad_vendible < data.cantidad_total) {
            totalNote.textContent = data.cantidad_vendible + ' ' + T.buyable_suffix.replace('__TOTAL__', data.cantidad_total);
        } else {
            totalNote.textContent = '';
        }

        // Consumo
        if (data.consumo) {
            consumoBox.hidden = false;
            const pct = Math.min(100, Math.round(data.consumo.estimado_w / data.consumo.fuente_w * 100));
            consumoFill.style.width = pct + '%';
            consumoFill.className = 'pcb-fill-' + data.consumo.estado;
            consumoText.textContent = data.consumo.mensaje;
        } else {
            consumoBox.hidden = true;
        }

        // Pares de compatibilidad
        pairsBody.innerHTML = '';
        if (data.pares && data.pares.length) {
            pairsEmpty.hidden = true;
            pairsTable.hidden = false;
            data.pares.forEach(p => {
                const tr = document.createElement('tr');
                let badgeHtml;
                if (p.estado === 'warn') badgeHtml = '<span class="badge bg-warning text-dark">' + T.warn + '</span>';
                else if (p.estado === 'na') badgeHtml = '<span class="badge bg-secondary">' + T.na + '</span>';
                else if (p.compatible) badgeHtml = '<span class="badge bg-success">' + T.compat + '</span>';
                else badgeHtml = '<span class="badge bg-danger">' + T.bad + '</span>';
                tr.innerHTML =
                    '<td>' + p.producto_a + '</td>' +
                    '<td>' + p.producto_b + '</td>' +
                    '<td>' + badgeHtml + '</td>' +
                    '<td class="small">' + p.mensaje + '</td>';
                pairsBody.appendChild(tr);
            });
        } else {
            pairsEmpty.hidden = false;
            pairsTable.hidden = true;
        }

        // Boton: habilitado si hay al menos un componente comprable y no hay incompatibilidad grave
        const comprables = data.cantidad_vendible > 0;
        const bloqueado = data.estado_global === 'bad';
        addBtn.disabled = !comprables;
        addBtn.classList.toggle('btn-danger', bloqueado && comprables);
        addBtn.classList.toggle('btn-primary', !(bloqueado && comprables));
        addBtn.textContent = bloqueado && comprables ? T.add_anyway : T.add_all;
    }

    let pendiente = null;
    function evaluar() {
        pintarNotasLocales();
        const ids = seleccionActual();
        const seleccionados = Object.values(ids).filter(Boolean).length;

        // Siempre refrescamos inputs ocultos
        selects.forEach(s => {
            const input = document.getElementById('pcb-input-' + s.dataset.slot);
            if (input) input.value = s.value || '';
        });

        if (seleccionados === 0) {
            render({ cantidad_total: 0, cantidad_vendible: 0, total: 0, pares: [], consumo: null, faltantes: [] });
            return;
        }

        if (pendiente) pendiente.abort();
        pendiente = new AbortController();

        fetch(EVAL_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': TOKEN, 'Accept': 'application/json' },
            body: JSON.stringify(ids),
            signal: pendiente.signal,
        })
        .then(r => r.json())
        .then(render)
        .catch(err => { if (err.name !== 'AbortError') console.error(err); });
    }

    selects.forEach(s => s.addEventListener('change', evaluar));
    evaluar();
})();
</script>
@endsection
