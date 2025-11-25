<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsuariosExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return User::select('id', 'nombre', 'usuario', 'rol_id', 'created_at')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nombre',
            'Usuario',
            'Rol',
            'Fecha de Registro',
        ];
    }
}
