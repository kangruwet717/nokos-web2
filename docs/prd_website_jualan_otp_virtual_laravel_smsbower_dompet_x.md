# PRD Website Jualan OTP Virtual

## 1. Ringkasan Produk

### 1.1 Nama Produk Sementara
**OTP Virtual Marketplace**

Nama final dapat diganti saat branding, misalnya:
- RanzzOTP
- IndoOTP Hub
- VerifyID Virtual Number
- OTPMarket

### 1.2 Deskripsi Produk
Website ini adalah platform penjualan nomor virtual untuk menerima SMS/OTP secara legal dan terkontrol. Sistem mengambil stok nomor, layanan, negara, harga, dan status aktivasi dari provider SMSBower, lalu menjualnya kembali ke user dengan margin tertentu. Pembayaran/top up saldo dilakukan melalui payment gateway DompetX.

Platform dibuat menggunakan Laravel dengan sistem wallet, order OTP, webhook provider, webhook pembayaran, dashboard user, dashboard admin, monitoring transaksi, dan kontrol anti-penyalahgunaan.

### 1.3 Tujuan Produk
Tujuan utama:
1. Menyediakan platform self-service untuk pembelian nomor virtual/OTP.
2. Memudahkan user melakukan top up saldo dan order nomor secara cepat.
3. Mengotomatisasi proses pemesanan nomor dari provider SMSBower.
4. Mengotomatisasi pembayaran melalui DompetX.
5. Memberikan dashboard admin untuk monitoring, profit, saldo provider, order, dan komplain.
6. Menyediakan fondasi legal, audit, dan anti-abuse sebelum publish ke umum.

### 1.4 Prinsip Legal dan Etika Produk
Produk harus digunakan hanya untuk kebutuhan yang sah, misalnya testing aplikasi, QA, verifikasi layanan yang dimiliki user, atau kebutuhan bisnis yang sesuai ketentuan layanan pihak terkait.

Platform tidak boleh diposisikan untuk:
- spam;
- penipuan;
- impersonation;
- pembuatan akun palsu massal;
- bypass sistem keamanan pihak ketiga;
- penyalahgunaan layanan digital;
- aktivitas ilegal lain.

Karena itu, PRD ini memasukkan fitur compliance, audit log, risk scoring, blacklist service, limit order, dan mekanisme suspend user.

---

## 2. Latar Belakang dan Referensi

### 2.1 Referensi Tampilan
Referensi UI/UX:
1. smspva.com
   - Tampilan marketplace OTP.
   - Daftar layanan dan negara.
   - Dashboard saldo dan order.
2. my.otpku.co.id
   - Tampilan lokal Indonesia.
   - Sistem saldo.
   - Order OTP berdasarkan service dan country.

Website final tidak harus menyalin desain, tetapi mengambil pola UX:
- landing page sederhana;
- daftar service populer;
- filter negara;
- dashboard user cepat;
- order status realtime;
- riwayat transaksi jelas;
- admin panel operasional.

### 2.2 Provider OTP
Provider utama: **SMSBower**.

Kebutuhan integrasi:
- cek saldo provider;
- ambil daftar layanan;
- ambil daftar negara;
- ambil harga/stok;
- request nomor;
- cek status SMS;
- cancel order;
- complete order;
- menerima webhook SMS jika tersedia.

### 2.3 Payment Gateway
Payment gateway utama: **DompetX**.

Kebutuhan integrasi:
- create transaction/top up invoice;
- redirect atau payment URL;
- cek status pembayaran;
- menerima webhook pembayaran;
- validasi signature webhook;
- idempotency agar saldo tidak masuk dobel;
- rekonsiliasi transaksi.

---

## 3. Target Pengguna

### 3.1 User Biasa
User yang membeli nomor virtual untuk kebutuhan valid dan sesuai aturan.

Kebutuhan:
- daftar/login;
- top up saldo;
- pilih service dan country;
- beli nomor;
- melihat kode SMS/OTP;
- membatalkan order jika belum menerima SMS;
- melihat riwayat transaksi;
- menghubungi support.

### 3.2 Reseller / Power User
User dengan volume tinggi.

Kebutuhan tambahan:
- limit order lebih besar setelah verifikasi;
- invoice/top up besar;
- riwayat order lebih detail;
- export transaksi;
- API internal opsional di tahap lanjutan.

### 3.3 Admin Operasional
Pihak internal yang mengelola platform.

Kebutuhan:
- melihat order aktif;
- melihat transaksi pembayaran;
- adjust saldo manual;
- refund;
- suspend user;
- melihat log webhook;
- melihat profit/loss;
- mengatur margin harga;
- mengatur whitelist/blacklist service;
- cek saldo provider;
- memproses komplain.

### 3.4 Super Admin / Owner
Pemilik bisnis.

Kebutuhan:
- semua akses admin;
- melihat laporan keuangan;
- melihat profit harian/bulanan;
- konfigurasi provider;
- konfigurasi payment gateway;
- konfigurasi risk rules;
- export data;
- akses audit log.

---

## 4. Scope Produk

## 4.1 Scope MVP
MVP adalah versi pertama yang layak dipublish secara terbatas/beta.

Fitur MVP:
1. Landing page.
2. Register, login, logout.
3. Dashboard user.
4. Sistem wallet/saldo.
5. Top up saldo via DompetX.
6. Webhook pembayaran DompetX.
7. Katalog service/country dari SMSBower.
8. Harga jual dengan margin.
9. Order nomor OTP.
10. Polling status SMS atau webhook provider.
11. Cancel order sesuai aturan provider.
12. Riwayat order.
13. Riwayat wallet.
14. Admin panel dasar.
15. Pengaturan margin.
16. Monitoring saldo provider.
17. Log webhook.
18. Basic anti-abuse: rate limit, suspend user, blacklist service.
19. Terms of Service dan Privacy Policy.
20. Halaman kontak/support.

## 4.2 Scope V1 Public Launch
Versi publish umum luas.

Tambahan dari MVP:
1. UI lebih matang dan responsive.
2. Risk scoring user.
3. KYC manual untuk user volume tinggi.
4. Ticketing support.
5. Reconciliation job untuk pembayaran.
6. Alerting admin.
7. Export laporan CSV/XLSX.
8. Promo/voucher.
9. Notifikasi email/Telegram/WhatsApp untuk admin.
10. Sistem maintenance mode per provider/service.
11. CDN dan caching.
12. Backup otomatis.
13. Dokumentasi operasional admin.
14. Penetration testing dasar.
15. Monitoring uptime.

## 4.3 Scope V2
Fitur lanjutan setelah stabil:
1. Multi-provider OTP.
2. API reseller.
3. Referral program.
4. Tier pricing.
5. Auto KYC integration.
6. Fraud analytics dashboard.
7. Mobile web/PWA.
8. Multi-language.
9. Integrasi live chat.
10. Auto settlement report.

## 4.4 Out of Scope untuk MVP
Tidak dikerjakan di MVP:
- aplikasi Android/iOS native;
- marketplace multi-vendor;
- crypto payment;
- affiliate/referral kompleks;
- API publik untuk reseller;
- auto KYC pihak ketiga;
- sistem akuntansi penuh.

---

## 5. Metrik Keberhasilan

### 5.1 Metrik Bisnis
1. Jumlah user terdaftar.
2. Jumlah user aktif harian.
3. Total top up harian.
4. Total order OTP harian.
5. Conversion rate dari landing page ke register.
6. Conversion rate dari register ke top up.
7. Conversion rate dari top up ke order.
8. Gross Merchandise Value.
9. Net profit.
10. Average margin per order.

### 5.2 Metrik Produk
1. Waktu dari login sampai order pertama.
2. Success rate order OTP.
3. Cancel/refund rate.
4. Payment success rate.
5. Webhook success rate.
6. Rata-rata waktu penerimaan SMS.
7. Jumlah komplain per 100 order.
8. Jumlah order gagal karena stok kosong.

