<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
</head>
<body>
    <h1>Listado de Productos</h1>
    
    @include('_partials.messages')
    
    <a href="{{ route('product.create') }}" class="btn">Crear Nuevo Producto</a>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
                <tr>
                    <td>{{ $product->id }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->description }}</td>
                    <td>{{ $product->price }} €</td>
                    <td>{{ $product->stock }}</td>
                    <td>
                        <a href="{{ route('product.show', $product->id) }}">Ver</a> | 
                        <a href="{{ route('product.edit', $product->id) }}">Editar</a> | 
                        
                        <form action="{{ route('product.destroy', $product->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="empty-message">No hay productos disponibles</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
