@component('legal.layout', ['title' => 'Refund Policy'])
    <h2>Refund Order OTP</h2>
    <p>Jika order gagal dibuat, stok provider habis, provider menolak request, order kadaluarsa, atau order berhasil dibatalkan sebelum kode diterima, saldo tertahan akan dikembalikan sesuai status order.</p>

    <h2>Order Berhasil</h2>
    <p>Order yang sudah menerima kode OTP dan berstatus sukses umumnya tidak dapat dibatalkan. Refund manual hanya dilakukan jika admin menyetujui berdasarkan bukti operasional yang jelas.</p>

    <h2>Top Up Saldo</h2>
    <p>Top up yang sudah masuk ke wallet digunakan sebagai saldo layanan. Permintaan pengembalian dana ke metode pembayaran awal akan ditinjau manual dan dapat bergantung pada kebijakan DompetX serta status penggunaan saldo.</p>

    <h2>Invoice Kadaluarsa atau Gagal</h2>
    <p>Invoice yang kadaluarsa, gagal, atau dibatalkan tidak menambah saldo. Pengguna dapat membuat invoice baru dari halaman Top Up.</p>

    <h2>Proses Review</h2>
    <p>Admin dapat meminta order ID, invoice ID, waktu transaksi, dan bukti pembayaran untuk meninjau refund. Semua refund manual wajib tercatat di audit log.</p>
@endcomponent