### 5.3 Metrik Teknis
1. Uptime minimal 99% untuk MVP, 99.5% untuk public launch.
2. Response time dashboard kurang dari 1 detik untuk halaman cacheable.
3. Webhook response kurang dari 2 detik.
4. Queue delay kurang dari 30 detik.
5. Error rate API internal kurang dari 1%.
6. Tidak ada saldo dobel akibat webhook retry.
7. Tidak ada order tanpa transaksi wallet yang valid.

---

## 6. Persona dan User Story

## 6.1 Persona: User Baru
Sebagai user baru, saya ingin mendaftar dengan email agar bisa membeli nomor virtual.

Acceptance criteria:
- User dapat daftar dengan nama, email, password.
- Email harus unik.
- Password minimal 8 karakter.
- Setelah daftar, user diarahkan ke dashboard.
- Saldo awal 0.

## 6.2 Persona: User yang Ingin Top Up
Sebagai user, saya ingin top up saldo menggunakan metode pembayaran lokal agar bisa membeli nomor OTP.

Acceptance criteria:
- User dapat memilih nominal top up.
- Sistem membuat invoice pending.
- User diarahkan ke payment page/payment instruction.
- Setelah webhook sukses, saldo bertambah otomatis.
- Jika webhook terkirim berulang, saldo hanya bertambah satu kali.
- User dapat melihat status invoice.

## 6.3 Persona: User yang Ingin Order OTP
Sebagai user, saya ingin memilih service dan negara agar sistem memberikan nomor virtual yang sesuai.

Acceptance criteria:
- User dapat melihat daftar service aktif.
- User dapat mencari service.
- User dapat memilih negara.
- User melihat harga jual sebelum order.
- Jika saldo cukup, sistem membuat order.
- Jika provider berhasil memberi nomor, nomor tampil di halaman order.
- Saldo user di-reserve saat order dibuat.
- Saat OTP sukses, saldo dipotong final.
- Jika order gagal/cancel, saldo dikembalikan.

## 6.4 Persona: User yang Menunggu SMS
Sebagai user, saya ingin melihat status OTP secara realtime agar tahu apakah kode sudah masuk.

Acceptance criteria:
- Halaman order menampilkan countdown.
- Status berubah otomatis tanpa refresh penuh jika memungkinkan.
- Jika SMS masuk, kode tampil jelas.
- SMS text sensitif dimasking jika perlu.
- User dapat menyalin kode.
- Order sukses tersimpan di riwayat.

## 6.5 Persona: Admin Operasional
Sebagai admin, saya ingin melihat semua order aktif agar bisa memantau masalah provider.

Acceptance criteria:
- Admin melihat order aktif, waiting, success, cancelled, expired.
- Admin dapat filter berdasarkan user, service, country, status, tanggal.
- Admin dapat melihat provider activation ID.
- Admin dapat melakukan refund manual dengan alasan.
- Semua aksi admin masuk audit log.

## 6.6 Persona: Owner
Sebagai owner, saya ingin melihat laporan profit agar bisa memantau bisnis.

Acceptance criteria:
- Dashboard menampilkan total top up, total order, revenue, cost, profit.
- Data dapat difilter harian, mingguan, bulanan.
- Export CSV tersedia.
- Profit dihitung dari selling price dikurangi provider cost dan fee pembayaran jika ingin dimasukkan.

---

## 7. Alur Produk

## 7.1 Alur Register dan Login
1. User membuka landing page.
2. User klik Register.
3. User mengisi nama, email, password.
4. Sistem validasi email unik.
5. Sistem membuat akun user.
6. User diarahkan ke dashboard.
7. Opsional: email verification diaktifkan sebelum public launch.

Validasi:
- email valid;
- password kuat;
- captcha di form register untuk mengurangi bot;
- rate limit register berdasarkan IP.

## 7.2 Alur Top Up Saldo
1. User masuk dashboard.
2. User klik Top Up.
3. User memilih nominal atau input manual.
4. Sistem validasi nominal minimal dan maksimal.
5. Sistem membuat `payment_invoice` status `pending`.
6. Sistem request create payment ke DompetX.
7. Sistem menyimpan external transaction ID/payment URL.
8. User menyelesaikan pembayaran.
9. DompetX mengirim webhook.
10. Sistem validasi signature webhook.
11. Sistem cek idempotency/event ID.
12. Jika pembayaran sukses, sistem menambah saldo user.
13. Sistem membuat wallet transaction type `topup`.
14. Invoice berubah menjadi `paid`.
15. User melihat saldo terbaru.

Status invoice:
- pending;
- paid;
- expired;
- failed;
- cancelled;
- refunded jika dibutuhkan.

Business rules:
- Satu invoice hanya boleh menambah saldo sekali.
- Webhook invalid signature harus ditolak dan dicatat.
- Webhook valid tetapi status sama harus dianggap idempotent.
- Jika webhook terlambat setelah expired tetapi status paid dari gateway valid, admin rule menentukan apakah tetap diterima atau butuh review.

## 7.3 Alur Order OTP
1. User membuka halaman Beli OTP.
2. Sistem menampilkan service aktif dan country aktif.
3. User memilih service dan country.
4. Sistem menampilkan harga jual dan estimasi stok.
5. User klik Beli.
6. Sistem menjalankan risk check.
7. Sistem validasi saldo tersedia.
8. Sistem membuat order internal status `creating`.
9. Sistem reserve saldo user sebesar selling price.
10. Sistem request nomor ke SMSBower.
11. Jika provider sukses:
    - simpan activation ID;
    - simpan nomor;
    - simpan provider cost;
    - status menjadi `waiting_sms`;
    - tampilkan nomor ke user.
12. Jika provider gagal:
    - status menjadi `failed`;
    - reserve saldo dikembalikan;
    - tampilkan pesan error aman.
13. User menunggu SMS.
14. Sistem menerima webhook SMS atau polling status.
15. Jika OTP masuk:
    - simpan kode;
    - status menjadi `success`;
    - reserve saldo dipotong final;
    - provider activation di-complete jika diperlukan.
16. Jika timeout:
    - sistem cancel ke provider jika memungkinkan;
    - status menjadi `expired` atau `cancelled`;
    - saldo dikembalikan sesuai aturan.

Business rules:
- Tidak boleh ada order tanpa wallet hold/reserve.
- Tidak boleh ada saldo negatif.
- Satu activation ID hanya boleh dimiliki satu order.
- Order tidak boleh di-complete dua kali.
- Cancel hanya tersedia sebelum OTP masuk dan sebelum batas waktu provider.

## 7.4 Alur Cancel Order
1. User membuka order waiting SMS.
2. Tombol Cancel tersedia jika status masih `waiting_sms` dan belum menerima kode.
3. User klik Cancel.
4. Sistem request cancel ke provider.
5. Jika provider menyetujui cancel:
   - status menjadi `cancelled`;
   - saldo reserve dikembalikan;
   - wallet transaction refund dibuat.
6. Jika provider menolak cancel:
   - status tetap waiting/sesuai provider;
   - user mendapat pesan bahwa order tidak dapat dibatalkan.

## 7.5 Alur Webhook SMS Provider
1. Provider mengirim payload SMS ke endpoint webhook.
2. Sistem mencatat raw payload ke `provider_webhook_logs`.
3. Sistem validasi token/signature jika tersedia.
4. Sistem mencari order berdasarkan activation ID.
5. Jika order ditemukan dan status masih waiting:
   - simpan kode;
   - simpan masked SMS text;
   - status success;
   - settle wallet;
   - dispatch event notifikasi.
6. Jika order sudah success:
   - abaikan sebagai idempotent;
   - tetap catat log.
7. Jika order tidak ditemukan:
   - catat sebagai orphan webhook;
   - admin bisa review.

