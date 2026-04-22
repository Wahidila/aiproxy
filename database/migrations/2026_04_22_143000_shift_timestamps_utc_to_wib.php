<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Shift all existing timestamps from UTC to WIB (UTC+7).
     *
     * This migration is needed because the application was previously storing
     * timestamps in UTC, but is now configured to use Asia/Jakarta (WIB, UTC+7).
     * Without this shift, all historical timestamps would appear 7 hours behind.
     */
    public function up(): void
    {
        // Disable foreign key checks to avoid constraint issues during bulk update
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // --- User-facing tables ---

        // token_usages (most important — usage history displayed on dashboard)
        DB::statement('UPDATE token_usages SET created_at = DATE_ADD(created_at, INTERVAL 7 HOUR) WHERE created_at IS NOT NULL');

        // wallet_transactions (transaction history)
        DB::statement('UPDATE wallet_transactions SET created_at = DATE_ADD(created_at, INTERVAL 7 HOUR) WHERE created_at IS NOT NULL');

        // api_keys
        DB::statement('UPDATE api_keys SET last_used_at = DATE_ADD(last_used_at, INTERVAL 7 HOUR) WHERE last_used_at IS NOT NULL');
        DB::statement('UPDATE api_keys SET created_at = DATE_ADD(created_at, INTERVAL 7 HOUR) WHERE created_at IS NOT NULL');
        DB::statement('UPDATE api_keys SET updated_at = DATE_ADD(updated_at, INTERVAL 7 HOUR) WHERE updated_at IS NOT NULL');

        // users
        DB::statement('UPDATE users SET created_at = DATE_ADD(created_at, INTERVAL 7 HOUR) WHERE created_at IS NOT NULL');
        DB::statement('UPDATE users SET updated_at = DATE_ADD(updated_at, INTERVAL 7 HOUR) WHERE updated_at IS NOT NULL');
        DB::statement('UPDATE users SET email_verified_at = DATE_ADD(email_verified_at, INTERVAL 7 HOUR) WHERE email_verified_at IS NOT NULL');
        DB::statement('UPDATE users SET banned_at = DATE_ADD(banned_at, INTERVAL 7 HOUR) WHERE banned_at IS NOT NULL');

        // donations
        DB::statement('UPDATE donations SET created_at = DATE_ADD(created_at, INTERVAL 7 HOUR) WHERE created_at IS NOT NULL');
        DB::statement('UPDATE donations SET updated_at = DATE_ADD(updated_at, INTERVAL 7 HOUR) WHERE updated_at IS NOT NULL');
        DB::statement('UPDATE donations SET approved_at = DATE_ADD(approved_at, INTERVAL 7 HOUR) WHERE approved_at IS NOT NULL');

        // token_quotas
        DB::statement('UPDATE token_quotas SET created_at = DATE_ADD(created_at, INTERVAL 7 HOUR) WHERE created_at IS NOT NULL');
        DB::statement('UPDATE token_quotas SET updated_at = DATE_ADD(updated_at, INTERVAL 7 HOUR) WHERE updated_at IS NOT NULL');
        DB::statement('UPDATE token_quotas SET free_tokens_reset_at = DATE_ADD(free_tokens_reset_at, INTERVAL 7 HOUR) WHERE free_tokens_reset_at IS NOT NULL');
        DB::statement('UPDATE token_quotas SET paid_expires_at = DATE_ADD(paid_expires_at, INTERVAL 7 HOUR) WHERE paid_expires_at IS NOT NULL');

        // user_invitations
        DB::statement('UPDATE user_invitations SET created_at = DATE_ADD(created_at, INTERVAL 7 HOUR) WHERE created_at IS NOT NULL');
        DB::statement('UPDATE user_invitations SET updated_at = DATE_ADD(updated_at, INTERVAL 7 HOUR) WHERE updated_at IS NOT NULL');
        DB::statement('UPDATE user_invitations SET expires_at = DATE_ADD(expires_at, INTERVAL 7 HOUR) WHERE expires_at IS NOT NULL');
        DB::statement('UPDATE user_invitations SET accepted_at = DATE_ADD(accepted_at, INTERVAL 7 HOUR) WHERE accepted_at IS NOT NULL');

        // trial_requests
        DB::statement('UPDATE trial_requests SET created_at = DATE_ADD(created_at, INTERVAL 7 HOUR) WHERE created_at IS NOT NULL');
        DB::statement('UPDATE trial_requests SET updated_at = DATE_ADD(updated_at, INTERVAL 7 HOUR) WHERE updated_at IS NOT NULL');

        // --- System tables ---

        // settings
        DB::statement('UPDATE settings SET created_at = DATE_ADD(created_at, INTERVAL 7 HOUR) WHERE created_at IS NOT NULL');
        DB::statement('UPDATE settings SET updated_at = DATE_ADD(updated_at, INTERVAL 7 HOUR) WHERE updated_at IS NOT NULL');

        // model_pricings
        DB::statement('UPDATE model_pricings SET created_at = DATE_ADD(created_at, INTERVAL 7 HOUR) WHERE created_at IS NOT NULL');
        DB::statement('UPDATE model_pricings SET updated_at = DATE_ADD(updated_at, INTERVAL 7 HOUR) WHERE updated_at IS NOT NULL');

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Reverse: shift timestamps back from WIB to UTC (-7 hours).
     */
    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        DB::statement('UPDATE token_usages SET created_at = DATE_SUB(created_at, INTERVAL 7 HOUR) WHERE created_at IS NOT NULL');
        DB::statement('UPDATE wallet_transactions SET created_at = DATE_SUB(created_at, INTERVAL 7 HOUR) WHERE created_at IS NOT NULL');

        DB::statement('UPDATE api_keys SET last_used_at = DATE_SUB(last_used_at, INTERVAL 7 HOUR) WHERE last_used_at IS NOT NULL');
        DB::statement('UPDATE api_keys SET created_at = DATE_SUB(created_at, INTERVAL 7 HOUR) WHERE created_at IS NOT NULL');
        DB::statement('UPDATE api_keys SET updated_at = DATE_SUB(updated_at, INTERVAL 7 HOUR) WHERE updated_at IS NOT NULL');

        DB::statement('UPDATE users SET created_at = DATE_SUB(created_at, INTERVAL 7 HOUR) WHERE created_at IS NOT NULL');
        DB::statement('UPDATE users SET updated_at = DATE_SUB(updated_at, INTERVAL 7 HOUR) WHERE updated_at IS NOT NULL');
        DB::statement('UPDATE users SET email_verified_at = DATE_SUB(email_verified_at, INTERVAL 7 HOUR) WHERE email_verified_at IS NOT NULL');
        DB::statement('UPDATE users SET banned_at = DATE_SUB(banned_at, INTERVAL 7 HOUR) WHERE banned_at IS NOT NULL');

        DB::statement('UPDATE donations SET created_at = DATE_SUB(created_at, INTERVAL 7 HOUR) WHERE created_at IS NOT NULL');
        DB::statement('UPDATE donations SET updated_at = DATE_SUB(updated_at, INTERVAL 7 HOUR) WHERE updated_at IS NOT NULL');
        DB::statement('UPDATE donations SET approved_at = DATE_SUB(approved_at, INTERVAL 7 HOUR) WHERE approved_at IS NOT NULL');

        DB::statement('UPDATE token_quotas SET created_at = DATE_SUB(created_at, INTERVAL 7 HOUR) WHERE created_at IS NOT NULL');
        DB::statement('UPDATE token_quotas SET updated_at = DATE_SUB(updated_at, INTERVAL 7 HOUR) WHERE updated_at IS NOT NULL');
        DB::statement('UPDATE token_quotas SET free_tokens_reset_at = DATE_SUB(free_tokens_reset_at, INTERVAL 7 HOUR) WHERE free_tokens_reset_at IS NOT NULL');
        DB::statement('UPDATE token_quotas SET paid_expires_at = DATE_SUB(paid_expires_at, INTERVAL 7 HOUR) WHERE paid_expires_at IS NOT NULL');

        DB::statement('UPDATE user_invitations SET created_at = DATE_SUB(created_at, INTERVAL 7 HOUR) WHERE created_at IS NOT NULL');
        DB::statement('UPDATE user_invitations SET updated_at = DATE_SUB(updated_at, INTERVAL 7 HOUR) WHERE updated_at IS NOT NULL');
        DB::statement('UPDATE user_invitations SET expires_at = DATE_SUB(expires_at, INTERVAL 7 HOUR) WHERE expires_at IS NOT NULL');
        DB::statement('UPDATE user_invitations SET accepted_at = DATE_SUB(accepted_at, INTERVAL 7 HOUR) WHERE accepted_at IS NOT NULL');

        DB::statement('UPDATE trial_requests SET created_at = DATE_SUB(created_at, INTERVAL 7 HOUR) WHERE created_at IS NOT NULL');
        DB::statement('UPDATE trial_requests SET updated_at = DATE_SUB(updated_at, INTERVAL 7 HOUR) WHERE updated_at IS NOT NULL');

        DB::statement('UPDATE settings SET created_at = DATE_SUB(created_at, INTERVAL 7 HOUR) WHERE created_at IS NOT NULL');
        DB::statement('UPDATE settings SET updated_at = DATE_SUB(updated_at, INTERVAL 7 HOUR) WHERE updated_at IS NOT NULL');

        DB::statement('UPDATE model_pricings SET created_at = DATE_SUB(created_at, INTERVAL 7 HOUR) WHERE created_at IS NOT NULL');
        DB::statement('UPDATE model_pricings SET updated_at = DATE_SUB(updated_at, INTERVAL 7 HOUR) WHERE updated_at IS NOT NULL');

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
