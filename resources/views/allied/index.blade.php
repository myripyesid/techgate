{{-- Consumo del servicio web del equipo precedente (entregable #2). --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="cmp-head">
        <h1 class="mb-1">{{ __('messages.allied_title') }}</h1>
        <p class="text-muted mb-0">{{ __('messages.allied_subtitle') }}</p>
    </div>

    <p class="small text-muted">{{ __('messages.allied_source') }}: <code>{{ $fuente }}</code></p>

    @if(! $disponible)
        <div class="alert alert-warning">{{ __('messages.allied_unavailable') }}</div>
    @elseif(empty($productos))
        <div class="alert alert-secondary">{{ __('messages.allied_empty') }}</div>
    @else
        <div class="table-responsive">
            <table class="table table-bordered bg-white align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>{{ __('messages.product') }}</th>
                        <th>{{ __('messages.category') }}</th>
                        <th class="text-end">{{ __('messages.price') }}</th>
                        <th class="text-end">{{ __('messages.stock') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productos as $p)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $p['nombre'] ?? '—' }}</div>
                                @if(! empty($p['marca']))<div class="text-muted small">{{ $p['marca'] }}</div>@endif
                            </td>
                            <td>{{ $p['categoria'] ?? '—' }}</td>
                            <td class="text-end">
                                @isset($p['precio'])${{ number_format((float) $p['precio'], 2) }}@else—@endisset
                            </td>
                            <td class="text-end">{{ $p['stock'] ?? '—' }}</td>
                            <td class="text-end">
                                @if(! empty($p['url']))
                                    <a href="{{ $p['url'] }}" target="_blank" rel="noopener"
                                       class="btn btn-sm btn-outline-primary">{{ __('messages.allied_view') }}</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
