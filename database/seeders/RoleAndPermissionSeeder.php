<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $roles = [
            'Super Admin',
            'Pharmacy Manager',
            'Cashier',
            'Doctor',
            'Reception',
            'Accountant',
            'Inventory Officer',
            'Laboratory',
            'Pharmacist',
            'Sales Person'
        ];

        foreach ($roles as $roleName) {
            // Need to generate UUID for each role as per our migration update
            Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'web'],
                ['uuid' => (string) Str::uuid()]
            );
        }
    }
}
