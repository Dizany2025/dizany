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