## 7.6 Alur Admin Refund Manual
1. Admin membuka detail order.
2. Admin klik Refund.
3. Admin wajib mengisi alasan refund.
4. Sistem validasi order eligible refund.
5. Sistem mengembalikan saldo ke user.
6. Sistem membuat wallet transaction type `refund`.
7. Sistem update order status `refunded`.
8. Sistem mencatat audit log admin.

---

## 8. Fitur Detail

## 8.1 Landing Page
Tujuan: menjelaskan produk, membangun trust, dan mengarahkan user ke register.

Komponen:
- Navbar: logo, harga, cara kerja, FAQ, login, register.
- Hero section: headline, subheadline, CTA.
- Search preview service.
- Popular services.
- Country coverage.
- Cara kerja:
  1. Top up saldo.
  2. Pilih layanan dan negara.
  3. Terima nomor dan kode SMS.
- Keunggulan:
  - proses cepat;
  - saldo otomatis;
  - riwayat jelas;
  - support;
  - dashboard realtime.
- Compliance notice.
- FAQ.
- Footer: Terms, Privacy, Contact.

Copywriting harus menghindari klaim yang mendorong penyalahgunaan. Gunakan narasi legal/testing/verification needs.

## 8.2 Auth
Fitur:
- register;
- login;
- logout;
- forgot password;
- email verification opsional;
- 2FA untuk admin;
- captcha untuk register/login jika dicurigai bot.

Role:
- user;
- admin;
- super_admin.

Security:
- password hashing bcrypt/argon;
- login throttling;
- session secure cookie;
- CSRF protection;
- optional device/session management.

## 8.3 Dashboard User
Komponen:
- kartu saldo tersedia;
- saldo tertahan/reserved;
- tombol Top Up;
- tombol Beli OTP;
- order aktif;
- riwayat order terbaru;
- riwayat top up terbaru;
- notifikasi sistem;
- status akun/KYC.

## 8.4 Halaman Top Up
Komponen:
- nominal preset: 10k, 25k, 50k, 100k, 250k, 500k, 1jt;
- input nominal manual;
- pilihan metode pembayaran jika didukung gateway;
- ringkasan invoice;
- instruksi pembayaran/payment link;
- status invoice;
- auto refresh status.

Validasi nominal:
- minimal top up: ditentukan admin, misalnya Rp10.000;
- maksimal per transaksi: ditentukan admin;
- limit harian untuk akun baru.

## 8.5 Halaman Beli OTP
Komponen:
- search service;
- filter country;
- sorting harga termurah/populer;
- label stok tersedia;
- harga final;
- tombol beli;
- info estimasi waktu;
- aturan refund/cancel.

UX penting:
- tampilkan harga sebelum user klik beli;
- tampilkan error stok kosong dengan jelas;
- jangan expose error mentah dari provider;
- gunakan skeleton loading saat sync harga.

## 8.6 Halaman Detail Order
Komponen:
- nomor virtual;
- service;
- country;
- harga;
- status;
- countdown;
- kode OTP jika masuk;
- tombol copy nomor;
- tombol copy kode;
- tombol cancel jika eligible;
- log status sederhana untuk user.

Status user-facing:
- Menyiapkan nomor;
- Menunggu SMS;
- Kode diterima;
- Dibatalkan;
- Gagal;
- Kadaluarsa;
- Refund diproses.

## 8.7 Riwayat Order
Filter:
- tanggal;
- service;
- country;
- status;
- keyword nomor/order ID.

Kolom:
- order ID;
- tanggal;
- service;
- country;
- nomor masked;
- harga;
- status;
- aksi detail.

## 8.8 Riwayat Wallet
Kolom:
- tanggal;
- tipe transaksi;
- nominal;
- saldo sebelum;
- saldo sesudah;
- status;
- referensi.

Tipe transaksi:
- topup;
- order_hold;
- order_charge;
- refund;
- adjustment;
- promo_credit.

## 8.9 Admin Dashboard
Komponen:
- total user;
- user aktif;
- total top up hari ini;
- total order hari ini;
- order sukses/gagal;
- gross revenue;
- provider cost;
- profit estimasi;
- saldo provider SMSBower;
- webhook error;
- order waiting terlalu lama;
- user berisiko.

## 8.10 Admin Manajemen User
Fitur:
- list user;
- search user;
- detail user;
- lihat saldo;
- lihat order;
- lihat top up;
- suspend/unsuspend;
- set KYC status;
- manual adjustment saldo;
- catatan admin.

## 8.11 Admin Manajemen Service dan Harga
Fitur:
- sync service dari provider;
- sync country dari provider;
- aktif/nonaktif service;
- aktif/nonaktif country;
- blacklist kombinasi service-country;
- set margin global;
- set margin per service;
- set margin per country;
- set margin per service-country;
- harga minimal;
- round price, misalnya pembulatan ke Rp100/Rp500.

Prioritas harga:
1. Override service-country.
2. Override service.
3. Override country.
4. Margin global.

## 8.12 Admin Order Monitor
Fitur:
- list semua order;
- filter status;
- filter user;
- filter provider;
- filter service/country;
- detail order;
- force refresh status;
- manual cancel;
- manual refund;
- add note.

## 8.13 Admin Payment Monitor
Fitur:
- list invoice;
- filter paid/pending/expired/failed;
- lihat payload webhook;
- cek status manual ke gateway;
- reconcile invoice;
- export laporan.

## 8.14 Support Ticket
MVP bisa sederhana.

Fitur:
- user membuat tiket;
- pilih kategori: payment, order, refund, akun, lainnya;
- attach order ID/invoice ID;
- admin balas tiket;
- status open/pending/closed.

## 8.15 Compliance dan Anti-Abuse
Fitur wajib sebelum public launch:
- Terms of Service;
- Privacy Policy;
- Acceptable Use Policy;
- checkbox persetujuan saat register;
- rate limit order;
- limit top up akun baru;
- blacklist service sensitif;
- blacklist country tertentu jika diperlukan;
- suspend user;
- audit log;
- risk score;
- notifikasi admin untuk aktivitas mencurigakan.

Contoh rule:
- akun baru maksimal 5 order per jam;
- user non-KYC maksimal 30 order per hari;
- user gagal/cancel lebih dari 70% dalam 24 jam diberi flag;
- banyak akun dari IP/device sama diberi flag;
- top up besar pertama kali masuk review manual;
- service tertentu hanya untuk user verified.

---

## 9. Business Rules

## 9.1 Wallet
1. `balance` adalah saldo tersedia.
2. `reserved_balance` adalah saldo tertahan untuk order aktif.
3. Available balance = balance - reserved_balance, atau gunakan model ledger terpisah.
4. Semua perubahan saldo wajib punya record wallet transaction.
5. Tidak boleh update saldo tanpa database transaction.
6. Tidak boleh saldo negatif.
7. Manual adjustment wajib menyimpan alasan dan admin ID.

## 9.2 Order OTP
1. Order hanya bisa dibuat jika saldo tersedia cukup.
2. Harga dikunci saat order dibuat.
3. Perubahan harga setelah order tidak memengaruhi order yang sudah dibuat.
4. Jika provider gagal memberi nomor, saldo dikembalikan.
5. Jika OTP sukses, saldo dipotong final.
6. Jika cancel sukses, saldo dikembalikan.
7. Jika order expired, sistem mengikuti aturan provider untuk refund.
8. Order yang sudah success tidak bisa dicancel user.
9. Admin refund manual bisa dilakukan dengan audit.

## 9.3 Payment
1. Satu invoice hanya boleh paid satu kali.
2. Webhook harus divalidasi signature.
3. Webhook harus idempotent.
4. Payment status dari gateway adalah sumber utama, tetapi reconciliation job diperlukan.
5. Invoice pending yang melewati expiry menjadi expired.
6. Pembayaran expired yang ternyata paid perlu masuk review atau auto accept sesuai kebijakan.

