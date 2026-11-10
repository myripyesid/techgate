{{-- Autor: Marbin --}}
@extends('layouts.app')

@section('content')
<div class="container">

    @unless($filtrando)
        <section class="tg-hero">
            <div>
                <h1>{{ __('messages.hero_title') }}</h1>
                <p>{{ __('messages.hero_subtitle') }}</p>
            </div>
            <div class="big">
                <svg width="90" height="90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="13" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
            </div>
        </section>
    @endunless

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
        <h1 class="tg-title mb-0">
            @if(! $filtrando)
                {{ __('messages.explore_by_category') }}
            @elseif(request()->filled('buscar'))
                {{ __('messages.results_for') }} “{{ request('buscar') }}”
                <small class="text-muted fs-6 fw-normal">({{ $products->count() }})</small>
            @else
                {{ optional($categories->firstWhere('id', (int) request('category_id')))->getNombre() ?? __('messages.products') }}
                <small class="text-muted fs-6 fw-normal">({{ $products->count() }})</small>
            @endif
        </h1>

        @if(auth()->user()->esAdministrador())
            <a href="{{ route('products.create') }}" class="btn btn-primary">{{ __('messages.new_product') }}</a>
        @endif
    </div>

    <div class="tg-chips">
        <a class="tg-chip {{ request()->filled('category_id') ? '' : 'active' }}"
           href="{{ route('products.index', array_filter(['buscar' => request('buscar')])) }}">{{ __('messages.all') }}</a>
        @foreach($categories as $category)
            <a class="tg-chip {{ (int) request('category_id') === $category->getId() ? 'active' : '' }}"
               href="{{ route('products.index', array_filter(['buscar' => request('buscar'), 'category_id' => $category->getId()])) }}">
                {{ $category->getNombre() }}
            </a>
        @endforeach
    </div>

    @if(! $filtrando)
        {{-- Vista "Todo": 3 productos por categoria + boton "Ver más" --}}
        @foreach($secciones as $seccion)
            @php($categoria = $seccion['categoria'])
            <section class="tg-section">
                <div class="tg-section-head">
                    <h2>{{ __('messages.featured_in') }} <strong>{{ $categoria->getNombre() }}</strong></h2>
                    <a href="{{ route('products.index', ['category_id' => $categoria->getId()]) }}"
                       class="btn btn-sm btn-outline-secondary">{{ __('messages.see_more') }}</a>
                </div>
                <div class="tg-grid tg-grid-section">
                    @foreach($seccion['productos'] as $product)
                        @include('products._card', ['product' => $product])
                    @endforeach
                    <a class="tg-more" href="{{ route('products.index', ['category_id' => $categoria->getId()]) }}">
                        <span class="arrow">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </span>
                        <span>{{ __('messages.see_more_in') }}<br><strong>{{ $categoria->getNombre() }}</strong></span>
                    </a>
                </div>
            </section>
        @endforeach
    @elseif($products->isEmpty())
        <div class="text-center py-5">
            <div class="text-muted mb-2"><svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg></div>
            <p class="mb-3">{{ __('messages.no_results') }}</p>
            <a href="{{ route('products.index') }}" class="btn btn-primary">{{ __('messages.view_full_catalog') }}</a>
        </div>
    @else
        <div class="tg-grid">
            @foreach($products as $product)
                @include('products._card', ['product' => $product])
            @endforeach
        </div>
    @endif
</div>
@endsection
