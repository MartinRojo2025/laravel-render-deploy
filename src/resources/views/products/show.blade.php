<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Producto</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
</head>
<body>
    <h1>Vista de Detalles del Producto</h1>

    <p><strong>ID:</strong> {{ $product->id }}</p>
    <p><strong>Nombre:</strong> {{ $product->name }}</p>
    <p><strong>Descripción:</strong> {{ $product->description }}</p>
    <p><strong>Precio:</strong> {{ $product->price }} €</p>
    <p><strong>Stock:</strong> {{ $product->stock }}</p>

    <br>
    <a href="{{ route('product.index') }}">Volver al listado</a>
</body>
</html>
