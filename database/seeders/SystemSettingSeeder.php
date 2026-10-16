<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [

            // ==========================================================
            // SITE INFO
            // ==========================================================
            [
                'ss_field' => 'site_logo',
                'ss_type'  => 'image',
                'ss_value' => 'files/setting_image_starter/Logo_20261007070847.png',
            ],
            [
                'ss_field' => 'site_corseal_img',
                'ss_type'  => 'image',
                'ss_value' => 'files/setting_image_starter/Firefly_RemoveBackground_20261006082050.png',
            ],
            [
                'ss_field' => 'site_loading_img',
                'ss_type'  => 'image',
                'ss_value' => 'files/setting_image_starter/loading_1_20261006020115.gif',
            ],
            [
                'ss_field' => 'site_name',
                'ss_type'  => 'text',
                'ss_value' => 'Laravel & React Template',
            ],
            [
                'ss_field' => 'site_short_name',
                'ss_type'  => 'text',
                'ss_value' => 'LRT',
            ],
            [
                'ss_field' => 'site_email',
                'ss_type'  => 'text',
                'ss_value' => null,
            ],
            [
                'ss_field' => 'site_contact',
                'ss_type'  => 'text',
                'ss_value' => '+63',
            ],
            [
                'ss_field' => 'site_address',
                'ss_type'  => 'text',
                'ss_value' => null,
            ],
            [
                'ss_field' => 'site_copyright',
                'ss_type'  => 'text',
                'ss_value' => null,
            ],
            [
                'ss_field' => 'site_mission',
                'ss_type'  => 'quill',
                'ss_value' => null,
            ],
            [
                'ss_field' => 'site_vision',
                'ss_type'  => 'quill',
                'ss_value' => null,
            ],
            [
                'ss_field' => 'site_data_privacy',
                'ss_type'  => 'quill',
                'ss_value' => null,
            ],

            // ==========================================================
            // LOGIN
            // ==========================================================
            [
                'ss_field' => 'login_background',
                'ss_type'  => 'image',
                'ss_value' => 'files/setting_image_starter/pngtree-product-presentation-mockup-background-featuring-3d-studio-and-podium-on-floor-image_3651559_20261001102014.jpg',
            ],
            [
                'ss_field' => 'login_enable_recaptcha',
                'ss_type'  => 'switch',
                'ss_value' => '1',
            ],
            [
                'ss_field' => 'login_recaptcha_site_key',
                'ss_type'  => 'text',
                'ss_value' => '6Le4q5wtAAAAAL2jbijpemWKt6IbdeHmy0J72Fsm',
            ],
            [
                'ss_field' => 'login_enable_registration',
                'ss_type'  => 'switch',
                'ss_value' => '1',
            ],
            [
                'ss_field' => 'login_enable_forgot_password',
                'ss_type'  => 'switch',
                'ss_value' => '1',
            ],
            [
                'ss_field' => 'login_enable_otp',
                'ss_type'  => 'switch',
                'ss_value' => null,
            ],
            [
                'ss_field' => 'login_remember_me',
                'ss_type'  => 'switch',
                'ss_value' => '1',
            ],
            [
                'ss_field' => 'login_otp_length',
                'ss_type'  => 'text',
                'ss_value' => '6',
            ],
            [
                'ss_field' => 'login_otp_expiry_minutes',
                'ss_type'  => 'text',
                'ss_value' => '5',
            ],
            [
                'ss_field' => 'login_otp_resend_seconds',
                'ss_type'  => 'text',
                'ss_value' => '60',
            ],
            [
                'ss_field' => 'login_otp_max_attempts',
                'ss_type'  => 'text',
                'ss_value' => '5',
            ],
            [
                'ss_field' => 'login_max_attempts',
                'ss_type'  => 'text',
                'ss_value' => '5',
            ],
            [
                'ss_field' => 'login_lockout_minutes',
                'ss_type'  => 'text',
                'ss_value' => '15',
            ],

            // ==========================================================
            // SETUP MAINTENANCE
            // ==========================================================
            [
                'ss_field' => 'maintenance_timezone',
                'ss_type'  => 'select',
                'ss_value' => 'Asia/Manila',
            ],
            [
                'ss_field' => 'maintenance_date_format',
                'ss_type'  => 'select',
                'ss_value' => 'F d, Y',
            ],
            [
                'ss_field' => 'maintenance_time_format',
                'ss_type'  => 'select',
                'ss_value' => 'h:i A',
            ],
            [
                'ss_field' => 'maintenance_mode',
                'ss_type'  => 'switch',
                'ss_value' => null,
            ],
            [
                'ss_field' => 'maintenance_start_time',
                'ss_type'  => 'datetime-local',
                'ss_value' => null,
            ],
            [
                'ss_field' => 'maintenance_end_time',
                'ss_type'  => 'datetime-local',
                'ss_value' => null,
            ],
            [
                'ss_field' => 'maintenance_title',
                'ss_type'  => 'text',
                'ss_value' => null,
            ],
            [
                'ss_field' => 'maintenance_subject',
                'ss_type'  => 'text',
                'ss_value' => null,
            ],
            [
                'ss_field' => 'maintenance_message',
                'ss_type'  => 'quill',
                'ss_value' => null,
            ],
        ];

        foreach ($settings as $setting) {
            $record = SystemSetting::firstOrCreate(
                ['ss_field' => $setting['ss_field']],
                $setting
            );

            if (! $record->wasRecentlyCreated) {
                $record->update(['ss_type' => $setting['ss_type']]);
            }
        }
    }
}