## 9.4 Pricing
1. Harga jual = provider cost + margin.
2. Margin bisa fixed atau percentage.
3. Harga jual minimal tidak boleh lebih rendah dari provider cost.
4. Admin bisa set markup berbeda per service/country.
5. Sistem harus menyimpan provider cost dan selling price pada order untuk laporan profit.

## 9.5 Refund
1. Refund otomatis hanya jika order eligible.
2. Refund manual hanya oleh admin/super admin.
3. Refund harus membuat wallet transaction.
4. Refund tidak boleh lebih besar dari nilai order.
5. Refund berulang harus dicegah.

---

## 10. Model Data

## 10.1 users
Field:
- id
- name
- email
- email_verified_at
- password
- role: user/admin/super_admin
- status: active/suspended/banned
- balance
- reserved_balance
- kyc_status: none/pending/verified/rejected
- risk_score
- last_login_at
- created_at
- updated_at

## 10.2 user_profiles
Field:
- id
- user_id
- phone
- country
- timezone
- metadata
- created_at
- updated_at

## 10.3 wallet_transactions
Field:
- id
- user_id
- type: topup/order_hold/order_charge/refund/adjustment/promo
- direction: credit/debit/hold/release
- amount
- balance_before
- balance_after
- reserved_before
- reserved_after
- reference_type
- reference_id
- status: pending/success/failed/reversed
- description
- metadata
- created_by_admin_id nullable
- created_at
- updated_at

## 10.4 payment_invoices
Field:
- id
- invoice_no
- user_id
- provider: dompetx
- external_id
- idempotency_key
- amount
- fee
- net_amount
- status: pending/paid/expired/failed/cancelled
- payment_method
- payment_url
- raw_create_response
- expired_at
- paid_at
- created_at
- updated_at

## 10.5 payment_webhook_events
Field:
- id
- provider
- event_id
- external_id
- invoice_id nullable
- event_type
- payload
- signature_valid
- processed
- processed_at
- error_message
- created_at

Unique constraints:
- unique(provider, event_id) jika event_id tersedia;
- unique(provider, external_id, event_type) jika tidak ada event_id.

## 10.6 providers
Field:
- id
- code: smsbower
- name
- base_url
- is_active
- config_json encrypted
- created_at
- updated_at

## 10.7 services
Field:
- id
- provider_id
- provider_code
- name
- category
- is_active
- is_blacklisted
- created_at
- updated_at

## 10.8 countries
Field:
- id
- provider_id
- provider_code
- iso_code
- name
- is_active
- is_blacklisted
- created_at
- updated_at

## 10.9 service_prices
Field:
- id
- provider_id
- service_id
- country_id
- provider_price
- margin_type: fixed/percent
- margin_value
- selling_price
- stock_count
- is_active
- last_synced_at
- created_at
- updated_at

Unique:
- unique(provider_id, service_id, country_id)

## 10.10 otp_orders
Field:
- id
- order_no
- user_id
- provider_id
- service_id
- country_id
- provider_activation_id
- phone_number
- phone_number_masked
- provider_cost
- selling_price
- margin_amount
- status: creating/waiting_sms/success/cancelled/expired/failed/refunded
- sms_code encrypted nullable
- sms_text_masked nullable
- raw_provider_response nullable
- expires_at
- completed_at
- cancelled_at
- refunded_at
- refund_reason
- created_at
- updated_at

Unique:
- unique(provider_id, provider_activation_id)

## 10.11 otp_order_status_logs
Field:
- id
- otp_order_id
- old_status
- new_status
- source: system/user/admin/provider/webhook/job
- message
- metadata
- created_at

## 10.12 provider_webhook_events
Field:
- id
- provider
- event_id nullable
- activation_id nullable
- otp_order_id nullable
- payload
- signature_valid
- processed
- processed_at
- error_message
- created_at

## 10.13 audit_logs
Field:
- id
- actor_user_id nullable
- actor_role
- action
- target_type
- target_id
- ip_address
- user_agent
- metadata
- created_at

## 10.14 support_tickets
Field:
- id
- ticket_no
- user_id
- category
- subject
- status: open/pending/closed
- priority: low/normal/high
- related_order_id nullable
- related_invoice_id nullable
- created_at
- updated_at

## 10.15 support_ticket_messages
Field:
- id
- support_ticket_id
- sender_user_id
- message
- attachments_json
- created_at

---

## 11. Arsitektur Sistem

## 11.1 Stack Utama
Backend:
- Laravel 11/12
- PHP 8.3+
- MySQL 8 atau PostgreSQL
- Redis
- Laravel Queue
- Laravel Horizon
- Laravel Scheduler

Frontend:
- Blade + Tailwind CSS untuk MVP cepat
- Livewire atau Inertia.js jika butuh interaksi dinamis
- Alpine.js untuk komponen ringan

Admin:
- Filament Admin Panel direkomendasikan untuk mempercepat pengembangan

Infrastructure:
- VPS/cloud server
- Nginx
- PHP-FPM
- Supervisor untuk queue worker
- Redis
- MySQL/PostgreSQL managed atau self-hosted
- SSL/TLS
- Backup otomatis
- Monitoring uptime

## 11.2 Struktur Folder Laravel

```text
app/
  Actions/
    Wallet/
    Orders/
    Payments/
  Services/
    Providers/
      SmsProviderInterface.php
      SmsbowerClient.php
      SmsProviderManager.php
    Payments/
      PaymentGatewayInterface.php
      DompetxClient.php
      PaymentService.php
    Wallet/
      WalletService.php
    Pricing/
      PricingService.php
    Risk/
      RiskRuleService.php
  Http/
    Controllers/
      Web/
      User/
      Admin/
      Webhook/
    Requests/
  Jobs/
    SyncSmsbowerCatalogJob.php
    PollOtpOrderStatusJob.php
    ExpireOtpOrderJob.php
    ReconcilePaymentInvoiceJob.php
    ProcessDompetxWebhookJob.php
    ProcessSmsbowerWebhookJob.php
  Models/
  Policies/
  Events/
  Listeners/
  Notifications/
```

## 11.3 Service Layer
Gunakan service layer agar controller tidak penuh logic.

Service penting:
- `WalletService`
- `PaymentService`
- `DompetxClient`
- `OtpOrderService`
- `SmsbowerClient`
- `PricingService`
- `RiskRuleService`
- `WebhookProcessingService`

## 11.4 Queue dan Job
Queue digunakan untuk:
- memproses webhook secara asynchronous;
- polling status OTP;
- sync katalog provider;
- rekonsiliasi payment;
- mengirim notifikasi;
- expire order.

Webhook endpoint harus cepat:
1. validate minimal;
2. simpan event;
3. dispatch job;
4. return 200 OK.

## 11.5 Scheduler
Jadwal:
- sync katalog service/country/price: setiap 15-60 menit;
- poll order waiting_sms: setiap 10-30 detik atau batch per menit;
- expire pending invoice: setiap 5 menit;
- reconcile payment: setiap 15-30 menit;
- check provider balance: setiap 5-15 menit;
- backup database: harian;
- cleanup log lama: sesuai retention.

---

## 12. Integrasi SMSBower

## 12.1 Tujuan Integrasi
Integrasi SMSBower digunakan untuk:
- mendapatkan data layanan;
- mendapatkan data negara;
- mengecek harga/stok;
- membeli nomor;
- mengecek SMS masuk;
- cancel/complete order;
- menerima webhook SMS jika tersedia.

## 12.2 Konfigurasi
Environment:

```env
SMSBOWER_API_KEY=
SMSBOWER_BASE_URL=
SMSBOWER_WEBHOOK_SECRET=
SMSBOWER_TIMEOUT_SECONDS=20
SMSBOWER_RETRY_ATTEMPTS=2
```

Catatan:
- API key wajib disimpan di environment variable.
- Jangan pernah menaruh API key di repo.
- Log tidak boleh menyimpan API key.

## 12.3 Interface Provider

