# Security Architecture — Mpesa Analyzer WebApp

This document details encryption protocols, authentication boundaries, and defensive web measures.

---

## 1. Dynamic IV AES-128-CBC Decryption

Ingested files uploaded via `POST /api/v1/upload` are encrypted client-side on Android devices using dynamic session IVs.

### Decryption Protocol
1. The backend receives the binary stream (`loot_[uuid].enc`).
2. It extracts the first **16 bytes** to serve as the initialization vector (`$iv`).
3. The remaining payload bytes (`$ciphertext`) are decrypted using OpenSSL:
   ```php
   $plaintext = openssl_decrypt(
       $ciphertext,
       'AES-128-CBC',
       getenv('MPESA_CRYPT_KEY'),
       OPENSSL_RAW_DATA,
       $iv
   );
   ```
4. The decrypted JSON string is validated and parsed into individual SMS records.

---

## 2. Shield Access Tokens & Sessions

- **Web Users**: Authenticated via standard session cookies. Cookies are configured with `HttpOnly`, `SameSite=Lax`, and 30-day lifetime.
- **Mobile Clients**: Authenticated via SHA-256 hashed access tokens passed in the `Authorization: Bearer <token>` header. Raw tokens are generated once, and only SHA-256 hashes are stored in `auth_identities`.

---

## 3. Web Defenses

- **CSRF Protection**: Enabled globally via `app/Config/Security.php` with automatic token regeneration on submission.
- **Content Security Policy (CSP)**: Strict script, style, and font origin whitelisting configured in `app/Config/App.php`.
- **Role-Based Access Control (RBAC)**: Shield groups (`superadmin`, `admin`, `developer`, `user`, `beta`) gate sensitive admin features and ML model management.
