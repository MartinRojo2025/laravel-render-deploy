<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
</head>
<body>
    <h1>Formulario de Edición de Producto</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('product.update', $product->id) }}" method="POST">
        @csrf
        @method('PUT')

        <label>Nombre:</label>
        <input type="text" name="name" value="{{ old('name', $product->name) }}" class="@error('name') is-invalid @enderror">
        @error('name')
            <small class="error-message">{{ $message }}</small>
        @enderror

        <label>Descripción:</label>
        <textarea name="description" class="@error('description') is-invalid @enderror">{{ old('description', $product->description) }}</textarea>
        @error('description')
            <small class="error-message">{{ $message }}</small>
        @enderror

        <label>Precio:</label>
        <input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" class="@error('price') is-invalid @enderror">
        @error('price')
            <small class="error-message">{{ $message }}</small>
        @enderror

        <label>Stock:</label>
        <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" class="@error('stock') is-invalid @enderror">
        @error('stock')
            <small class="error-message">{{ $message }}</small>
        @enderror

        <button type="submit" class="btn">Actualizar Producto</button>
    </form>

    <br>
    <a href="{{ route('product.index') }}">Cancelar y Volver</a>
</body>
</html>
