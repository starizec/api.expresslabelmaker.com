# Transactional Emails Documentation
Generated: {{ date('Y-m-d H:i:s') }}

## Overview
This document lists all transactional emails in the ExpressLabelMaker application.

---

## 1. Mailable Classes (app/Mail/)

### 1.1 ContactFormMail
**File:** `app/Mail/ContactFormMail.php`
**Purpose:** Handles contact form submissions
**Views:** 
- `emails.contact-form` (admin notification)
- `emails.contact-form-confirmation` (user confirmation)

**Usage:**
- Sent to: `info@expresslabelmaker.com` (admin notification)
- Sent to: User email (confirmation)
- Triggered from: `ContactController.php`

**Subject Lines:**
- Admin: "Nova kontakt poruka - ExpressLabelMaker"
- Confirmation: "Potvrda primitka vaše poruke - ExpressLabelMaker"

**Parameters:**
- `$email` - User email address
- `$messageContent` - Contact message content
- `$isConfirmation` - Boolean flag for confirmation vs admin notification

---

### 1.2 PaymentDataMail
**File:** `app/Mail/PaymentDataMail.php`
**Purpose:** Sends payment/licence information to admin
**View:** `emails.payment-data`

**Usage:**
- Sent to: `info@expresslabelmaker.com`
- Reply-To: User email
- Triggered from: `PaymentController.php`

**Subject:** `__('payment.offer_request') . ' - ExpressLabelMaker'`

**Parameters:**
- `$licence` - Licence model instance
- `$user` - User model instance
- `$domain` - Domain model instance
- `$locale` - Locale string (default: 'hr')

---

### 1.3 PasswordReset
**File:** `app/Mail/PasswordReset.php`
**Purpose:** Password reset email (appears incomplete/stub)
**View:** `view.name` (needs to be configured)

**Status:** ⚠️ INCOMPLETE - View reference is placeholder

---

## 2. Notification Classes (app/Notifications/)

### 2.1 WelcomeNewUserNotification
**File:** `app/Notifications/WelcomeNewUserNotification.php`
**Purpose:** Welcome email with password setup instructions for new users
**Type:** Queued notification (implements ShouldQueue)

**Usage:**
- Triggered from: `User::sendPasswordSetupNotification()`
- Called from: `LicenceController.php` when new user is created

**Subject:** `Lang::get('auth.welcome_subject')`
**Content:** Uses Laravel MailMessage with:
- Greeting: `Lang::get('auth.welcome_greeting')`
- Message: `Lang::get('auth.welcome_message')`
- Password setup instructions
- Action button with reset URL
- Expiry information

**Features:**
- Generates password reset URL
- Includes expiry time from config
- Uses translation keys from `resources/lang/*/auth.php`

---

### 2.2 NewDomainNotification
**File:** `app/Notifications/NewDomainNotification.php`
**Purpose:** Notifies admin when a new domain is added
**Type:** Queued notification (implements ShouldQueue)

**Usage:**
- Triggered from: `Domain.php` model (when domain is created)
- Sent to: Domain owner (user)

**Subject:** `Lang::get('messages.new_domain_notification')`
**Greeting:** `Lang::get('messages.new_domain_added', ['domain' => $domain])`

**Parameters:**
- `$domain` - Domain name/identifier

---

### 2.3 LicenceBoughtNotification
**File:** `app/Notifications/LicenceBoughtNotification.php`
**Purpose:** Notifies user when a licence is purchased
**Type:** Standard notification

**Usage:**
- Triggered from: `LicenceController.php` (when licence is updated/bought)
- Sent to: Licence owner (user)

**Subject:** `Lang::get('messages.licence_bought_notification', ['domain' => $domain->name])`
**Greeting:** `Lang::get('messages.licence_bought_added', ['domain' => $domain->name, 'valid_until' => $formattedDate])`

**Parameters:**
- `$licence` - Licence model instance
- Includes formatted `valid_until` date (d.m.Y format)

---

### 2.4 LicenceRenewalNotification
**File:** `app/Notifications/LicenceRenewalNotification.php`
**Purpose:** Notifies user when a licence is renewed
**Type:** Standard notification

**Usage:**
- Triggered from: `LicenceController.php` (when new licence is created/renewed)
- Sent to: Licence owner (user)

**Subject:** `Lang::get('messages.licence_renewal_notification', ['domain' => $domain->name])`
**Greeting:** `Lang::get('messages.licence_renewal_added', ['domain' => $domain->name, 'valid_until' => $formattedDate])`

**Parameters:**
- `$licence` - Licence model instance
- Includes formatted `valid_until` date (d.m.Y format)

---

## 3. Email Views (resources/views/emails/)

### 3.1 contact-form.blade.php
**Purpose:** Admin notification for contact form submission
**Used by:** `ContactFormMail` (when `$isConfirmation = false`)

**Content:**
- Email sender display
- Contact message in styled box
- Footer with source information

