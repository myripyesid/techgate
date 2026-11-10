<?php
// Reglas de compatibilidad entre productos del catalogo fusionado
// (componentes de PC y dispositivos electronicos).
//
// El nucleo (evaluarEspecificaciones) trabaja solo con arreglos, sin base de
// datos, para poder probarlo de forma aislada. evaluar() lo adapta a modelos.
//
// Cada resultado es un arreglo:
//   estado     ok | warn | bad | na | ninguna
//   mensaje    explicacion en espanol
//   compatible true cuando el estado es ok, warn o na

namespace App\Services;

use App\Models\Product;

class CompatibilityService
{
    public const GRUPO_PC = ['cpu', 'chipset', 'ram', 'gpu', 'almacenamiento', 'fuente', 'refrigeracion', 'gabinete'];

    // Evalua dos productos del catalogo.
    public function evaluar(Product $a, Product $b): array
    {
        $slugA = $a->getCategorySlug();
        $slugB = $b->getCategorySlug();

        // Categorias sin slug (creadas a mano por el administrador): se
        // mantiene la regla historica, compatibles si comparten categoria.
        if ($slugA === null || $slugB === null) {
            $igual = $a->getCategoryId() === $b->getCategoryId();

            return $this->resultado(
                $igual ? 'ok' : 'ninguna',
                $igual ? 'Pertenecen a la misma categoría.' : 'Los productos pertenecen a categorías distintas.'
            );
        }

        return $this->evaluarEspecificaciones($slugA, $a->getEspecificaciones(), $slugB, $b->getEspecificaciones());
    }

    public function evaluarEspecificaciones(string $slugA, array $a, string $slugB, array $b): array
    {
        if ($slugA === $slugB) {
            return $this->resultado('bad', 'Ambos productos cumplen la misma función (son del mismo tipo), por lo que no tiene sentido usarlos juntos.');
        }

        $reglas = $this->reglas();

        if (isset($reglas["$slugA|$slugB"])) {
            return $reglas["$slugA|$slugB"]($a, $b);
        }

        if (isset($reglas["$slugB|$slugA"])) {
            return $reglas["$slugB|$slugA"]($b, $a);
        }

        if (in_array($slugA, self::GRUPO_PC, true) && in_array($slugB, self::GRUPO_PC, true)) {
            return $this->resultado('ok', 'No hay restricciones técnicas directas entre estas dos categorías.');
        }

        return $this->resultado('ninguna', 'Los productos pertenecen a categorías sin relación de compatibilidad.');
    }

    // Estima el consumo del equipo (procesador + gráfica + ~100 W del resto)
    // y lo contrasta con la fuente. Devuelve null si falta alguno de los tres.
    public function estimarConsumo(?array $cpu, ?array $gpu, ?array $fuente): ?array
    {
        if (! $cpu || ! $gpu || ! $fuente) {
            return null;
        }

        $estimado = (int) ($cpu['potencia_max_w'] ?? 0) + (int) ($gpu['tdp_w'] ?? 0) + 100;
        $potencia = (int) ($fuente['potencia_w'] ?? 0);
        $estado = $estimado <= $potencia * 0.8 ? 'ok' : ($estimado <= $potencia ? 'warn' : 'bad');

        return [
            'estado' => $estado,
            'estimado_w' => $estimado,
            'fuente_w' => $potencia,
            'mensaje' => match ($estado) {
                'ok' => "Consumo estimado de ~{$estimado} W; la fuente de {$potencia} W tiene margen cómodo (uso menor al 80 %).",
                'warn' => "Consumo estimado de ~{$estimado} W; la fuente de {$potencia} W queda con margen ajustado.",
                default => "Consumo estimado de ~{$estimado} W; la fuente de {$potencia} W es insuficiente.",
            },
        ];
    }