```php
interface SmsProviderInterface
{
    public function getBalance(): float;
    public function getServices(): array;
    public function getCountries(): array;
    public function getPrices(?string $service = null, ?string $country = null): array;
    public function requestNumber(string $service, string $country, ?float $maxPrice = null): array;
    public function getStatus(string $activationId): array;
    public function cancel(string $activationId): bool;
    public function complete(string $activationId): bool;
}
```

## 12.4 Error Handling Provider
Kategori error:
- provider timeout;
- invalid API key;
- insufficient provider balance;
- no numbers available;
- price too high;
- activation not found;
- cancel not allowed;
- provider maintenance.

User-facing message harus aman:
- Jangan tampilkan raw error API.
- Tampilkan pesan sederhana seperti “Stok sedang kosong” atau “Provider sedang sibuk, coba lagi nanti”.

Admin log tetap menyimpan detail raw error untuk debugging.

## 12.5 Provider Balance Guard
Sistem harus mencegah order jika saldo provider rendah.

Rule:
- jika provider balance < threshold admin, tampilkan service maintenance;
- kirim alert ke admin;
- tombol order bisa dimatikan sementara.

## 12.6 Webhook SMS
Endpoint:
- `POST /webhooks/smsbower`

Proses:
1. terima payload;
2. simpan raw payload;
3. validasi signature/secret jika tersedia;
4. cari order berdasarkan activation ID;
5. ekstrak kode OTP jika provider memberikan field code;
6. update order;
7. settle wallet;
8. return 200.

Security:
- IP allowlist jika provider mendukung;
- token rahasia di URL atau header jika signature tidak tersedia;
- rate limit webhook;
- audit semua event.

---

## 13. Integrasi DompetX

## 13.1 Tujuan Integrasi
DompetX digunakan sebagai payment gateway untuk top up saldo.

Fungsi:
- create payment;
- get payment status;
- cancel payment jika didukung;
- menerima webhook status pembayaran;
- validasi signature;
- idempotency.

## 13.2 Konfigurasi
Environment:

```env
DOMPETX_BASE_URL=
DOMPETX_API_KEY=
DOMPETX_SECRET_KEY=
DOMPETX_WEBHOOK_SECRET=
DOMPETX_SANDBOX=true
DOMPETX_TIMEOUT_SECONDS=20
```

## 13.3 Payment Flow
1. User request top up.
2. Sistem membuat invoice internal.
3. Sistem membuat request pembayaran ke DompetX.
4. DompetX mengembalikan payment URL/instruction.
5. User membayar.
6. DompetX mengirim webhook.
7. Sistem validasi webhook.
8. Sistem menambah saldo.
9. Sistem update invoice.

## 13.4 Webhook DompetX
Endpoint:
- `POST /webhooks/dompetx`

Proses:
1. terima request;
2. ambil raw body;
3. validasi signature;
4. simpan event webhook;
5. return 200 secepat mungkin;
6. proses event via queue;
7. cek invoice berdasarkan external ID;
8. jika status paid dan belum pernah diproses:
   - update invoice paid;
   - tambah saldo user;
   - buat wallet transaction;
   - catat audit.

## 13.5 Idempotency Payment
Wajib diterapkan di dua sisi:
1. Saat create payment, pakai idempotency key unik per invoice.
2. Saat webhook, deduplicate event berdasarkan event ID/external transaction ID/status.

Rule:
- Jika webhook paid datang 3 kali, saldo hanya masuk 1 kali.
- Jika webhook pending datang setelah paid, abaikan atau simpan log tanpa downgrade status.
- Status tidak boleh mundur dari paid ke pending.

## 13.6 Reconciliation
Job reconciliation harus:
- mencari invoice pending yang belum expired;
- cek status ke DompetX;
- update jika sudah paid/failed/expired;
- menangani missed webhook;
- mencatat hasil cek.

---

## 14. API Internal

## 14.1 Endpoint User

Auth:
- `GET /login`
- `POST /login`
- `GET /register`
- `POST /register`
- `POST /logout`

Dashboard:
- `GET /dashboard`

Top up:
- `GET /topup`
- `POST /topup`
- `GET /topup/{invoice}`
- `GET /topup/{invoice}/status`

OTP:
- `GET /otp`
- `GET /otp/services`
- `GET /otp/prices`
- `POST /otp/orders`
- `GET /otp/orders/{order}`
- `GET /otp/orders/{order}/status`
- `POST /otp/orders/{order}/cancel`

History:
- `GET /orders`
- `GET /wallet/history`

Support:
- `GET /support/tickets`
- `POST /support/tickets`
- `GET /support/tickets/{ticket}`
- `POST /support/tickets/{ticket}/messages`

## 14.2 Endpoint Admin

Dashboard:
- `GET /admin`

Users:
- `GET /admin/users`
- `GET /admin/users/{user}`
- `POST /admin/users/{user}/suspend`
- `POST /admin/users/{user}/unsuspend`
- `POST /admin/users/{user}/adjust-balance`

Services:
- `GET /admin/services`
- `POST /admin/services/sync`
- `PATCH /admin/services/{service}`

Pricing:
- `GET /admin/prices`
- `PATCH /admin/prices/{price}`

Orders:
- `GET /admin/orders`
- `GET /admin/orders/{order}`
- `POST /admin/orders/{order}/refresh`
- `POST /admin/orders/{order}/refund`

Payments:
- `GET /admin/payments`
- `GET /admin/payments/{invoice}`
- `POST /admin/payments/{invoice}/reconcile`

Logs:
- `GET /admin/webhook-logs`
- `GET /admin/audit-logs`

Settings:
- `GET /admin/settings`
- `PATCH /admin/settings`

## 14.3 Webhook Endpoint
- `POST /webhooks/dompetx`
- `POST /webhooks/smsbower`

Webhook endpoints harus exempt dari CSRF tetapi dilindungi signature/token.

---

## 15. UI/UX Requirement

## 15.1 Design Style
Arahan visual:
- modern SaaS dashboard;
- clean;
- cepat dimuat;
- responsive mobile;
- warna utama bisa biru/ungu/hijau sesuai brand;
- card-based layout;
- tabel rapi;
- CTA jelas.

## 15.2 Halaman Wajib
Public:
1. Home.
2. Pricing/Services preview.
3. FAQ.
4. Terms of Service.
5. Privacy Policy.
6. Contact.
7. Login.
8. Register.

User:
1. Dashboard.
2. Top Up.
3. Invoice detail.
4. Beli OTP.
5. Detail order OTP.
6. Riwayat order.
7. Riwayat saldo.
8. Support ticket.
9. Profile/security.

Admin:
1. Dashboard.
2. Users.
3. Orders.
4. Payments.
5. Services.
6. Prices.
7. Provider status.
8. Webhook logs.
9. Audit logs.
10. Settings.

## 15.3 Mobile Requirement
User banyak memakai HP, maka:
- dashboard harus mobile-first;
- tombol copy nomor/kode besar;
- tabel riwayat berubah menjadi card list di mobile;
- order status harus mudah dibaca;
- payment instruction mudah diikuti.

## 15.4 UX Detail untuk Order
Status waiting harus jelas:
- tampilkan countdown;
- tampilkan “Jangan tutup halaman ini, status akan diperbarui otomatis”;
- sediakan tombol refresh manual;
- sediakan tombol cancel jika eligible;
- tampilkan info refund policy.

---

## 16. Security Requirement

## 16.1 Aplikasi
- HTTPS wajib.
- CSRF aktif untuk semua form user.
- XSS protection via escaping Blade.
- SQL injection dicegah dengan Eloquent/query binding.
- Rate limit login/register/topup/order.
- Password hash kuat.
- Admin 2FA.
- Admin route protected by role policy.
- Sensitive data encrypted.
- API key disimpan di `.env`.
- Debug mode off di production.

## 16.2 Webhook
- Verify signature.
- Verify timestamp jika tersedia.
- Idempotency.
- Raw body disimpan untuk audit.
- Return cepat.
- Processing via queue.
- Jangan expose error internal ke response.

