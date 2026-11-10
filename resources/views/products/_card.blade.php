{{-- Tarjeta de producto reutilizable (catalogo y secciones por categoria) --}}
<article class="tg-card">
    @if(! $product->esVendible())
        <span class="tg-tag">{{ __('messages.reference') }}</span>
    @elseif(! $product->estaDisponible())
        <span class="tg-tag out">{{ __('messages.out_of_stock') }}</span>
    @endif

    <a href="{{ route('products.show', $product) }}" class="thumb">
        {!! $catIcon($product->getCategorySlug(), 64) !!}
    </a>

    <div class="body">
        <span class="brand">{{ $product->getMarca() ?? 'TechGate' }}</span>
        <a href="{{ route('products.show', $product) }}" class="name">{{ $product->getNombre() }}</a>
        <span class="cat">{{ $product->getNombreCategoria() }}</span>

        @if($product->esVendible())
            <div class="tg-price">${{ number_format($product->getPrecio(), 2) }}
                <small>· {{ __('messages.stock') }} {{ $product->getStock() }}</small>
            </div>
        @else
            <div class="tg-price text-muted" style="font-size:1rem">{{ __('messages.price_tbd') }}</div>
        @endif

        <div class="actions">
            @if(auth()->user()->esUsuario())
                <form action="{{ route('cart.add', $product) }}" method="POST" class="flex-fill d-flex">
                    @csrf
                    <input type="hidden" name="cantidad" value="1">
                    <button type="submit" class="btn btn-add w-100"
                            @disabled(! $product->estaDisponible())>{{ __('messages.add') }}</button>
                </form>
                <form action="{{ route('comparator.add', $product) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-cmp" title="{{ __('messages.compare') }}">{{ __('messages.compare') }}</button>
                </form>
            @endif

            @if(auth()->user()->esAdministrador())
                <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-secondary flex-fill">{{ __('messages.edit') }}</a>
                <form action="{{ route('products.destroy', $product) }}" method="POST"
                      onsubmit="return confirm('{{ __('messages.confirm_delete_product') }}')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">{{ __('messages.delete') }}</button>
                </form>
            @endif
        </div>
    </div>
</article>
