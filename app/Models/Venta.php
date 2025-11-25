<?php
namespace App\Models;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $table = 'ventas';
    public $timestamps = false;

    protected $fillable = [
        'cliente_id',
        'usuario_id',
        'fecha',
        'tipo_comprobante',
        'metodo_pago',
        'total',
        'estado',
        'estado_sunat',     // NUEVO
        'serie',            // opcional
        'correlativo',      // opcional
        'hash',             // opcional
        'xml_url',          // opcional
        'pdf_url',          // opcional
        'cdr_url'           // opcional
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function detalleVentas()
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function calcularTotalNuevo()
    {
        return $this->detalleVentas->sum(function ($detalle) {
            $precio = $detalle->precio_mayor && $detalle->precio_mayor > 0
                ? $detalle->precio_mayor
                : $detalle->precio_unitario;
            return $precio * $detalle->cantidad;
        });
    }

    public function calcularGanancia()
    {
        return $this->detalleVentas->sum('ganancia');
    }

    public function calcularSubtotal()
    {
        return $this->detalleVentas->sum('subtotal');
    }

    public function estaActiva()
    {
        return $this->estado === 'activa';
    }

    public function estaAnulada()
    {
        return $this->estado === 'anulada';
    }
}