## 16.3 Wallet Consistency
- Gunakan database transaction.
- Gunakan row-level lock saat update saldo.
- Hindari race condition ketika user membuat banyak order.
- Semua transaksi saldo harus immutable.
- Gunakan ledger untuk audit.

## 16.4 Data Privacy
- Masking nomor di riwayat setelah periode tertentu.
- SMS text tidak disimpan penuh kecuali dibutuhkan; simpan kode dan masked text.
- Encrypt field sensitif.
- Retention policy untuk log.
- Privacy Policy menjelaskan data yang disimpan.

## 16.5 Admin Security
- 2FA wajib untuk super admin.
- IP allowlist opsional.
- Audit semua aksi admin.
- Role-based permission.
- Tidak ada shared admin account.

---

## 17. Compliance dan Policy

## 17.1 Dokumen Legal Wajib
Sebelum publish umum:
1. Terms of Service.
2. Privacy Policy.
3. Refund Policy.
4. Acceptable Use Policy.
5. Contact/abuse report page.

## 17.2 Isi Minimal Acceptable Use Policy
Dilarang menggunakan platform untuk:
- spam;
- scam;
- phishing;
- impersonation;
- pembuatan akun palsu massal;
- bypass sistem keamanan;
- aktivitas yang melanggar hukum;
- pelanggaran ToS platform pihak ketiga.

## 17.3 Enforcement
Sistem harus mendukung:
- suspend user;
- block withdrawal jika ada fitur withdrawal di masa depan;
- block service tertentu;
- investigasi audit log;
- export data untuk review internal;
- hapus/sensor data sesuai kebijakan privasi.

---

## 18. Non-Functional Requirement

## 18.1 Performance
- Landing page LCP target kurang dari 2.5 detik.
- Dashboard utama load kurang dari 1.5 detik.
- Webhook response kurang dari 2 detik.
- Order request ke provider timeout maksimal 20 detik.
- Polling status memakai queue agar tidak membebani server.

## 18.2 Scalability
MVP target:
- 1.000 user terdaftar;
- 100 user aktif harian;
- 1.000 order per hari.

Public launch target awal:
- 10.000 user terdaftar;
- 1.000 user aktif harian;
- 10.000 order per hari.

Skalabilitas:
- horizontal scaling web server jika diperlukan;
- queue worker terpisah;
- Redis untuk cache/rate limit;
- index database pada kolom pencarian.

## 18.3 Reliability
- Backup database harian.
- Uptime monitoring.
- Error tracking.
- Queue monitoring.
- Alert jika webhook gagal.
- Alert jika provider balance rendah.
- Alert jika payment mismatch.

## 18.4 Observability
Log penting:
- payment create;
- payment webhook;
- provider request;
- provider webhook;
- wallet transaction;
- admin action;
- failed jobs;
- security events.

Tools opsional:
- Laravel Telescope untuk development/staging.
- Sentry/Bugsnag untuk production error tracking.
- UptimeRobot/BetterStack untuk uptime.
- Grafana/Prometheus jika skala meningkat.

---

## 19. Testing Plan

## 19.1 Unit Test
Wajib test:
- pricing calculation;
- wallet debit/credit/hold/release;
- idempotency webhook;
- order status transition;
- risk rule;
- refund logic.

## 19.2 Feature Test
Wajib test:
- register/login;
- create top up invoice;
- webhook payment paid;
- duplicate webhook payment;
- create OTP order success;
- provider fail handling;
- cancel order;
- SMS webhook success;
- admin refund;
- user suspend cannot order.

## 19.3 Integration Test
Gunakan sandbox/mock untuk:
- DompetX create payment;
- DompetX webhook;
- SMSBower request number;
- SMSBower status polling;
- SMSBower webhook.

## 19.4 UAT Scenario
Scenario 1: User baru order sukses.
1. Register.
2. Top up Rp50.000.
3. Webhook paid.
4. Saldo bertambah.
5. Pilih service.
6. Beli OTP.
7. Nomor tampil.
8. SMS masuk.
9. Kode tampil.
10. Saldo terpotong.

Scenario 2: Payment webhook duplicate.
1. Buat invoice.
2. Kirim webhook paid dua kali.
3. Saldo hanya bertambah sekali.

Scenario 3: Provider gagal.
1. User beli OTP.
2. Provider return stok kosong.
3. Order failed.
4. Saldo tidak terpotong.

Scenario 4: Cancel order.
1. User beli OTP.
2. Status waiting SMS.
3. User cancel.
4. Provider cancel success.
5. Saldo kembali.

Scenario 5: Admin refund.
1. Order sudah gagal secara operasional.
2. Admin refund.
3. Saldo user bertambah.
4. Audit log tercatat.

---

## 20. Deployment dan Infrastruktur

## 20.1 Environment
Minimal 3 environment:
1. Local development.
2. Staging.
3. Production.

Staging harus memakai:
- sandbox DompetX;
- API provider test atau limit kecil;
- dummy data;
- domain staging berbeda.

## 20.2 Server Production Minimum
Untuk awal:
- VPS 2-4 vCPU;
- RAM 4-8 GB;
- SSD 80 GB+;
- Ubuntu LTS;
- Nginx;
- PHP-FPM;
- MySQL/PostgreSQL;
- Redis;
- Supervisor;
- SSL.

Lebih aman:
- database managed terpisah;
- Redis terpisah;
- object storage untuk file;
- backup offsite.

## 20.3 CI/CD
Pipeline minimal:
1. Pull latest code.
2. Install composer dependencies.
3. Install/build frontend assets.
4. Run tests.
5. Put app maintenance mode.
6. Run migrations.
7. Clear/cache config, route, view.
8. Restart queue.
9. Disable maintenance mode.

## 20.4 Production Config
Checklist `.env`:
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://domain.com`
- database credentials aman;
- Redis aktif;
- queue connection database/redis;
- mail configured;
- DompetX live credentials;
- SMSBower live credentials;
- webhook secret;
- log channel daily/stack;
- session secure cookie.

## 20.5 Domain dan SSL
- Domain utama: `domain.com`
- Admin bisa: `domain.com/admin`
- Webhook:
  - `domain.com/webhooks/dompetx`
  - `domain.com/webhooks/smsbower`
- SSL wajib aktif.
- Redirect HTTP ke HTTPS.

---

## 21. Roadmap Implementasi

## 21.1 Phase 0: Persiapan
Durasi estimasi: 2-4 hari kerja.

Output:
- finalisasi nama brand;
- finalisasi domain;
- akses SMSBower;
- akses DompetX sandbox/live;
- desain logo sederhana;
- repo Git;
- setup Laravel project;
- setup staging server.

Checklist:
- API key provider tersedia;
- payment gateway sandbox aktif;
- skema pricing disepakati;
- Terms/Privacy draft awal tersedia.

## 21.2 Phase 1: Core Foundation
Durasi estimasi: 5-8 hari kerja.

Fitur:
- Laravel setup;
- auth;
- role user/admin;
- database migration utama;
- wallet ledger;
- dashboard user dasar;
- admin panel dasar;
- audit log dasar.

Deliverable:
- user bisa register/login;
- user punya saldo;
- admin bisa melihat user;
- wallet transaction bisa dicatat.

## 21.3 Phase 2: Payment Integration
Durasi estimasi: 4-7 hari kerja.

Fitur:
- top up page;
- create invoice;
- DompetX create payment;
- DompetX webhook;
- idempotency;
- invoice status;
- reconciliation job.

Deliverable:
- user bisa top up sandbox;
- saldo masuk otomatis;
- duplicate webhook aman.

## 21.4 Phase 3: Provider Integration
Durasi estimasi: 6-10 hari kerja.

Fitur:
- SMSBower client;
- sync services;
- sync countries;
- sync prices/stocks;
- provider balance check;
- admin manage service/country;
- pricing margin.

