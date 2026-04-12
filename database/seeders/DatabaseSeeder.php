<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(DocumentoSeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call(AlmacenSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(TipoUnidadSeeder::class);
        $this->call(CategoriaSeeder::class);
        $this->call(MarcaSeeder::class);
        $this->call(ComprobanteSeeder::class);
        $this->call(GrupoClienteSeeder::class);
        $this->call(ClienteSeeder::class);
    }
}
