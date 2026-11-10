<?php
// Autor: Kevin (armador de PC)
//
// "Arma tu PC": nueva pestaña que permite elegir cada componente, validar su
// compatibilidad en vivo y agregar todo el armado al carrito de una sola vez.
// Exclusivo de usuarios (igual que el carrito y el comparador).

namespace App\Http\Controllers;

use App\Services\PcBuilderService;
use Illuminate\Http\Request;

class PcBuilderController extends Controller
{
    public function __construct(
        private readonly PcBuilderService $builder,
    ) {}

    // Pagina del armador con las opciones de cada slot.
    public function index()
    {
        return view('builder.index', [
            'slots' => $this->builder->opciones(),
        ]);
    }

    // Evaluacion en vivo (AJAX): recibe los ids elegidos por slot y devuelve
    // compatibilidad, consumo y total en JSON.
    public function evaluate(Request $request)
    {
        $ids = $this->idsValidados($request);
        $seleccion = $this->builder->cargarSeleccion($ids);
        $resultado = $this->builder->evaluar($seleccion);

        // Serializamos la seleccion para que la vista refresque precios/estado.
        $componentes = [];
        foreach ($seleccion as $slug => $producto) {
            $componentes[$slug] = [
                'id' => $producto->getId(),
                'nombre' => $producto->getNombre(),
                'precio' => $producto->getPrecio(),
                'vendible' => $producto->esVendible(),
                'disponible' => $producto->estaDisponible(),
                'stock' => $producto->getStock(),
            ];
        }

        return response()->json([
            'componentes' => $componentes,
            'pares' => $resultado['pares'],
            'consumo' => $resultado['consumo'],
            'total' => $resultado['total'],
            'estado_global' => $resultado['estado_global'],
            'faltantes' => $resultado['faltantes'],
            'cantidad_vendible' => $resultado['cantidad_vendible'],
            'cantidad_total' => $resultado['cantidad_total'],
        ]);
    }

    // Agrega todos los componentes seleccionados al carrito de una vez.
    // Si algun componente no tiene stock o no es vendible, se omite y se
    // informa; el resto se agrega igualmente.
    public function addToCart(Request $request)
    {
        $ids = $this->idsValidados($request);
        $seleccion = $this->builder->cargarSeleccion($ids);

        if ($seleccion === []) {
            return back()->with('error', 'No seleccionaste ningún componente.');
        }

        $carrito = $request->user()->carritoActivo();
        $agregados = [];
        $omitidos = [];

        foreach ($seleccion as $producto) {
            if ($carrito->agregarProducto($producto, 1)) {
                $agregados[] = $producto->getNombre();
            } else {
                $omitidos[] = $producto->getNombre();
            }
        }

        if ($agregados === []) {
            return back()->with('error', 'Ningún componente pudo agregarse al carrito (sin stock o solo de referencia).');
        }

        $mensaje = count($agregados).' componente(s) agregados al carrito.';

        if ($omitidos !== []) {
            $mensaje .= ' No se pudieron agregar: '.implode(', ', $omitidos).' (sin stock o solo de referencia).';
        }

        return redirect()->route('cart.index')->with(
            $omitidos === [] ? 'success' : 'error',
            $mensaje
        );
    }

    // Valida que cada id enviado exista en products (uno por slot).
    private function idsValidados(Request $request): array
    {
        $reglas = [];
        foreach (array_keys(PcBuilderService::SLOTS) as $slug) {
            $reglas[$slug] = ['nullable', 'integer', 'exists:products,id'];
        }

        return $request->validate($reglas);
    }
}
