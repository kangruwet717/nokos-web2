@component('legal.layout', ['title' => 'Contact and Abuse Report'])
    <h2>Kontak Support</h2>
    <p>Untuk bantuan pembayaran, order OTP, refund, akun, atau pertanyaan umum, hubungi tim support melalui email operasional yang tersedia di bawah ini.</p>

    <div class="not-prose mt-4 rounded-lg border border-slate-200 bg-slate-50 p-4">
        <div class="text-sm font-semibold text-slate-500">Email support</div>
        <a href="mailto:{{ config('mail.from.address') }}" class="mt-1 inline-block break-all text-lg font-bold text-emerald-700">{{ config('mail.from.address') }}</a>
    </div>

    <h2>Abuse Report</h2>
    <p>Jika Anda menemukan dugaan penyalahgunaan layanan, kirim laporan dengan detail waktu kejadian, invoice atau order ID jika ada, email akun terkait jika diketahui, dan deskripsi kejadian.</p>

    <h2>Informasi yang Membantu</h2>
    <ul>
        <li>Order ID atau invoice ID.</li>
        <li>Email akun yang digunakan.</li>
        <li>Waktu kejadian dan bukti pendukung.</li>
        <li>Jenis masalah: payment, OTP, refund, akun, atau abuse.</li>
    </ul>
@endcomponent
