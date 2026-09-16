<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $componentes = Category::create([
            'nombre' => 'Componentes de PC',
            'descripcion' => 'Piezas para armar o actualizar un computador de escritorio.',
        ]);

        $electronicos = Category::create([
            'nombre' => 'Dispositivos electronicos',
            'descripcion' => 'Equipos electronicos de uso personal y del hogar.',
        ]);

        $telefonos = Category::create([
            'nombre' => 'Telefonos',
            'descripcion' => 'Smartphones de distintas marcas y gamas.',
        ]);

        $televisores = Category::create([
            'nombre' => 'Televisores',
            'descripcion' => 'Televisores y pantallas para el hogar.',
        ]);

        $listaComponentes = [
            ['nombre' => 'Procesador Intel Core i5-13400F', 'precio' => 189.99, 'stock' => 25],
            ['nombre' => 'Procesador AMD Ryzen 5 7600', 'precio' => 219.99, 'stock' => 20],
            ['nombre' => 'Tarjeta madre ASUS B760M-Plus', 'precio' => 139.99, 'stock' => 15],
            ['nombre' => 'Tarjeta madre Gigabyte B650M', 'precio' => 129.99, 'stock' => 18],
            ['nombre' => 'Memoria RAM Corsair Vengeance 16GB DDR4', 'precio' => 49.99, 'stock' => 40],
            ['nombre' => 'Memoria RAM Kingston Fury 32GB DDR5', 'precio' => 99.99, 'stock' => 30],
            ['nombre' => 'Tarjeta grafica NVIDIA RTX 4060', 'precio' => 299.99, 'stock' => 12],
            ['nombre' => 'Tarjeta grafica AMD RX 7600', 'precio' => 269.99, 'stock' => 10],
            ['nombre' => 'Disco SSD Samsung 970 EVO 1TB NVMe', 'precio' => 79.99, 'stock' => 35],
            ['nombre' => 'Disco SSD Kingston A400 480GB SATA', 'precio' => 34.99, 'stock' => 45],
            ['nombre' => 'Fuente de poder EVGA 650W 80+ Bronze', 'precio' => 69.99, 'stock' => 22],
            ['nombre' => 'Fuente de poder Corsair RM750x', 'precio' => 109.99, 'stock' => 15],
            ['nombre' => 'Gabinete NZXT H510', 'precio' => 79.99, 'stock' => 20],
            ['nombre' => 'Gabinete Cooler Master MasterBox Q300L', 'precio' => 54.99, 'stock' => 18],
            ['nombre' => 'Disipador de CPU Cooler Master Hyper 212', 'precio' => 39.99, 'stock' => 30],
            ['nombre' => 'Procesador Intel Core i7-14700K', 'precio' => 409.99, 'stock' => 15],
            ['nombre' => 'Procesador AMD Ryzen 7 7800X3D', 'precio' => 449.99, 'stock' => 12],
            ['nombre' => 'Tarjeta madre MSI PRO B650-P', 'precio' => 159.99, 'stock' => 20],
            ['nombre' => 'Tarjeta madre ASUS ROG Strix Z790-E', 'precio' => 329.99, 'stock' => 8],
            ['nombre' => 'Memoria RAM G.Skill Trident Z5 32GB DDR5', 'precio' => 129.99, 'stock' => 25],
            ['nombre' => 'Memoria RAM Crucial 16GB DDR4', 'precio' => 39.99, 'stock' => 40],
            ['nombre' => 'Tarjeta grafica NVIDIA RTX 4070', 'precio' => 549.99, 'stock' => 10],
            ['nombre' => 'Tarjeta grafica AMD RX 7800 XT', 'precio' => 499.99, 'stock' => 9],
            ['nombre' => 'Disco SSD WD Black SN850X 2TB NVMe', 'precio' => 149.99, 'stock' => 20],
            ['nombre' => 'Disco duro Seagate Barracuda 2TB HDD', 'precio' => 54.99, 'stock' => 30],
            ['nombre' => 'Fuente de poder Thermaltake Toughpower 850W', 'precio' => 129.99, 'stock' => 14],
            ['nombre' => 'Fuente de poder Cooler Master MWE 550W', 'precio' => 59.99, 'stock' => 25],
            ['nombre' => 'Gabinete Lian Li Lancool 216', 'precio' => 109.99, 'stock' => 12],
            ['nombre' => 'Monitor gamer ASUS TUF 27" 165Hz', 'precio' => 259.99, 'stock' => 15],
            ['nombre' => 'Teclado mecanico Logitech G413', 'precio' => 79.99, 'stock' => 30],
        ];

        foreach ($listaComponentes as $item) {
            Product::create($item + ['category_id' => $componentes->id]);
        }

        $listaElectronicos = [
            ['nombre' => 'Smartphone Samsung Galaxy A54', 'precio' => 399.99, 'stock' => 20],
            ['nombre' => 'Smartphone iPhone 13', 'precio' => 699.99, 'stock' => 15],
            ['nombre' => 'Laptop Lenovo IdeaPad 3', 'precio' => 549.99, 'stock' => 12],
            ['nombre' => 'Laptop HP Pavilion 15', 'precio' => 599.99, 'stock' => 10],
            ['nombre' => 'Audifonos Sony WH-1000XM4', 'precio' => 279.99, 'stock' => 25],
            ['nombre' => 'Audifonos JBL Tune 510BT', 'precio' => 39.99, 'stock' => 50],
            ['nombre' => 'Smartwatch Apple Watch SE', 'precio' => 249.99, 'stock' => 18],
            ['nombre' => 'Smartwatch Xiaomi Mi Band 8', 'precio' => 34.99, 'stock' => 60],
            ['nombre' => 'Tablet Samsung Galaxy Tab A9', 'precio' => 179.99, 'stock' => 22],
            ['nombre' => 'Parlante Bluetooth JBL Flip 6', 'precio' => 99.99, 'stock' => 30],
            ['nombre' => 'Television LG 55" 4K UHD', 'precio' => 449.99, 'stock' => 8],
            ['nombre' => 'Consola de videojuegos PlayStation 5', 'precio' => 499.99, 'stock' => 6],
            ['nombre' => 'Camara GoPro Hero 11', 'precio' => 349.99, 'stock' => 10],
            ['nombre' => 'Impresora HP DeskJet 2720', 'precio' => 79.99, 'stock' => 15],
            ['nombre' => 'Router TP-Link Archer AX21', 'precio' => 69.99, 'stock' => 25],
        ];

        foreach ($listaElectronicos as $item) {
            Product::create($item + ['category_id' => $electronicos->id]);
        }

        $listaTelefonos = [
            ['nombre' => 'Smartphone Google Pixel 8', 'precio' => 599.99, 'stock' => 12],
            ['nombre' => 'Smartphone Motorola Edge 50', 'precio' => 449.99, 'stock' => 18],
            ['nombre' => 'Smartphone Xiaomi Redmi Note 13 Pro', 'precio' => 299.99, 'stock' => 25],
        ];

        foreach ($listaTelefonos as $item) {
            Product::create($item + ['category_id' => $telefonos->id]);
        }

        $listaTelevisores = [
            ['nombre' => 'Televisor Samsung 43" QLED 4K', 'precio' => 349.99, 'stock' => 10],
            ['nombre' => 'Televisor LG 50" NanoCell 4K', 'precio' => 429.99, 'stock' => 8],
            ['nombre' => 'Televisor Sony 55" Bravia OLED', 'precio' => 899.99, 'stock' => 5],
            ['nombre' => 'Televisor TCL 65" 4K UHD', 'precio' => 549.99, 'stock' => 6],
            ['nombre' => 'Televisor Hisense 32" HD Smart TV', 'precio' => 179.99, 'stock' => 15],
        ];

        foreach ($listaTelevisores as $item) {
            Product::create($item + ['category_id' => $televisores->id]);
        }
    }
}
