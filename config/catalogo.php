<?php
// Definicion del catalogo fusionado (componentes de PC + dispositivos).
//
// 'categorias': slug => nombre, grupo, descripcion y especificaciones.
// Cada especificacion es:
//   [campo, etiqueta, unidad, direccion, peso, tipo, extra]
//   direccion: 1 = mas es mejor, -1 = menos es mejor, 0 = solo informativo
//   peso:      importancia al decidir "cual es mejor" (0 = no puntua)
//   tipo:      n = numerico, b = si/no, t = texto, r = ranking (usa extra['mapa'])
//   extra:     'mapa' => [valor => orden], 'misma_marca' => true (solo puntua
//              si ambos productos son de la misma marca)

$wifi = ['Wi-Fi 6' => 1, 'Wi-Fi 6E' => 2, 'Wi-Fi 7' => 3];
$cert = ['Bronze' => 1, 'Silver' => 2, 'Gold' => 3, 'Platinum' => 4, 'Titanium' => 5];

return [
    'categorias' => [
        // Componentes de PC
        'cpu' => ['nombre' => 'Procesadores', 'grupo' => 'pc', 'descripcion' => 'Procesadores AMD Ryzen e Intel Core para PC de escritorio.', 'specs' => [
            ['nucleos_totales', 'Núcleos', '', 1, 1.5, 'n', ['misma_marca' => true]],
            ['hilos', 'Hilos', '', 1, 1, 'n', ['misma_marca' => true]],
            ['boost_ghz', 'Frecuencia máxima', 'GHz', 1, 1, 'n'],
            ['cache_l3_mb', 'Caché L3', 'MB', 1, 1, 'n'],
            ['potencia_max_w', 'Potencia máxima', 'W', -1, .5, 'n'],
            ['base_ghz', 'Frecuencia base', 'GHz', 0, 0, 'n'],
            ['socket', 'Socket', '', 0, 0, 't'],
            ['arquitectura', 'Arquitectura', '', 0, 0, 't'],
            ['memoria_soportada', 'Memoria soportada', '', 0, 0, 't'],
            ['graficos_integrados', 'Gráficos integrados', '', 0, 0, 't'],
        ]],
        'gpu' => ['nombre' => 'Tarjetas gráficas', 'grupo' => 'pc', 'descripcion' => 'Tarjetas gráficas NVIDIA GeForce RTX, AMD Radeon RX e Intel Arc.', 'specs' => [
            ['vram_gb', 'Memoria de video', 'GB', 1, 1.5, 'n'],
            ['ancho_banda_gbs', 'Ancho de banda', 'GB/s', 1, 1.5, 'n'],
            ['shaders', 'Shaders / núcleos', '', 1, 1, 'n', ['misma_marca' => true]],
            ['rt_cores', 'Núcleos de ray tracing', '', 1, .5, 'n', ['misma_marca' => true]],
            ['tmus', 'Unidades de textura (TMUs)', '', 1, .5, 'n', ['misma_marca' => true]],
            ['rops', 'Unidades de rasterizado (ROPs)', '', 1, .5, 'n', ['misma_marca' => true]],
            ['cache_mb', 'Caché de la GPU', 'MB', 1, .5, 'n', ['misma_marca' => true]],
            ['tdp_w', 'Consumo (TDP)', 'W', -1, .5, 'n'],
            ['boost_mhz', 'Frecuencia boost', 'MHz', 0, 0, 'n'],
            ['memoria_gbps', 'Velocidad de memoria', 'Gbps', 0, 0, 'n'],
            ['psu_recomendada_w', 'Fuente recomendada', 'W', 0, 0, 'n'],
            ['longitud_mm', 'Longitud de la tarjeta', 'mm', 0, 0, 'n'],
            ['proceso_nm', 'Proceso de fabricación', 'nm', 0, 0, 'n'],
            ['vram_tipo', 'Tipo de memoria', '', 0, 0, 't'],
            ['interfaz_pcie', 'Interfaz PCIe', '', 0, 0, 't'],
            ['arquitectura', 'Arquitectura', '', 0, 0, 't'],
            ['conector_poder', 'Conector de alimentación', '', 0, 0, 't'],
        ]],
        'chipset' => ['nombre' => 'Chipsets (placas madre)', 'grupo' => 'pc', 'descripcion' => 'Chipsets de referencia: definen socket, memoria y PCIe de la placa madre. No se venden por separado.', 'specs' => [
            ['pcie_gpu_max', 'PCIe máximo para la GPU', '', 1, 1, 'n'],
            ['socket', 'Socket', '', 0, 0, 't'],
            ['segmento', 'Segmento', '', 0, 0, 't'],
            ['memoria', 'Memoria', '', 0, 0, 't'],
            ['oc_cpu', 'Overclock de CPU', '', 0, 0, 't'],
            ['oc_memoria', 'Overclock de memoria', '', 0, 0, 't'],
            ['notas', 'Notas', '', 0, 0, 't'],
        ]],
        'ram' => ['nombre' => 'Memoria RAM', 'grupo' => 'pc', 'descripcion' => 'Perfiles de memoria DDR4 y DDR5 de referencia. No se venden por separado.', 'specs' => [
            ['latencia_ns', 'Latencia real', 'ns', -1, 1.5, 'n'],
            ['velocidad_mts', 'Velocidad', 'MT/s', 1, 1, 'n'],
            ['capacidad_gb', 'Capacidad del kit', 'GB', 1, 1, 'n'],
            ['cl', 'Latencia CAS', 'CL', -1, .5, 'n'],
            ['voltaje_v', 'Voltaje', 'V', -1, .5, 'n'],
            ['tipo', 'Tipo', '', 0, 0, 't'],
            ['perfil', 'Perfil', '', 0, 0, 't'],
            ['kit', 'Kit', '', 0, 0, 't'],
            ['notas', 'Notas', '', 0, 0, 't'],
        ]],
        'almacenamiento' => ['nombre' => 'Almacenamiento', 'grupo' => 'pc', 'descripcion' => 'SSD NVMe, SSD SATA y discos duros.', 'specs' => [
            ['lectura_mbs', 'Lectura', 'MB/s', 1, 1.5, 'n'],
            ['escritura_mbs', 'Escritura', 'MB/s', 1, 1.5, 'n'],
            ['capacidad_gb', 'Capacidad', 'GB', 1, 1, 'n'],
            ['tipo', 'Tipo', '', 0, 0, 't'],
            ['interfaz', 'Interfaz', '', 0, 0, 't'],
            ['formato', 'Formato', '', 0, 0, 't'],
        ]],
        'fuente' => ['nombre' => 'Fuentes de poder', 'grupo' => 'pc', 'descripcion' => 'Fuentes de poder ATX con certificación 80 PLUS.', 'specs' => [
            ['potencia_w', 'Potencia', 'W', 1, 1.5, 'n'],
            ['certificacion', 'Certificación 80 PLUS', '', 1, 1, 'r', ['mapa' => $cert]],
            ['modularidad', 'Modularidad', '', 0, 0, 't'],
            ['estandar', 'Estándar', '', 0, 0, 't'],
        ]],
        'refrigeracion' => ['nombre' => 'Refrigeración', 'grupo' => 'pc', 'descripcion' => 'Coolers de aire y refrigeración líquida (AIO).', 'specs' => [
            ['tdp_declarado_w', 'TDP declarado', 'W', 1, 1, 'n'],
            ['altura_mm', 'Altura', 'mm', -1, .5, 'n'],
            ['tipo', 'Tipo', '', 0, 0, 't'],
            ['ventiladores', 'Ventiladores', '', 0, 0, 't'],
            ['radiador_mm', 'Radiador', 'mm', 0, 0, 'n'],
            ['sockets', 'Sockets', '', 0, 0, 't'],
        ]],
        'gabinete' => ['nombre' => 'Gabinetes', 'grupo' => 'pc', 'descripcion' => 'Gabinetes ATX / mATX / ITX.', 'specs' => [
            ['gpu_max_mm', 'Largo máximo de GPU', 'mm', 1, 1, 'n'],
            ['cooler_max_mm', 'Altura máxima del cooler', 'mm', 1, 1, 'n'],
            ['radiador_max_top_mm', 'Radiador máximo superior', 'mm', 1, 1, 'n'],
            ['formato', 'Formato', '', 0, 0, 't'],
            ['placas_soportadas', 'Placas soportadas', '', 0, 0, 't'],
        ]],

        // Dispositivos electronicos
        'celular' => ['nombre' => 'Celulares', 'grupo' => 'electronico', 'descripcion' => 'Smartphones Apple, Samsung y Google.', 'specs' => [
            ['pantalla_pulg', 'Pantalla', '″', 1, .5, 'n'],
            ['refresco_hz', 'Tasa de refresco', 'Hz', 1, 1, 'n'],
            ['ram_gb', 'RAM', 'GB', 1, 1, 'n'],
            ['almacenamiento_base_gb', 'Almacenamiento base', 'GB', 1, 1, 'n'],
            ['camara_principal_mp', 'Cámara principal', 'MP', 0, 0, 'n'],
            ['bateria_mah', 'Batería', 'mAh', 1, 1.5, 'n'],
            ['carga_cable_w', 'Carga por cable', 'W', 1, 1, 'n'],
            ['carga_inalambrica_w', 'Carga inalámbrica', 'W', 1, .5, 'n'],
            ['wifi', 'Wi-Fi', '', 1, .5, 'r', ['mapa' => $wifi]],
            ['peso_g', 'Peso', 'g', -1, .5, 'n'],
            ['procesador', 'Procesador', '', 0, 0, 't'],
            ['tipo_panel', 'Panel', '', 0, 0, 't'],
            ['resolucion', 'Resolución', '', 0, 0, 't'],
            ['sistema_operativo', 'Sistema operativo', '', 0, 0, 't'],
            ['resistencia_agua', 'Resistencia al agua', '', 0, 0, 't'],
        ]],
        'televisor' => ['nombre' => 'Televisores', 'grupo' => 'electronico', 'descripcion' => 'Televisores OLED, QD-OLED y Mini LED.', 'specs' => [
            ['pantalla_pulg', 'Tamaño', '″', 1, .5, 'n'],
            ['refresco_hz', 'Tasa de refresco', 'Hz', 1, 1.5, 'n'],
            ['puertos_hdmi_21', 'Puertos HDMI 2.1', '', 1, 1.5, 'n'],
            ['vrr', 'VRR', '', 1, 1, 'b'],
            ['dolby_vision', 'Dolby Vision', '', 1, .5, 'b'],
            ['hdr10_plus', 'HDR10+', '', 1, .5, 'b'],
            ['tipo_panel', 'Panel', '', 0, 0, 't'],
            ['resolucion', 'Resolución', '', 0, 0, 't'],
            ['sistema_operativo', 'Sistema operativo', '', 0, 0, 't'],
        ]],
        'consola' => ['nombre' => 'Consolas', 'grupo' => 'electronico', 'descripcion' => 'Consolas de videojuegos de sobremesa e híbridas.', 'specs' => [
            ['tflops', 'Potencia gráfica', 'TFLOPS', 1, 2, 'n'],
            ['ram_gb', 'RAM', 'GB', 1, 1, 'n'],
            ['almacenamiento_gb', 'Almacenamiento', 'GB', 1, 1, 'n'],
            ['max_hz', 'Salida máxima', 'Hz', 1, 1, 'n'],
            ['max_4k_hz', 'Salida 4K máxima', 'Hz', 1, 1, 'n'],
            ['lector_discos', 'Lector de discos', '', 0, 0, 'b'],
            ['portatil', 'Portátil', '', 0, 0, 'b'],
            ['bateria_mah', 'Batería', 'mAh', 0, 0, 'n'],
            ['cpu', 'Procesador', '', 0, 0, 't'],
            ['gpu', 'Gráficos', '', 0, 0, 't'],
            ['resolucion_max_salida', 'Resolución máxima', '', 0, 0, 't'],
            ['hdmi', 'HDMI', '', 0, 0, 't'],
        ]],
        'ventilador' => ['nombre' => 'Ventiladores', 'grupo' => 'electronico', 'descripcion' => 'Ventiladores de hogar (torre, mesa, pedestal).', 'specs' => [
            ['caudal_cfm', 'Caudal de aire', 'CFM', 1, 2, 'n'],
            ['ruido_db', 'Ruido (alta velocidad)', 'dB', -1, 1, 'n'],
            ['potencia_w', 'Potencia', 'W', -1, .5, 'n'],
            ['velocidades', 'Velocidades', '', 1, .5, 'n'],
            ['oscilacion_grados', 'Oscilación', '°', 1, .5, 'n'],
            ['control_remoto', 'Control remoto', '', 1, .5, 'b'],
            ['temporizador_h', 'Temporizador máx.', 'h', 1, .5, 'n'],
            ['tipo', 'Tipo', '', 0, 0, 't'],
            ['tamano_pulg', 'Tamaño', '″', 0, 0, 'n'],
        ]],
        'nevera' => ['nombre' => 'Neveras', 'grupo' => 'electronico', 'descripcion' => 'Neveras y refrigeradores.', 'specs' => [
            ['capacidad_litros', 'Capacidad', 'L', 1, 2, 'n'],
            ['no_frost', 'No frost', '', 1, 1, 'b'],
            ['compresor_inverter', 'Compresor inverter', '', 1, 1, 'b'],
            ['wifi', 'Wi-Fi', '', 1, .5, 'b'],
            ['ancho_in', 'Ancho', '″', 0, 0, 'n'],
            ['tipo', 'Tipo', '', 0, 0, 't'],
        ]],
        'microondas' => ['nombre' => 'Microondas', 'grupo' => 'electronico', 'descripcion' => 'Hornos microondas de encimera o empotrables.', 'specs' => [
            ['capacidad_litros', 'Capacidad', 'L', 1, 1, 'n'],
            ['potencia_salida_w', 'Potencia de cocción', 'W', 1, 2, 'n'],
            ['consumo_w', 'Consumo eléctrico', 'W', -1, .5, 'n'],
            ['plato_pulg', 'Plato giratorio', '″', 1, .5, 'n'],
            ['inverter', 'Tecnología inverter', '', 1, 1, 'b'],
            ['sensor', 'Sensor automático', '', 1, .5, 'b'],
            ['instalacion', 'Instalación', '', 0, 0, 't'],
            ['voltaje_v', 'Voltaje', 'V', 0, 0, 'n'],
        ]],
    ],
];
