<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 5px; text-align: left; }
        th { background-color: #f2f2f2; }
        h3 { margin-bottom: 5px; }
    </style>
</head>
<body>
    <h3>Reporte de Ventas Filtradas</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Cliente</th>
                <th>Tipo</th>
                <th>Total</th>
                <th>Fecha</th>
                <th>Usuario</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @php $totalGeneral = 0; @endphp
            @foreach($ventas as $venta)
                <tr>
                    <td>{{ $venta->id }}</td>
                    <td>{{ $venta->cliente->nombre ?? 'Sin cliente' }}</td>
                    <td>{{ ucfirst($venta->tipo) }}</td>
                    <td>S/ {{ number_format($venta->total, 2) }}</td>
                    <td>{{ $venta->fecha }}</td>
                    <td>{{ $venta->usuario->name ?? 'Sin usuario' }}</td>
                    <td>{{ ucfirst($venta->estado) }}</td>
                </tr>
                @php $totalGeneral += $venta->total; @endphp
            @endforeach

            <!-- Fila de total -->
            <tr>
                <td colspan="3"><strong>Total General</strong></td>
                <td><strong>S/ {{ number_format($totalGeneral, 2) }}</strong></td>
                <td colspan="3"></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