**Styling:** Inline CSS, max-width 600px, blue header (#045cb8)

---

### 3.2 contact-form-confirmation.blade.php
**Purpose:** User confirmation email for contact form submission
**Used by:** `ContactFormMail` (when `$isConfirmation = true`)

**Content:**
- Thank you message
- Confirmation of message receipt
- Copy of user's message
- ExpressLabelMaker team signature
- Auto-generated disclaimer

**Styling:** Inline CSS, max-width 600px, dark header (#2c3e50)

---

### 3.3 payment-data.blade.php
**Purpose:** Payment/licence information email to admin
**Used by:** `PaymentDataMail`

**Content:**
- Licence information section:
  - Licence UID
  - Domain name
  - Valid from date
  - Valid until date
- User information section:
  - First name, Last name
  - Email
  - Company name, address, town, country
  - VAT number
- Footer with localized message

**Features:**
- Uses translation keys from `resources/lang/*/payment.php`
- Handles date formatting with fallback
- Responsive design with styled sections

---

## 4. Notification Template (resources/views/vendor/notifications/)

### 4.1 email.blade.php
**Purpose:** Base template for Laravel notifications
**Used by:** All notification classes (WelcomeNewUserNotification, NewDomainNotification, LicenceBoughtNotification, LicenceRenewalNotification)

**Features:**
- Uses Laravel Mail components (`@component('mail::message')`)
- Supports greeting customization
- Action button support with color variants
- Intro/outro lines
- Salutation
- Subcopy for action URLs

**Components:**
- Greeting (customizable or default)
- Intro lines (array)
- Action button (optional)
- Outro lines (array)
- Salutation
- Subcopy for action URLs

---

## 5. Email Usage Locations

### 5.1 ContactController.php
```php
// Admin notification
Mail::to('info@expresslabelmaker.com')->send(new ContactFormMail($validated));

// User confirmation
Mail::to($validated['email'])->send(new ContactFormMail($validated, true));
```

### 5.2 PaymentController.php
```php
Mail::send(new \App\Mail\PaymentDataMail($licence, $lang));
```

### 5.3 LicenceController.php
```php
// Welcome new user
$user->sendPasswordSetupNotification();

// Licence bought
$update_licence->user->notify(new LicenceBoughtNotification($update_licence));

// Licence renewal
$new_licence->user->notify(new LicenceRenewalNotification($new_licence));
```

### 5.4 Domain.php (Model)
```php
$this->user->notify(new NewDomainNotification($domain));
```

### 5.5 User.php (Model)
```php
public function sendPasswordSetupNotification()
{
    $this->notify(new \App\Notifications\WelcomeNewUserNotification());
}
```

---

## 6. Translation Keys Used

### auth.php
- `auth.welcome_subject`
- `auth.welcome_greeting`
- `auth.welcome_message`
- `auth.welcome_password_setup`
- `auth.welcome_password_button`
- `auth.welcome_password_expiry`
- `auth.welcome_no_action`

### messages.php
- `messages.new_domain_notification`
- `messages.new_domain_added`
- `messages.licence_bought_notification`
- `messages.licence_bought_added`
- `messages.licence_renewal_notification`
- `messages.licence_renewal_added`

### payment.php
- `payment.email_title`
- `payment.offer_request`
- `payment.licence_information`
- `payment.licence_uid`
- `payment.domain_name`
- `payment.valid_from`
- `payment.valid_until`
- `payment.user_information`
- `payment.first_name`
- `payment.last_name`
- `payment.email`
- `payment.company_name`
- `payment.company_address`
- `payment.town`
- `payment.country`
- `payment.vat_number`
- `payment.email_footer`

---

## 7. Email Summary

### Total Emails: 7

1. **Contact Form (Admin)** - `ContactFormMail` → `contact-form.blade.php`
2. **Contact Form (User Confirmation)** - `ContactFormMail` → `contact-form-confirmation.blade.php`
3. **Payment Data** - `PaymentDataMail` → `payment-data.blade.php`
4. **Welcome New User** - `WelcomeNewUserNotification` → `email.blade.php` (notification template)
5. **New Domain** - `NewDomainNotification` → `email.blade.php` (notification template)
6. **Licence Bought** - `LicenceBoughtNotification` → `email.blade.php` (notification template)
7. **Licence Renewal** - `LicenceRenewalNotification` → `email.blade.php` (notification template)

### Email Recipients

**Admin Emails (info@expresslabelmaker.com):**
- Contact form submissions
- Payment/licence requests

**User Emails:**
- Contact form confirmations
- Welcome/password setup
- New domain notifications
- Licence purchase confirmations
- Licence renewal confirmations

---

## 8. Notes

⚠️ **Issues Found:**
1. `PasswordReset` mailable has incomplete view reference (`view.name` - needs to be configured)
2. All notifications use the same base template (`email.blade.php`)
3. Some emails use hardcoded Croatian text (contact form subjects)
4. Translation keys may need verification in language files

✅ **Best Practices:**
- Most emails use translation keys for localization
- Queued notifications for better performance
- Proper separation of concerns (Mailable vs Notifications)
- Email views are well-structured with inline CSS for email clients

---

## 9. File Structure

```
app/
├── Mail/
│   ├── ContactFormMail.php
│   ├── PaymentDataMail.php
│   └── PasswordReset.php (incomplete)
└── Notifications/
    ├── WelcomeNewUserNotification.php
    ├── NewDomainNotification.php
    ├── LicenceBoughtNotification.php
    └── LicenceRenewalNotification.php

resources/views/
├── emails/
│   ├── contact-form.blade.php
│   ├── contact-form-confirmation.blade.php
│   └── payment-data.blade.php
└── vendor/
    └── notifications/
        └── email.blade.php
```

---

*End of Transactional Emails Documentation*

