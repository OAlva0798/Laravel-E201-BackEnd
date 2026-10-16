<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Users Account
        |--------------------------------------------------------------------------
        */
        Schema::create('users_account', function (Blueprint $table) {
            $table->id();

            // Account identification
            $table->string('uacc_reference')->unique();
            $table->string('uacc_email')->unique();

            // Email verification
            $table->timestamp('uacc_email_verified_at')->nullable();

            // Authentication
            $table->string('uacc_password');

            // Account type
            $table->enum('uacc_type', [
                'Super Admin',
                'Admin',
                'User',
            ])
            ->default('User')
            ->index();

            // Account status
            $table->enum('uacc_status', [
                'Active',
                'Inactive',
                'Locked',
                'Suspended',
                'Deleted',
            ])
            ->default('Active')
            ->index();

            // Login security
            $table->unsignedInteger('uacc_failed_login_attempts')
                ->default(0);

            $table->timestamp('uacc_locked_at')->nullable();
            $table->timestamp('uacc_locked_until')->nullable();

            // Login information
            $table->timestamp('uacc_last_login_at')->nullable();
            $table->string('uacc_last_login_ip', 45)->nullable();

            // Password security
            $table->timestamp('uacc_password_changed_at')->nullable();
            $table->timestamp('uacc_password_expires_at')->nullable();

            // Two-factor authentication
            $table->boolean('uacc_two_factor_enabled')
                ->default(false);

            $table->text('uacc_two_factor_secret')->nullable();

            $table->timestamp('uacc_two_factor_confirmed_at')
                ->nullable();

            // Remember token
            $table->rememberToken();

            // Timestamps
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Password Reset Tokens
        |--------------------------------------------------------------------------
        */
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('uacc_email')->primary();
            $table->string('uacc_token');
            $table->timestamp('uacc_created_at')->nullable();
        });

        /*
        |--------------------------------------------------------------------------
        | Sessions
        |--------------------------------------------------------------------------
        |
        | uacc_type is included so the session record can retain the account
        | type associated with the authenticated session.
        |
        */
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('uacc_session_id')->primary();

            $table->unsignedBigInteger('uacc_id')
                ->nullable()
                ->index();

            // Account type stored with session
            $table->enum('uacc_type', [
                'Super Admin',
                'Admin',
                'User',
            ])
            ->nullable()
            ->index();

            $table->string('uacc_ip_address', 45)->nullable();

            $table->text('uacc_user_agent')->nullable();

            $table->longText('uacc_payload');

            $table->integer('uacc_last_activity')
                ->index();

            $table->foreign('uacc_id')
                ->references('id')
                ->on('users_account')
                ->nullOnDelete();
        });

        /*
        |--------------------------------------------------------------------------
        | Login Attempt Logs
        |--------------------------------------------------------------------------
        */
        Schema::create('user_account_login_logs', function (Blueprint $table) {
            $table->id();

            // Account
            $table->unsignedBigInteger('uacc_id')
                ->nullable()
                ->index();

            $table->string('uacc_reference')
                ->nullable()
                ->index();

            $table->string('uacc_email')
                ->nullable()
                ->index();

            // Account type
            $table->enum('uacc_type', [
                'Super Admin',
                'Admin',
                'User',
            ])
            ->nullable()
            ->index();

            // Login result
            $table->enum('uacc_login_status', [
                'Success',
                'Failed',
                'Locked',
                'Blocked',
                'Logout',
            ])
            ->index();

            // Failure information
            $table->string('uacc_failure_reason')
                ->nullable();

            // Request information
            $table->string('uacc_ip_address', 45)
                ->nullable();

            $table->text('uacc_user_agent')
                ->nullable();

            // Device information
            $table->string('uacc_device')
                ->nullable();

            $table->string('uacc_browser')
                ->nullable();

            $table->string('uacc_platform')
                ->nullable();

            // Session
            $table->string('uacc_session_id')
                ->nullable()
                ->index();

            // Log timestamp
            $table->timestamp('uacc_logged_at')
                ->useCurrent();

            $table->timestamps();

            $table->foreign('uacc_id')
                ->references('id')
                ->on('users_account')
                ->nullOnDelete();
        });

        /*
        |--------------------------------------------------------------------------
        | Security / Account Audit Logs
        |--------------------------------------------------------------------------
        */
        Schema::create('user_account_security_logs', function (Blueprint $table) {
            $table->id();

            // Account
            $table->unsignedBigInteger('uacc_id')
                ->nullable()
                ->index();

            $table->string('uacc_reference')
                ->nullable()
                ->index();

            // Account type
            $table->enum('uacc_type', [
                'Super Admin',
                'Admin',
                'User',
            ])
            ->nullable()
            ->index();

            // Security action
            $table->enum('uacc_action', [
                'Password Changed',
                'Password Reset',
                'Email Changed',
                'Account Locked',
                'Account Unlocked',
                'Account Suspended',
                'Account Activated',
                '2FA Enabled',
                '2FA Disabled',
                '2FA Verified',
                'Session Revoked',
                'Security Settings Changed',
            ])
            ->index();

            $table->text('uacc_description')
                ->nullable();

            // Request information
            $table->string('uacc_ip_address', 45)
                ->nullable();

            $table->text('uacc_user_agent')
                ->nullable();

            // Action timestamp
            $table->timestamp('uacc_performed_at')
                ->useCurrent();

            $table->timestamps();

            $table->foreign('uacc_id')
                ->references('id')
                ->on('users_account')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('user_account_security_logs');
        Schema::dropIfExists('user_account_login_logs');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users_account');

        Schema::enableForeignKeyConstraints();
    }
};