    // Tabla de reglas: "categoriaA|categoriaB" => funcion(especA, especB).
    private function reglas(): array
    {
        return [
            'cpu|chipset' => fn ($cpu, $chip) => ($cpu['socket'] ?? null) === ($chip['socket'] ?? null)
                ? $this->resultado('ok', 'Mismo socket ('.$cpu['socket'].').')
                : $this->resultado('bad', 'Socket distinto: el procesador usa '.($cpu['socket'] ?? '?').' y la placa '.($chip['socket'] ?? '?').'.'),

            'cpu|ram' => function ($cpu, $ram) {
                $tipo = $ram['tipo'] ?? '';
                $maximo = $this->velocidadMaxima($cpu['memoria_soportada'] ?? '', $tipo);

                if ($maximo === 0) {
                    return $this->resultado('bad', "El procesador no soporta {$tipo} (".($cpu['memoria_soportada'] ?? 'sin dato').').');
                }

                return ($ram['velocidad_mts'] ?? 0) > $maximo
                    ? $this->resultado('warn', "Supera la velocidad oficial ({$tipo}-{$maximo}). Funciona con perfil EXPO/XMP, pero es overclock de memoria.")
                    : $this->resultado('ok', 'Dentro de la velocidad oficial del procesador.');
            },

            'chipset|ram' => function ($chip, $ram) {
                $tipo = $ram['tipo'] ?? '';
                $memoria = $chip['memoria'] ?? '';

                if (! str_contains($memoria, $tipo)) {
                    return $this->resultado('bad', "El chipset solo trabaja con {$memoria}.");
                }

                return str_contains($memoria, 'según')
                    ? $this->resultado('warn', 'Hay placas '.($chip['chipset'] ?? '').' DDR4 y DDR5: verifica que la placa sea de tipo '.$tipo.'.')
                    : $this->resultado('ok', $tipo.' compatible con el chipset.');
            },

            'gpu|fuente' => fn ($gpu, $psu) => ($psu['potencia_w'] ?? 0) >= ($gpu['psu_recomendada_w'] ?? 0)
                ? $this->resultado('ok', 'La fuente cumple los '.$gpu['psu_recomendada_w'].' W recomendados para la gráfica.')
                : $this->resultado('bad', 'Fuente de '.($psu['potencia_w'] ?? 0).' W; la gráfica recomienda al menos '.($gpu['psu_recomendada_w'] ?? 0).' W.'),

            'gpu|chipset' => function ($gpu, $chip) {
                $gen = $this->generacionPcie($gpu['interfaz_pcie'] ?? '');
                $max = (float) ($chip['pcie_gpu_max'] ?? 0);

                return $gen > $max
                    ? $this->resultado('warn', sprintf('La gráfica es PCIe %.1f y la placa llega a %.1f: funciona a %.1f con pérdida mínima.', $gen, $max, $max))
                    : $this->resultado('ok', 'Interfaz PCIe compatible ('.($gpu['interfaz_pcie'] ?? '').').');
            },

            'cpu|refrigeracion' => function ($cpu, $cooler) {
                if (! str_contains($cooler['sockets'] ?? '', $cpu['socket'] ?? '?')) {
                    return $this->resultado('bad', 'El cooler no soporta el socket '.($cpu['socket'] ?? '?').'.');
                }

                $tdp = $cooler['tdp_declarado_w'] ?? null;

                if ($tdp && ($cpu['potencia_max_w'] ?? 0) > $tdp) {
                    return $this->resultado('warn', 'El procesador puede llegar a '.$cpu['potencia_max_w']." W y el cooler declara {$tdp} W.");
                }

                return $this->resultado('ok', 'Soporta el socket '.$cpu['socket'].($tdp ? ' y cubre la potencia del procesador.' : ' (el fabricante no declara TDP).'));
            },

            'refrigeracion|gabinete' => function ($cooler, $caja) {
                if (! empty($cooler['altura_mm'])) {
                    return $cooler['altura_mm'] <= ($caja['cooler_max_mm'] ?? 0)
                        ? $this->resultado('ok', "Cabe: {$cooler['altura_mm']} mm de altura, máximo {$caja['cooler_max_mm']} mm.")
                        : $this->resultado('bad', "No cabe: el cooler mide {$cooler['altura_mm']} mm y el gabinete admite ".($caja['cooler_max_mm'] ?? 0).' mm.');
                }

                $radiador = (int) ($cooler['radiador_mm'] ?? 0);
                $maximo = (int) ($caja['radiador_max_top_mm'] ?? 0);

                return $radiador <= $maximo
                    ? $this->resultado('ok', "Radiador de {$radiador} mm cabe en el techo (máx. {$maximo} mm).")
                    : $this->resultado('bad', "Radiador de {$radiador} mm; el gabinete admite {$maximo} mm arriba.");
            },

            'almacenamiento|chipset' => function ($disco, $chip) {
                if (! str_contains($disco['interfaz'] ?? '', 'PCIe 5.0')) {
                    return $this->resultado('ok', 'Compatible ('.($disco['interfaz'] ?? '').').');
                }

                return in_array($chip['chipset'] ?? '', ['B650', 'A620', 'H810', 'Z790', 'B760'], true)
                    ? $this->resultado('warn', 'SSD PCIe 5.0: en este chipset depende de la placa y puede funcionar a PCIe 4.0.')
                    : $this->resultado('ok', 'Soporta PCIe 5.0 en M.2.');
            },

            'gpu|gabinete' => function ($gpu, $caja) {
                $largo = (int) ($gpu['longitud_mm'] ?? 0);
                $maximo = (int) ($caja['gpu_max_mm'] ?? 0);

                if ($largo === 0 || $maximo === 0) {
                    return $this->resultado('na', 'Falta la longitud de la gráfica o el límite del gabinete para comprobar el espacio.');
                }

                if ($largo > $maximo) {
                    return $this->resultado('bad', "No cabe: la gráfica mide {$largo} mm y el gabinete admite hasta {$maximo} mm.");
                }

                $holgura = $maximo - $largo;

                return $holgura < 20
                    ? $this->resultado('warn', "Cabe muy justo: {$largo} mm en un gabinete de {$maximo} mm (solo {$holgura} mm de holgura).")
                    : $this->resultado('ok', "Cabe: {$largo} mm de largo, el gabinete admite hasta {$maximo} mm.");
            },

            // Carriles PCIe del procesador frente a la interfaz de la grafica.
            // Si la grafica pide PCIe 5.0 pero la CPU solo expone PCIe 4.0 en
            // su linea de GPU, funciona pero con una generacion por debajo.
            'cpu|gpu' => function ($cpu, $gpu) {
                $genGpu = $this->generacionPcie($gpu['interfaz_pcie'] ?? '');
                $genCpu = $this->generacionPcie($cpu['pcie'] ?? '');

                if ($genGpu === 0.0 || $genCpu === 0.0) {
                    return $this->resultado('ok', 'Compatibles por ranura PCIe x16 estándar.');
                }

                if ($genGpu > $genCpu) {
                    return $this->resultado('warn', sprintf(
                        'La gráfica es PCIe %.1f y el procesador expone PCIe %.1f: funciona a PCIe %.1f (pérdida de rendimiento mínima).',
                        $genGpu, $genCpu, $genCpu
                    ));
                }

                return $this->resultado('ok', sprintf('Interfaz PCIe compatible (el procesador ofrece PCIe %.1f).', $genCpu));
            },

            // Potencia total estimada (CPU + GPU + ~100 W) frente a la fuente.
            // Cuando no hay grafica en el par se estima solo con el procesador.
            'cpu|fuente' => function ($cpu, $psu) {
                $consumoCpu = (int) ($cpu['potencia_max_w'] ?? 0);
                $potencia = (int) ($psu['potencia_w'] ?? 0);

                if ($consumoCpu === 0 || $potencia === 0) {
                    return $this->resultado('ok', 'Sin datos de potencia suficientes; se asume compatible.');
                }

                // Reserva para una grafica de gama media/alta y el resto del equipo.
                $estimadoSinGpu = $consumoCpu + 100;

                if ($estimadoSinGpu > $potencia) {
                    return $this->resultado('bad', "Solo el procesador (~{$consumoCpu} W) y el resto del equipo superan los {$potencia} W de la fuente.");
                }

                return $estimadoSinGpu > $potencia * 0.6
                    ? $this->resultado('warn', "El procesador consume ~{$consumoCpu} W; con una gráfica potente la fuente de {$potencia} W puede quedar justa.")
                    : $this->resultado('ok', "La fuente de {$potencia} W cubre con holgura el procesador (~{$consumoCpu} W).");
            },

            // Estandar de la fuente frente al segmento de la plataforma: las
            // placas y graficas nuevas (PCIe 5.x) se benefician de ATX 3.x.
            'chipset|fuente' => function ($chip, $psu) {
                $estandar = $psu['estandar'] ?? '';
                $esAtx3 = str_contains($estandar, 'ATX 3');
                $pcie5 = (float) ($chip['pcie_gpu_max'] ?? 0) >= 5.0;

                if ($pcie5 && ! $esAtx3) {
                    return $this->resultado('warn', 'La plataforma es PCIe 5.0; se recomienda una fuente ATX 3.x con conector 12V-2x6 para gráficas modernas.');
                }

                return $this->resultado('ok', $esAtx3
                    ? "Fuente {$estandar}: estándar moderno, ideal para esta plataforma."
                    : 'Fuente compatible con la placa.');
            },

            // SSD NVMe PCIe 5.0 frente a los carriles del procesador.
            'cpu|almacenamiento' => function ($cpu, $disco) {
                if (! str_contains($disco['interfaz'] ?? '', 'PCIe 5.0')) {
                    return $this->resultado('ok', 'Compatible ('.($disco['interfaz'] ?? 'interfaz estándar').').');
                }

                return str_contains($cpu['pcie'] ?? '', 'PCIe 5.0')
                    ? $this->resultado('ok', 'El procesador aporta carriles PCIe 5.0 para el SSD NVMe.')
                    : $this->resultado('warn', 'El SSD es PCIe 5.0, pero el procesador no declara carriles PCIe 5.0: funcionará a PCIe 4.0.');
            },

            'consola|televisor' => function ($consola, $tv) {
                $max4k = (int) ($consola['max_4k_hz'] ?? 0);
                $hzTv = (int) ($tv['refresco_hz'] ?? 0);
                $hdmi = (int) ($tv['puertos_hdmi_21'] ?? 0);

                if ($max4k >= 120 && $hzTv >= 120 && $hdmi > 0) {
                    return $this->resultado('ok', '4K a 120 Hz'.(! empty($tv['vrr']) ? ' con VRR' : '').': la consola y el televisor (HDMI 2.1) lo permiten.');
                }

                if ($max4k >= 120) {
                    return $this->resultado('warn', 'La consola admite 4K a 120 Hz, pero el televisor limita la señal (refresco '.$hzTv.' Hz, puertos HDMI 2.1: '.$hdmi.').');
                }

                if ($max4k >= 60) {
                    return $this->resultado('ok', 'Salida de 4K a 60 Hz'.(($consola['max_hz'] ?? 0) >= 120 && $hzTv >= 120 ? ' (120 Hz a menor resolución)' : '').'.');
                }

                return $this->resultado('ok', 'La consola entrega hasta 1080p; el televisor la muestra sin problema.');
            },
        ];
    }

    private function resultado(string $estado, string $mensaje): array
    {
        return [
            'estado' => $estado,
            'mensaje' => $mensaje,
            'compatible' => in_array($estado, ['ok', 'warn', 'na'], true),
        ];
    }

    // "DDR5-5600 / DDR4-3200" + "DDR4" => 3200. Devuelve 0 si no aparece el tipo.
    private function velocidadMaxima(string $memoria, string $tipo): int
    {
        return preg_match('/'.preg_quote($tipo, '/').'-(\d+)/', $memoria, $m) ? (int) $m[1] : 0;
    }

    private function generacionPcie(string $interfaz): float
    {
        return preg_match('/PCIe (\d\.\d)/', $interfaz, $m) ? (float) $m[1] : 0.0;
    }
}
