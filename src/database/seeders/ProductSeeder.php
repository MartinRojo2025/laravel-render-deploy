<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::create([
            'name' => 'Laptop Dell XPS 15',
            'description' => 'Portátil de alto rendimiento con pantalla 4K',
            'price' => 1299.99,
            'stock' => 15
        ]);

        Product::create([
            'name' => 'Mouse Logitech MX Master 3',
            'description' => 'Mouse inalámbrico ergonómico para productividad',
            'price' => 89.99,
            'stock' => 50
        ]);

        Product::create([
            'name' => 'Teclado Mecánico Keychron K2',
            'description' => 'Teclado mecánico compacto con switches Cherry MX',
            'price' => 79.50,
            'stock' => 30
        ]);

        Product::create([
            'name' => 'Monitor LG UltraWide 34"',
            'description' => 'Monitor curvo ultrawide 3440x1440',
            'price' => 549.00,
            'stock' => 8
        ]);

        Product::create([
            'name' => 'Auriculares Sony WH-1000XM5',
            'description' => 'Auriculares con cancelación de ruido premium',
            'price' => 349.99,
            'stock' => 25
        ]);

        Product::create([
            'name' => 'Webcam Logitech C920',
            'description' => 'Cámara web Full HD 1080p',
            'price' => 69.99,
            'stock' => 40
        ]);

        Product::create([
            'name' => 'SSD Samsung 1TB',
            'description' => 'Disco sólido NVMe M.2 de alta velocidad',
            'price' => 129.99,
            'stock' => 100
        ]);
    }
}
