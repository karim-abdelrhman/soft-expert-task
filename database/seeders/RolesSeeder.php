<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('roles')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $roles = ['manager', 'user'];
        foreach($roles as $role) {
            DB::table('roles')->insert([
                'name' => $role,
                'guard_name' => 'web',
            ]);
        }


        $permissions =  DB::table('permissions')->get();
        //assign permissions to manager role
        foreach ($permissions as $permission) {
            DB::table('role_has_permissions')->updateOrInsert(
                [
                    'role_id' => 1,
                    'permission_id' => $permission->id
                ]
            );
        }
    }
}