Deliverable:
- katalog tampil;
- harga jual dihitung;
- admin bisa aktif/nonaktif service;
- provider balance tampil.

## 21.5 Phase 4: OTP Order Flow
Durasi estimasi: 7-12 hari kerja.

Fitur:
- order OTP;
- reserve saldo;
- request number;
- detail order;
- polling status;
- webhook SMS;
- cancel order;
- settle/refund wallet;
- order history.

Deliverable:
- user bisa beli nomor;
- kode OTP tampil saat masuk;
- saldo aman;
- cancel/refund berjalan.

## 21.6 Phase 5: Admin, Risk, Support
Durasi estimasi: 5-9 hari kerja.

Fitur:
- admin order monitor;
- admin payment monitor;
- manual refund;
- manual adjustment;
- suspend user;
- risk rules;
- support ticket;
- webhook logs.

Deliverable:
- admin bisa operasional harian;
- ada kontrol anti-abuse;
- ada support basic.

## 21.7 Phase 6: UI Polish dan Legal
Durasi estimasi: 4-7 hari kerja.

Fitur:
- landing page final;
- responsive polish;
- FAQ;
- Terms;
- Privacy;
- Refund Policy;
- Acceptable Use Policy;
- contact page;
- SEO basic.

Deliverable:
- website siap beta publik terbatas.

## 21.8 Phase 7: Testing, Security, Launch
Durasi estimasi: 5-10 hari kerja.

Fitur:
- full UAT;
- payment sandbox test;
- provider small live test;
- load test ringan;
- security review;
- backup test;
- monitoring setup;
- production deploy.

Deliverable:
- public launch.

---

## 22. Go-Live Checklist

## 22.1 Produk
- Landing page selesai.
- Register/login berjalan.
- Top up live berjalan.
- Order OTP live berjalan.
- Cancel/refund berjalan.
- Riwayat transaksi berjalan.
- Admin panel siap.
- Support channel siap.
- FAQ tersedia.

## 22.2 Legal
- Terms of Service publish.
- Privacy Policy publish.
- Refund Policy publish.
- Acceptable Use Policy publish.
- Contact/abuse report tersedia.
- Checkbox persetujuan ToS saat register.

## 22.3 Payment
- DompetX live credential aktif.
- Webhook live URL terdaftar.
- Signature validation aktif.
- Idempotency aktif.
- Reconciliation job aktif.
- Test pembayaran kecil sukses.
- Test duplicate webhook aman.

## 22.4 Provider
- SMSBower API key aktif.
- Provider balance cukup.
- Service/country synced.
- Harga jual sudah benar.
- Test order kecil sukses.
- Test cancel sukses.
- Test SMS webhook/polling sukses.

## 22.5 Security
- APP_DEBUG false.
- HTTPS aktif.
- Admin 2FA aktif.
- Rate limit aktif.
- API key tidak bocor di log.
- Backup aktif.
- Error tracking aktif.
- Uptime monitoring aktif.
- Server firewall aktif.

## 22.6 Operational
- Admin sudah dilatih.
- SOP refund tersedia.
- SOP komplain tersedia.
- SOP provider down tersedia.
- SOP payment mismatch tersedia.
- Alert Telegram/email aktif.
- Kontak support aktif.

---

## 23. SOP Operasional

## 23.1 Jika Payment Sudah Dibayar Tapi Saldo Belum Masuk
1. Admin cari invoice berdasarkan email/user/invoice no.
2. Cek status invoice internal.
3. Cek webhook log.
4. Jalankan reconcile ke DompetX.
5. Jika gateway menyatakan paid dan saldo belum masuk, lakukan process payment manually melalui fitur admin.
6. Catat audit log.
7. Balas user.

## 23.2 Jika OTP Tidak Masuk
1. User diminta menunggu sampai batas waktu.
2. Sistem polling provider.
3. Jika belum masuk dan cancel eligible, user bisa cancel.
4. Jika cancel gagal, admin cek provider status.
5. Refund mengikuti aturan provider dan kebijakan platform.

## 23.3 Jika Provider Down
1. Sistem mendeteksi error rate tinggi.
2. Admin menerima alert.
3. Matikan order sementara atau maintenance mode.
4. Tampilkan banner ke user.
5. Lanjutkan polling order yang sudah aktif.
6. Setelah provider pulih, aktifkan kembali.

## 23.4 Jika Ada User Mencurigakan
1. Sistem memberi flag risk.
2. Admin review order, IP, device, pola top up.
3. Admin bisa suspend sementara.
4. Jika terbukti melanggar ToS, akun diblokir sesuai kebijakan.
5. Simpan catatan audit.

---

## 24. Risk Register

## 24.1 Risiko: Penyalahgunaan Platform
Dampak: reputasi buruk, risiko hukum, provider/payment gateway bermasalah.

Mitigasi:
- Acceptable Use Policy;
- rate limit;
- service blacklist;
- user suspend;
- KYC untuk volume tinggi;
- audit log.

## 24.2 Risiko: Saldo Dobel karena Webhook Retry
Dampak: kerugian finansial.

Mitigasi:
- idempotency key;
- unique constraint event;
- invoice status guard;
- database transaction;
- reconciliation.

## 24.3 Risiko: Provider Stok Kosong
Dampak: order gagal, user kecewa.

Mitigasi:
- sync stok berkala;
- tampilkan status stok;
- fallback provider di V2;
- pesan error jelas.

## 24.4 Risiko: Provider Balance Habis
Dampak: semua order gagal.

Mitigasi:
- balance monitoring;
- threshold alert;
- auto disable order jika balance rendah;
- SOP top up provider.

## 24.5 Risiko: Race Condition Wallet
Dampak: saldo negatif/kehilangan dana.

Mitigasi:
- database transaction;
- row lock;
- ledger immutable;
- test concurrent order.

## 24.6 Risiko: Payment Gateway Gangguan
Dampak: top up gagal.

Mitigasi:
- status page/alert;
- pending invoice tetap bisa reconcile;
- multi payment gateway di V2.

## 24.7 Risiko: Kebocoran API Key
Dampak: penyalahgunaan provider/payment.

Mitigasi:
- .env;
- secret rotation;
- no key in log;
- limited server access;
- git secret scanning.

---

## 25. Analytics dan Reporting

## 25.1 Dashboard Owner
Metric:
- GMV;
- revenue;
- provider cost;
- payment fee;
- profit;
- total top up;
- total order;
- success rate;
- refund rate;
- active users;
- top services;
- top countries.

Filter:
- today;
- yesterday;
- last 7 days;
- last 30 days;
- custom range.

## 25.2 Export
Export CSV/XLSX:
- payment invoices;
- wallet transactions;
- otp orders;
- profit report;
- user list;
- refund report.

## 25.3 Event Tracking
Track event:
- visit landing;
- register;
- login;
- create topup;
- payment paid;
- create order;
- order success;
- order cancel;
- support ticket created.

---

## 26. Notification Requirement

## 26.1 User Notification
Channel:
- in-app notification;
- email opsional.

Trigger:
- top up success;
- OTP received;
- order refunded;
- ticket replied;
- account suspended.

## 26.2 Admin Notification
Channel:
- email;
- Telegram bot;
- Discord webhook opsional.

Trigger:
- provider balance low;
- webhook error spike;
- payment mismatch;
- order error spike;
- suspicious user;
- failed jobs repeated;
- server health alert.

---

## 27. Content Requirement

## 27.1 Landing Copy Draft
Headline:
“Nomor Virtual untuk Kebutuhan Verifikasi yang Cepat dan Terkontrol”

Subheadline:
“Top up saldo, pilih layanan dan negara, lalu terima SMS verifikasi langsung dari dashboard.”

CTA:
- Mulai Sekarang
- Lihat Layanan

Compliance note:
“Gunakan layanan ini hanya untuk kebutuhan yang sah dan sesuai ketentuan platform terkait. Penyalahgunaan dapat menyebabkan akun dibatasi atau ditutup.”

