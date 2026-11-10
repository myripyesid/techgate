<?php
// Reglas de compatibilidad del catalogo fusionado (sin base de datos).

namespace Tests\Unit;

use App\Services\CompatibilityService;
use Tests\TestCase;

class CompatibilityServiceTest extends TestCase
{
    private CompatibilityService $servicio;

    protected function setUp(): void
    {
        parent::setUp();
        $this->servicio = new CompatibilityService();
    }

    private function cpuAm5(): array
    {
        return ['socket' => 'AM5', 'memoria_soportada' => 'DDR5-5600', 'potencia_max_w' => 88];
    }

    public function test_cpu_y_chipset_con_mismo_socket_son_compatibles(): void
    {
        $r = $this->servicio->evaluarEspecificaciones('cpu', $this->cpuAm5(), 'chipset', ['socket' => 'AM5', 'chipset' => 'B650']);

        $this->assertSame('ok', $r['estado']);
        $this->assertTrue($r['compatible']);
    }

    public function test_cpu_y_chipset_con_distinto_socket_son_incompatibles(): void
    {
        $r = $this->servicio->evaluarEspecificaciones('chipset', ['socket' => 'LGA1851', 'chipset' => 'Z890'], 'cpu', $this->cpuAm5());

        $this->assertSame('bad', $r['estado']);
        $this->assertFalse($r['compatible']);
        $this->assertStringContainsString('Socket distinto', $r['mensaje']);
    }

    public function test_ram_ddr4_no_funciona_con_cpu_solo_ddr5(): void
    {
        $r = $this->servicio->evaluarEspecificaciones('cpu', $this->cpuAm5(), 'ram', ['tipo' => 'DDR4', 'velocidad_mts' => 3200]);

        $this->assertSame('bad', $r['estado']);
    }

    public function test_ram_por_encima_de_la_velocidad_oficial_da_advertencia(): void
    {
        $r = $this->servicio->evaluarEspecificaciones('ram', ['tipo' => 'DDR5', 'velocidad_mts' => 6000], 'cpu', $this->cpuAm5());

        $this->assertSame('warn', $r['estado']);
        $this->assertTrue($r['compatible']);
    }

    public function test_chipset_con_ddr4_o_ddr5_pide_verificar_la_placa(): void
    {
        $r = $this->servicio->evaluarEspecificaciones('chipset', ['chipset' => 'Z790', 'memoria' => 'DDR4 o DDR5 (según placa)'], 'ram', ['tipo' => 'DDR5']);

        $this->assertSame('warn', $r['estado']);
    }

    public function test_grafica_con_fuente_insuficiente_es_incompatible(): void
    {
        $r = $this->servicio->evaluarEspecificaciones('gpu', ['psu_recomendada_w' => 1000], 'fuente', ['potencia_w' => 850]);

        $this->assertSame('bad', $r['estado']);
    }

    public function test_grafica_pcie5_en_placa_pcie4_da_advertencia(): void
    {
        $r = $this->servicio->evaluarEspecificaciones('gpu', ['interfaz_pcie' => 'PCIe 5.0 x16'], 'chipset', ['pcie_gpu_max' => '4.0']);

        $this->assertSame('warn', $r['estado']);
    }

    public function test_cooler_de_aire_debe_caber_en_el_gabinete(): void
    {
        $cooler = ['altura_mm' => 168, 'sockets' => 'AM5, LGA1700', 'tipo' => 'Aire'];

        $this->assertSame('ok', $this->servicio->evaluarEspecificaciones('refrigeracion', $cooler, 'gabinete', ['cooler_max_mm' => 170])['estado']);
        $this->assertSame('bad', $this->servicio->evaluarEspecificaciones('refrigeracion', $cooler, 'gabinete', ['cooler_max_mm' => 165])['estado']);
    }

    public function test_cpu_y_cooler_verifican_socket(): void
    {
        $r = $this->servicio->evaluarEspecificaciones('cpu', ['socket' => 'LGA1851', 'potencia_max_w' => 250], 'refrigeracion', ['sockets' => 'AM5, LGA1700']);

        $this->assertSame('bad', $r['estado']);
    }

    public function test_consola_4k120_con_tv_hdmi21_a_120hz(): void
    {
        $ps5 = ['max_4k_hz' => 120, 'max_hz' => 120];
        $tv = ['refresco_hz' => 120, 'puertos_hdmi_21' => 4, 'vrr' => 1];

        $r = $this->servicio->evaluarEspecificaciones('consola', $ps5, 'televisor', $tv);

        $this->assertSame('ok', $r['estado']);
        $this->assertStringContainsString('4K a 120 Hz', $r['mensaje']);
    }

    public function test_consola_4k120_con_tv_sin_hdmi21_advierte_limite(): void
    {
        $r = $this->servicio->evaluarEspecificaciones('consola', ['max_4k_hz' => 120], 'televisor', ['refresco_hz' => 60, 'puertos_hdmi_21' => 0]);

        $this->assertSame('warn', $r['estado']);
    }

    public function test_productos_del_mismo_tipo_nunca_son_compatibles(): void
    {
        $r = $this->servicio->evaluarEspecificaciones('cpu', [], 'cpu', []);

        $this->assertFalse($r['compatible']);
    }

