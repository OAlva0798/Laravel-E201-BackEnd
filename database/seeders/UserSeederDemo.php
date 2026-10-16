<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeederDemo extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Users Account
        $users = [
            [
                'uacc_reference' => 'UACC029B77',
                'uacc_email' => 'ntmawebcontrol@ntmacad.edu.ph',
                'uacc_email_verified_at' => '2026-09-24 17:33:24',
                'uacc_password' => '$2y$12$WfA9vDUvcM4vXIsf9d.dX./p6FHLEbBHm/ePwlouzwI3qQxYYEn8m',
                'uacc_type' => 'Super Admin',
                'uacc_status' => 'Active',
            ],
        ];

        $users = array_filter(
            $users,
            fn ($user) => !empty($user['uacc_email'])
        );

        DB::table('users_account')->insert(array_values($users));

        // 2. Seed User Details
        DB::table('user_details')->insert([
            [
                'ud_uniqid' => 'UACC029B77',
                'ud_avatar' => null,
                'ud_firstname' => 'System',
                'ud_middlename' => '',
                'ud_lastname' => 'Administrator',
                'ud_email' => 'ntmawebcontrol@ntmacad.edu.ph',
                'ud_center_head' => 'No',
                'ud_center_reference' => 'MC2026-00005',
                'ud_department_reference' => 'MD2026-00012',
                'ud_position_reference' => 'MP2026-00040',
                'ud_status' => 'Active',
                'ud_is_approver' => 'No',
                'ud_esign' => null,
                'ud_created_at' => now(),
                'ud_updated_at' => null,
            ],
        ]);
    }
}