## 27.2 FAQ Minimal
1. Apa itu nomor virtual?
2. Bagaimana cara top up saldo?
3. Berapa lama OTP masuk?
4. Bagaimana jika OTP tidak masuk?
5. Apakah saldo dikembalikan jika gagal?
6. Layanan apa saja yang tersedia?
7. Apakah ada batas order?
8. Apa saja penggunaan yang dilarang?
9. Bagaimana menghubungi support?

---

## 28. Definition of Done

## 28.1 Feature Done
Sebuah fitur dianggap selesai jika:
- requirement terpenuhi;
- validasi input ada;
- error handling ada;
- permission/authorization benar;
- unit/feature test minimal ada untuk logic kritikal;
- UI responsive;
- log/audit tersedia jika fitur finansial/admin;
- sudah diuji di staging;
- tidak ada critical bug.

## 28.2 MVP Done
MVP dianggap selesai jika:
- user bisa daftar;
- user bisa top up via sandbox/live kecil;
- saldo masuk otomatis;
- user bisa beli OTP;
- kode OTP bisa diterima;
- cancel/refund berjalan;
- admin bisa monitor;
- wallet aman dari duplicate webhook;
- Terms/Privacy tersedia;
- staging dan production siap.

## 28.3 Public Launch Done
Public launch dianggap siap jika:
- semua MVP done;
- payment live tested;
- provider live tested;
- monitoring aktif;
- backup aktif;
- security checklist lulus;
- legal pages publish;
- SOP admin siap;
- support channel siap;
- risk control aktif.

---

## 29. Prioritas Backlog

## P0 - Wajib untuk MVP
- Auth user.
- Wallet ledger.
- Top up DompetX.
- Webhook DompetX idempotent.
- SMSBower client.
- Katalog service/country.
- Pricing margin.
- Order OTP.
- Status OTP.
- Cancel/refund.
- Admin basic.
- Audit log.
- Rate limit.
- Legal pages.

## P1 - Wajib untuk Public Launch
- Support ticket.
- Risk scoring.
- Reconciliation payment.
- Provider balance alert.
- Export report.
- Admin 2FA.
- Better UI polish.
- Uptime/error monitoring.
- Backup automation.

## P2 - Setelah Launch
- Referral.
- Voucher.
- Tier pricing.
- Multi-provider.
- API reseller.
- Advanced fraud analytics.
- PWA.

---

## 30. Tim dan Tanggung Jawab

## 30.1 Product Owner
- finalisasi scope;
- prioritasi backlog;
- approve design;
- approve launch.

## 30.2 Backend Developer
- Laravel architecture;
- wallet;
- payment integration;
- provider integration;
- queue/job;
- admin logic;
- tests.

## 30.3 Frontend Developer
- landing page;
- dashboard user;
- order flow;
- responsive UI;
- UX polish.

## 30.4 DevOps
- server setup;
- CI/CD;
- SSL;
- queue worker;
- backup;
- monitoring;
- deployment.

## 30.5 QA
- test scenario;
- regression;
- payment test;
- provider test;
- mobile test;
- bug report.

## 30.6 Admin Operasional
- user support;
- refund;
- monitoring provider;
- review abuse;
- laporan harian.

---

## 31. Estimasi Timeline Global

Estimasi jika dikerjakan oleh 1 backend fullstack + 1 helper UI:

- Phase 0: 2-4 hari
- Phase 1: 5-8 hari
- Phase 2: 4-7 hari
- Phase 3: 6-10 hari
- Phase 4: 7-12 hari
- Phase 5: 5-9 hari
- Phase 6: 4-7 hari
- Phase 7: 5-10 hari

Total estimasi realistis:
- MVP beta: 4-6 minggu.
- Public launch stabil: 6-10 minggu.

Timeline bisa lebih cepat jika memakai Filament, Blade/Tailwind sederhana, dan integrasi API berjalan lancar.

---

## 32. Keputusan Teknis yang Direkomendasikan

1. Gunakan Laravel + Filament untuk mempercepat admin panel.
2. Gunakan Redis queue untuk order/polling/webhook.
3. Gunakan ledger wallet, bukan sekadar update angka saldo tanpa histori.
4. Gunakan database transaction dan row lock untuk semua update saldo.
5. Gunakan idempotency di payment dan webhook.
6. Gunakan service interface untuk SMS provider agar mudah tambah provider lain.
7. Gunakan staging environment sebelum live.
8. Jangan publish umum sebelum legal pages, rate limit, dan audit log aktif.
9. Jangan expose raw API error ke user.
10. Jangan simpan API key atau SMS sensitif di log publik.

---

## 33. Lampiran: Contoh Status Mapping

## 33.1 Payment Status
Internal:
- pending
- paid
- expired
- failed
- cancelled

Mapping gateway perlu disesuaikan dengan response DompetX.

Rule:
- `paid` adalah final success.
- `failed/cancelled/expired` adalah final failed.
- `pending` bisa berubah ke paid/failed/expired.
- status final tidak boleh downgrade.

## 33.2 OTP Order Status
Internal:
- creating
- waiting_sms
- success
- cancelled
- expired
- failed
- refunded

Allowed transition:
- creating -> waiting_sms
- creating -> failed
- waiting_sms -> success
- waiting_sms -> cancelled
- waiting_sms -> expired
- waiting_sms -> failed
- failed -> refunded
- expired -> refunded
- cancelled -> refunded jika saldo belum release otomatis

Blocked transition:
- success -> cancelled oleh user
- refunded -> success
- failed -> success tanpa admin override khusus

---

## 34. Lampiran: Checklist Implementasi Database Index

Index disarankan:
- users.email unique
- users.status
- users.role
- wallet_transactions.user_id
- wallet_transactions.reference_type, reference_id
- payment_invoices.invoice_no unique
- payment_invoices.user_id
- payment_invoices.external_id
- payment_invoices.status
- payment_webhook_events.provider, event_id unique nullable strategy
- services.provider_code
- countries.provider_code
- service_prices.service_id, country_id
- otp_orders.order_no unique
- otp_orders.user_id
- otp_orders.provider_activation_id unique
- otp_orders.status
- otp_orders.created_at
- audit_logs.actor_user_id
- audit_logs.action

---

## 35. Lampiran: Environment Variables

```env
APP_NAME="OTP Virtual Marketplace"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://example.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

SMSBOWER_API_KEY=
SMSBOWER_BASE_URL=
SMSBOWER_WEBHOOK_SECRET=
SMSBOWER_TIMEOUT_SECONDS=20
SMSBOWER_RETRY_ATTEMPTS=2

DOMPETX_BASE_URL=
DOMPETX_API_KEY=
DOMPETX_SECRET_KEY=
DOMPETX_WEBHOOK_SECRET=
DOMPETX_SANDBOX=false
DOMPETX_TIMEOUT_SECONDS=20

OTP_ORDER_TIMEOUT_MINUTES=20
OTP_MAX_ACTIVE_ORDERS_PER_USER=3
OTP_NEW_USER_DAILY_LIMIT=30
PROVIDER_LOW_BALANCE_THRESHOLD=100000

MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME="OTP Virtual Marketplace"
```

---

## 36. Catatan Akhir

PRD ini dibuat untuk membangun platform OTP virtual secara aman, terstruktur, dan siap operasional. Fokus terbesar bukan hanya membuat fitur beli OTP, tetapi memastikan sistem wallet, payment, provider order, refund, webhook, audit, dan anti-abuse berjalan benar.

Sebelum publish umum, prioritas tertinggi adalah:
1. keamanan wallet;
2. idempotency payment;
3. refund/cancel flow;
4. legal policy;
5. audit log;
6. monitoring provider dan payment;
7. kontrol penyalahgunaan.

Dengan fondasi tersebut, produk bisa dimulai dari MVP terbatas lalu diperluas menjadi layanan publik yang lebih stabil dan scalable.

