# AI Token Dashboard - Implementation Plan

## Project Overview

Dashboard Laravel untuk menjual akses AI token (pay-as-you-go) menggunakan EnowxAI sebagai backend proxy. User mendapat API key yang bisa dipakai di Cursor, VS Code, Cline, dan tool OpenAI-compatible lainnya.

## Business Model

| Tier | Harga | Token Limit | Durasi | Model |
|------|-------|-------------|--------|-------|
| Free | Gratis | 1M token (input+output) | Reset per bulan | Semua model |
| Donasi | Rp 20.000 | 10M token (input+output) | 1 hari (24 jam) | Semua model |

- Modal: Rp 3.000/1M token, Margin donasi: ~Rp 17.000/transaksi
- Payment: QRIS statis + upload bukti transfer, Admin approve manual
- Konsep donasi bukan resell

## Tech Stack

- Backend: Laravel 10 (existing)
- Frontend: Blade + Tailwind CSS + Alpine.js (existing Breeze)
- Auth: Laravel Breeze (existing)
- API Proxy: EnowxAI di VPS (43.133.141.45:1434/v1)
- Dashboard EnowxAI: 43.133.141.45:1435
- Database: MySQL

## Architecture

User (Cursor/VS Code/Cline) -> Laravel API Proxy -> EnowxAI Proxy -> AI Models

## Database Schema

### api_keys
- id, user_id, key (sk-random64), name, is_active, last_used_at, timestamps

### token_usages
- id, user_id, api_key_id, model, input_tokens, output_tokens, total_tokens, request_path, status_code, response_time_ms, created_at

### token_quotas
- id, user_id, free_tokens_used, free_tokens_limit (1M), free_tokens_reset_at, paid_tokens_used, paid_tokens_limit, paid_expires_at, timestamps

### donations
- id, user_id, amount, token_amount, duration_hours, status (pending/approved/rejected/expired), payment_proof, admin_notes, approved_by, approved_at, timestamps

### settings
- id, key, value, timestamps

### users (modify)
- Add role (enum: user, admin)

## Phase 1: Database and Models
## Phase 2: API Key Management
## Phase 3: API Proxy (Core)
## Phase 4: User Dashboard
## Phase 5: Donation/Payment System
## Phase 6: Admin Panel
## Phase 7: Scheduled Tasks
## Phase 8: UI/UX Polish

## Environment Variables
- ENOWXAI_BASE_URL=http://43.133.141.45:1434/v1
- ENOWXAI_API_KEY=your-enowxai-api-key
- FREE_TIER_TOKEN_LIMIT=1000000
- PAID_TIER_TOKEN_LIMIT=10000000
- PAID_TIER_DURATION_HOURS=24
- PAID_TIER_PRICE=20000
