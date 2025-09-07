<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Programa;

class ProgramaSeeder extends Seeder
{
    public function run(): void
    {
        Programa::create([
            'nome' => 'Programa A',
            'descricao' => 'Primeira oportunidade de teste',
            'data_inicio' => now()->subDays(5),
            'data_final' => now()->addDays(10),
            'estado' => 'ativo'
        ]);

        Programa::create([
            'nome' => 'Programa B',
            'descricao' => 'Programa expirado para validação de regras',
            'data_inicio' => now()->subDays(20),
            'data_final' => now()->subDays(5),
            'estado' => 'inativo'
        ]);
        
    }
}