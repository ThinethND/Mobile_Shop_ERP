<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $superAdmin = User::where('email', 'admin@admin.com')->first();

        $roles = [
            ['name' => 'Super Admin', 'dashboard_type' => 'super-admin'],
            ['name' => 'System Admin', 'dashboard_type' => 'system-admin'],
            ['name' => 'Owner', 'dashboard_type' => 'owner'],
            ['name' => 'Manger', 'dashboard_type' => 'manager'],
            ['name' => 'Staff', 'dashboard_type' => 'staff'],
        ];

        foreach ($roles as $role) {
            $has_roles = Role::where(['name' => $role['name']])->first();
            if (!isset($has_roles)) {
                Role::create(['name' => $role['name'], 'dashboard_type' => $role['dashboard_type']]);
            }
        }

        $superAdmin->syncRoles('Super Admin');


        $permission_array = [
            ['section_name' => 'dashboard', 'name' => 'dashboard.view'],

            ['section_name' => 'user', 'name' => 'user.view'],
            ['section_name' => 'user', 'name' => 'user.create'],
            ['section_name' => 'user', 'name' => 'user.edit'],
            ['section_name' => 'user', 'name' => 'user.delete'],
            ['section_name' => 'user', 'name' => 'user.order'],

            ['section_name' => 'roles-permissions', 'name' => 'roles-permissions.view'],
            ['section_name' => 'roles-permissions', 'name' => 'roles-permissions.create'],
            ['section_name' => 'roles-permissions', 'name' => 'roles-permissions.edit'],
            ['section_name' => 'roles-permissions', 'name' => 'roles-permissions.delete'],

            ['section_name' => 'branch', 'name' => 'branch.view'],
            ['section_name' => 'branch', 'name' => 'branch.create'],
            ['section_name' => 'branch', 'name' => 'branch.edit'],
            ['section_name' => 'branch', 'name' => 'branch.delete'],

            ['section_name' => 'calendar', 'name' => 'calendar.view'],
            ['section_name' => 'calendar', 'name' => 'calendar.staff'],
            ['section_name' => 'calendar', 'name' => 'calendar.AddBooking'],
            ['section_name' => 'calendar', 'name' => 'calendar.AddBlocktime'],
            // ['section_name' => 'calendar', 'name' => 'calendar.ContineCheckout'],


            ['section_name' => 'bookingpreview', 'name' => 'bookingpreview.statusChange'],
            ['section_name' => 'bookingpreview', 'name' => 'bookingpreview.ContineCheckout'],
          

            ['section_name' => 'service', 'name' => 'service.view'],
            ['section_name' => 'service', 'name' => 'service.create'],
            ['section_name' => 'service', 'name' => 'service.edit'],
            ['section_name' => 'service', 'name' => 'service.delete'],

            ['section_name' => 'clients', 'name' => 'clients.view'],
            ['section_name' => 'clients', 'name' => 'clients.create'],
            ['section_name' => 'clients', 'name' => 'clients.edit'],
            ['section_name' => 'clients', 'name' => 'clients.delete'],

            ['section_name' => 'discounts', 'name' => 'discounts.view'],
            ['section_name' => 'discounts', 'name' => 'discounts.create'],
            ['section_name' => 'discounts', 'name' => 'discounts.edit'],
            ['section_name' => 'discounts', 'name' => 'discounts.delete'],


            ['section_name' => 'banner', 'name' => 'banner.view'],
            ['section_name' => 'banner', 'name' => 'banner.create'],
            ['section_name' => 'banner', 'name' => 'banner.edit'],
            ['section_name' => 'banner', 'name' => 'banner.delete'],

            ['section_name' => 'reports', 'name' => 'reports.view'],
            ['section_name' => 'reports', 'name' => 'reports.create'],
            ['section_name' => 'reports', 'name' => 'reports.edit'],
            ['section_name' => 'reports', 'name' => 'reports.delete'],

            ['section_name' => 'sms-gateway', 'name' => 'sms-gateway.view'],
            ['section_name' => 'sms-gateway', 'name' => 'sms-gateway.create'],
            ['section_name' => 'sms-gateway', 'name' => 'sms-gateway.edit'],
            ['section_name' => 'sms-gateway', 'name' => 'sms-gateway.delete'],
            ['section_name' => 'sms-gateway', 'name' => 'sms-gateway.test'],

            ['section_name' => 'loyalty-program', 'name' => 'loyalty-program.view'],
            ['section_name' => 'loyalty-program', 'name' => 'loyalty-program.create'],
            ['section_name' => 'loyalty-program', 'name' => 'loyalty-program.edit'],
            ['section_name' => 'loyalty-program', 'name' => 'loyalty-program.delete'],

              ['section_name' => 'bookings', 'name' => 'bookings.view'],

               ['section_name' => 'waitlist', 'name' => 'waitlist.view'],
            ['section_name' => 'waitlist', 'name' => 'waitlist.accept'],
            ['section_name' => 'waitlist', 'name' => 'waitlist.edit&view'],
            ['section_name' => 'waitlist', 'name' => 'waitlist.close'],

            ['section_name' => 'sales', 'name' => 'sales.edit'],

        ];

        foreach ($permission_array as $permission) {

            $check_has_permission = Permission::where('name', $permission['name'])->first();
            if (!isset($check_has_permission)) {
                Permission::create($permission);
            }
        }
       
$superAdminRole = Role::where('name', 'Super Admin')->first();
if ($superAdminRole) {
    $superAdminRole->givePermissionTo('sales.edit');
}

    }
}
