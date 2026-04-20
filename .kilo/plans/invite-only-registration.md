# Plan: Invite-Only Registration System

## Overview
Tutup registrasi publik. Admin bisa invite user via email dari admin dashboard. User menerima email berisi link untuk set password. Mail via Gmail SMTP.

## Keputusan
- **Mail**: Gmail SMTP (App Password)
- **Flow**: Admin invite -> email link set password -> user aktif
- **Handle /register**: Redirect ke /login + flash message

---

## File Changes

### 1. Disable Public Registration Route
**File: `routes/auth.php`**
- Hapus route GET/POST `register`
- Tambahkan redirect: `Route::get('register', fn() => redirect()->route('login')->with('status', 'Registrasi hanya melalui undangan admin.'))->name('register');`
- Keep route name `register` agar tidak break link di login page

### 2. Create Invitation Migration
**File: `database/migrations/xxxx_create_user_invitations_table.php`**
```
Schema::create('user_invitations', function (Blueprint $table) {
    $table->id();
    $table->string('email')->unique();
    $table->string('name');
    $table->string('token', 64)->unique();
    $table->foreignId('invited_by')->constrained('users');
    $table->timestamp('expires_at');
    $table->timestamp('accepted_at')->nullable();
    $table->timestamps();
});
```

### 3. Create UserInvitation Model
**File: `app/Models/UserInvitation.php`**
- Fields: email, name, token, invited_by, expires_at, accepted_at
- Relationships: `invitedBy()` -> User
- Methods: `isExpired()`, `isAccepted()`, `isPending()`
- Token: 64-char random string via `Str::random(64)`
- Expiry: 72 hours from creation

### 4. Create InvitationController (Admin)
**File: `app/Http/Controllers/Admin/InvitationController.php`**
- `store(Request $request)` - Validate email (unique in users + invitations), create invitation, send email, redirect back with success
- `resend(UserInvitation $invitation)` - Resend invitation email, update expires_at

### 5. Create AcceptInvitationController (Guest)
**File: `app/Http/Controllers/Auth/AcceptInvitationController.php`**
- `show(string $token)` - Validate token (exists, not expired, not accepted), show set-password form
- `store(Request $request, string $token)` - Validate password, create User, mark invitation accepted, auto-login, redirect to dashboard

### 6. Create Mailable: InvitationMail
**File: `app/Mail/InvitationMail.php`**
- Receives: UserInvitation model
- Subject: "Anda diundang untuk bergabung di {APP_NAME}"
- View: `emails.invitation`

### 7. Create Email Template
**File: `resources/views/emails/invitation.blade.php`**
- Clean HTML email template
- Content: greeting, invitation message, "Set Password" button/link, expiry info
- Anti-spam best practices:
  - Proper FROM name and address
  - Text/HTML multipart
  - Unsubscribe-friendly (one-time link)
  - No spammy words
  - Clear sender identity

### 8. Create Set Password View
**File: `resources/views/auth/accept-invitation.blade.php`**
- Form: password + password_confirmation
- Shows invited email (readonly)
- Uses guest layout with Intercom design system
- Submit button: "Buat Akun"

### 9. Add Routes
**File: `routes/web.php` or `routes/auth.php`**
```php
// Guest routes (accept invitation)
Route::get('invitation/{token}', [AcceptInvitationController::class, 'show'])->name('invitation.accept');
Route::post('invitation/{token}', [AcceptInvitationController::class, 'store'])->name('invitation.store');

// Admin routes (send invitation)
Route::post('admin/users/invite', [InvitationController::class, 'store'])->name('admin.users.invite');
Route::post('admin/users/invite/{invitation}/resend', [InvitationController::class, 'resend'])->name('admin.users.invite.resend');
```

### 10. Update Admin Users Index View
**File: `resources/views/admin/users/index.blade.php`**
- Add "Invite User" button next to search bar
- Add invite modal/form: name + email fields
- Add pending invitations section (list of pending invites with resend button)

### 11. Gmail SMTP Anti-Spam Configuration
**File: `.env` (production - manual config)**
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

**Anti-spam measures:**
- Use Gmail App Password (not regular password)
- MAIL_FROM_ADDRESS must match MAIL_USERNAME
- Set proper APP_NAME in .env
- Email template uses proper HTML structure
- Single clear CTA button
- No excessive links or images
- Include plain text version

---

## Implementation Order

1. Migration: `user_invitations` table
2. Model: `UserInvitation`
3. Mail: `InvitationMail` + email template
4. Controller: `AcceptInvitationController` (guest)
5. View: `accept-invitation.blade.php`
6. Controller: `InvitationController` (admin)
7. Update: `admin/users/index.blade.php` (invite UI)
8. Routes: invitation routes (guest + admin)
9. Disable: public registration route
10. Run migration + build + test

## Files to Create (6)
- `database/migrations/xxxx_create_user_invitations_table.php`
- `app/Models/UserInvitation.php`
- `app/Mail/InvitationMail.php`
- `resources/views/emails/invitation.blade.php`
- `app/Http/Controllers/Admin/InvitationController.php`
- `app/Http/Controllers/Auth/AcceptInvitationController.php`
- `resources/views/auth/accept-invitation.blade.php`

## Files to Modify (2)
- `routes/auth.php` - disable register, add invitation routes
- `resources/views/admin/users/index.blade.php` - add invite UI

## No Changes Needed
- `.env` - manual config on server (Gmail SMTP credentials)
- `config/mail.php` - already supports SMTP, no changes needed