    public function test_categorias_sin_relacion_no_son_compatibles(): void
    {
        $r = $this->servicio->evaluarEspecificaciones('celular', [], 'nevera', []);

        $this->assertSame('ninguna', $r['estado']);
        $this->assertFalse($r['compatible']);
    }

    public function test_componentes_de_pc_sin_regla_directa_no_tienen_restricciones(): void
    {
        $r = $this->servicio->evaluarEspecificaciones('cpu', $this->cpuAm5(), 'gpu', []);

        $this->assertSame('ok', $r['estado']);
    }

    public function test_estimacion_de_consumo_contra_la_fuente(): void
    {
        $cpu = ['potencia_max_w' => 250];
        $gpu = ['tdp_w' => 575];

        $this->assertSame('ok', $this->servicio->estimarConsumo($cpu, $gpu, ['potencia_w' => 1200])['estado']);
        $this->assertSame('warn', $this->servicio->estimarConsumo($cpu, $gpu, ['potencia_w' => 950])['estado']);
        $this->assertSame('bad', $this->servicio->estimarConsumo($cpu, $gpu, ['potencia_w' => 750])['estado']);
        $this->assertNull($this->servicio->estimarConsumo($cpu, null, ['potencia_w' => 750]));
    }

    // ---- Reglas ampliadas ----

    public function test_grafica_debe_caber_en_el_gabinete_por_longitud(): void
    {
        $cajaChica = ['gpu_max_mm' => 355];

        $this->assertSame('bad', $this->servicio->evaluarEspecificaciones('gpu', ['longitud_mm' => 400], 'gabinete', $cajaChica)['estado']);
        $this->assertSame('warn', $this->servicio->evaluarEspecificaciones('gpu', ['longitud_mm' => 345], 'gabinete', $cajaChica)['estado']);
        $this->assertSame('ok', $this->servicio->evaluarEspecificaciones('gpu', ['longitud_mm' => 300], 'gabinete', $cajaChica)['estado']);
    }

    public function test_grafica_sin_longitud_queda_sin_dato(): void
    {
        $r = $this->servicio->evaluarEspecificaciones('gpu', [], 'gabinete', ['gpu_max_mm' => 360]);

        $this->assertSame('na', $r['estado']);
        $this->assertTrue($r['compatible']);
    }

    public function test_grafica_pcie5_en_cpu_pcie4_da_advertencia(): void
    {
        $r = $this->servicio->evaluarEspecificaciones('cpu', ['pcie' => 'PCIe 4.0 x16'], 'gpu', ['interfaz_pcie' => 'PCIe 5.0 x16']);

        $this->assertSame('warn', $r['estado']);
        $this->assertTrue($r['compatible']);
    }

    public function test_cpu_y_gpu_con_misma_generacion_pcie_son_compatibles(): void
    {
        $r = $this->servicio->evaluarEspecificaciones('cpu', ['pcie' => 'PCIe 5.0 x16'], 'gpu', ['interfaz_pcie' => 'PCIe 5.0 x16']);

        $this->assertSame('ok', $r['estado']);
    }

    public function test_fuente_insuficiente_solo_para_el_procesador_es_incompatible(): void
    {
        $r = $this->servicio->evaluarEspecificaciones('cpu', ['potencia_max_w' => 700, 'pcie' => 'PCIe 5.0'], 'fuente', ['potencia_w' => 750]);

        $this->assertSame('bad', $r['estado']);
    }

    public function test_cpu_de_alto_consumo_con_fuente_justa_da_advertencia(): void
    {
        $r = $this->servicio->evaluarEspecificaciones('cpu', ['potencia_max_w' => 400, 'pcie' => 'PCIe 5.0'], 'fuente', ['potencia_w' => 750]);

        $this->assertSame('warn', $r['estado']);
    }

    public function test_plataforma_pcie5_recomienda_fuente_atx3(): void
    {
        $atx30 = $this->servicio->evaluarEspecificaciones('chipset', ['pcie_gpu_max' => '5.0'], 'fuente', ['potencia_w' => 850, 'estandar' => 'ATX 2.52']);
        $atx31 = $this->servicio->evaluarEspecificaciones('chipset', ['pcie_gpu_max' => '5.0'], 'fuente', ['potencia_w' => 850, 'estandar' => 'ATX 3.1 / PCIe 5.1']);

        $this->assertSame('warn', $atx30['estado']);
        $this->assertSame('ok', $atx31['estado']);
    }

    public function test_ssd_pcie5_en_cpu_sin_pcie5_funciona_a_pcie4(): void
    {
        $r = $this->servicio->evaluarEspecificaciones('cpu', ['pcie' => 'PCIe 4.0 x16'], 'almacenamiento', ['interfaz' => 'PCIe 5.0 x4']);

        $this->assertSame('warn', $r['estado']);
        $this->assertTrue($r['compatible']);
    }

    public function test_ssd_sata_es_compatible_con_cualquier_cpu(): void
    {
        $r = $this->servicio->evaluarEspecificaciones('cpu', ['pcie' => 'PCIe 4.0'], 'almacenamiento', ['interfaz' => 'SATA III']);

        $this->assertSame('ok', $r['estado']);
    }
}
