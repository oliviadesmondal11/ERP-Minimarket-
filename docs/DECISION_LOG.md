# Decision Log — ERP PT RantauMart Riau

Dokumen ini mencatat keputusan arsitektural dan aturan bisnis inti yang disepakati untuk pengembangan sistem ERP Minimarket PT RantauMart Riau.

---

## 1. Kebijakan Penagihan Penjualan B2B (SR-01 vs SR-02)

- **Konteks:**
  Pada penjualan B2B (misalnya pasokan ke mitra ritel atau koperasi rekanan di Riau), barang seringkali dikirim secara bertahap atau memerlukan bukti serah terima (Delivery Order/Surat Jalan) sebelum pembayaran ditagihkan.
- **Opsi yang Dipertimbangkan:**
  - **SR-01 (Invoicing Based on Ordered Qty):** Invoice penagihan langsung diterbitkan sejumlah kuantitas pesanan yang tertera di Sales Order sesaat setelah Sales Order dikonfirmasi, tanpa menunggu barang dikirim.
  - **SR-02 (Invoicing Based on Delivered Qty):** Invoice penagihan pelanggan hanya boleh diterbitkan untuk jumlah barang yang sudah benar-benar terkirim (`Delivered Qty`) dan belum pernah ditagih sebelumnya (`Invoiced Qty`).
- **Keputusan:**
  **Memilih SR-02.** Invoicing mengacu pada Delivered Quantity yang belum ditagih (`Delivered Qty - Invoiced Qty > 0`), terinspirasi dari modul standar *Sales Delivery Invoicing* pada ERP Odoo.
- **Trade-off:**
  - *Kelemahan:* Pengakuan piutang (AR) dan penerimaan kas tidak dapat mendahului pengiriman fisik. Jika divisi logistik lambat memperbarui status Delivery Order, penerbitan invoice akan tertunda.
  - *Keuntungan:* Menghilangkan risiko sengketa faktur (*dispute*), pembatalan tagihan parsial, dan retur faktur akibat barang belum atau tidak terkirim.
- **Pihak yang Terdampak:**
  - **Sales Representative:** Harus memantau status pengiriman sebelum meminta Finance menerbitkan invoice.
  - **Warehouse Staff:** Wajib mencatat realisasi Surat Jalan / Delivery Order tepat waktu.
  - **Finance & Accounting:** Menagih sesuai hak tagih legal atas barang yang sudah diserahterimakan.
  - **Customer (B2B):** Menerima invoice yang akurat dan sesuai fisik barang yang diterima.
- **Mitigasi:**
  - Sediakan filter dashboard *"To Invoice Orders"* yang otomatis memunculkan Sales Order dengan status `delivered_partial` atau `delivered_full` namun `invoiced_qty < delivered_qty`.
  - Notifikasi otomatis dari sistem Gudang ke Finance saat Surat Jalan berstatus `done`.
- **Test / Skenario Terkait:**
  - `T-SO-INV-01`: Mencoba membuat Customer Invoice saat `Delivered Qty = 0` (Hasil: Ditolak dengan pesan validasi).
  - `T-SO-INV-02`: Order 100 pcs, Delivery parsial 40 pcs. Invoice pertama terbit 40 pcs. Sisa yang dapat ditagih menjadi 0 pcs sampai ada delivery berikutnya.

---

## 2. Kebijakan Validasi Stok Point of Sale (POS) Minimarket (SR-03 vs SR-04)

- **Konteks:**
  Operasi kasir ritel di minimarket toko fisik (`STORE-PKU` dan `STORE-DUM`) menghadapi antrean pelanggan yang membutuhkan kecepatan transaksi, namun harus menjaga integritas data stok real-time.
- **Opsi yang Dipertimbangkan:**
  - **SR-03 (Allow Negative Checkout):** Kasir POS tetap diizinkan menyelesaikan checkout meskipun kuantitas barang melebihi stok yang tercatat di sistem (mengasumsikan barang fisik riil ada di keranjang pelanggan), dengan asumsi koreksi stok dilakukan belakangan.
  - **SR-04 (Strict Stock Rejection on Checkout):** Kasir POS menolak checkout jika jumlah barang yang dipindai/diinput melebihi kuantitas `Available` (`On Hand - Reserved`) di lokasi toko yang bersangkutan.
- **Keputusan:**
  **Memilih SR-04.** Sistem POS secara ketat menolak checkout jika kuantitas melebihi `Available Stock` di lokasi toko tersebut. Database memberlakukan `CHECK (on_hand_qty >= 0)`.
- **Trade-off:**
  - *Kelemahan:* Jika terjadi keterlambatan input penerimaan stok toko atau kesalahan opname fisik, kasir tidak dapat men-scan barang yang ada di depan mata sampai stok di sistem disesuaikan.
  - *Keuntungan:* Mencegah stok minus, mencegah kebocoran barang yang tidak tercatat, menjaga akurasi valuasi persediaan, dan mematuhi aturan integritas data baseline ERP.
- **Pihak yang Terdampak:**
  - **Kasir:** Memerlukan mekanisme penanganan cepat jika terjadi barcode error / stok sistem kosong.
  - **Supervisor Toko:** Bertanggung jawab melakukan penyesuaian stok (*Stock Adjustment*) resmi jika ada selisih fisik.
  - **Pelanggan Toko:** Terlindungi dari perbedaan harga atau barang rusak yang belum divalidasi.
- **Mitigasi:**
  - Fitur *"Supervisor Override / Quick Opname"* dengan otorisasi login Supervisor Toko yang mencatat alasan dan jejak audit log lengkap.
  - Peringatan dini di POS saat stok toko berada di bawah batas minimum (*low stock warning*).
- **Test / Skenario Terkait:**
  - `T-POS-STK-01`: Kasir mencoba menjual 10 pcs SKU saat `Available = 5` di `STORE-PKU` (Hasil: Transaksi ditolak, HTTP 400 Bad Request, stok tidak berkurang).
  - `T-POS-STK-02`: Kasir menjual 5 pcs saat `Available = 5` (Hasil: Transaksi sukses, `on_hand_qty` menjadi 0, jurnal kas bertambah, receipt tercetak).

---

## 3. Kebijakan Pencatatan Tagihan Pemasok / Vendor Bill (SR-05 vs SR-06)

- **Konteks:**
  Dalam pengadaan barang dagang dari supplier (misal: CV Sumber Pangan Riau, PT Tirta Riau Distribusi), pengiriman barang sering kali mengalami penyusutan, kemasan rusak, atau barang kadaluwarsa yang ditolak (*Rejected*) saat proses *Goods Receipt / Quality Check*.
- **Opsi yang Dipertimbangkan:**
  - **SR-05 (Bill Upon PO Confirmation):** Vendor Bill dicatat langsung segera setelah Purchase Order (PO) disetujui, mencakup seluruh nominal pesanan.
  - **SR-06 (3-Way Matching: Bill Upon Accepted Qty):** Vendor Bill hanya boleh dibuat dan diposting sejumlah kuantitas barang yang telah diterima di gudang dan lolos inspeksi (`Accepted Qty`) yang belum pernah ditagih sebelumnya. Barang yang berstatus `Rejected Qty` tidak boleh ditagihkan ke hutang usaha.
- **Keputusan:**
  **Memilih SR-06.** Mengadopsi prinsip akuntansi *3-Way Matching* (PO vs Goods Receipt Accepted vs Vendor Bill). Hutang Usaha (AP) hanya diakui atas barang yang sah diterima dan diterima dengan baik.
- **Trade-off:**
  - *Kelemahan:* Bagian Finance tidak dapat mencatat tagihan masuk sebelum tim Gudang menyelesaikan input Goods Receipt dan QC.
  - *Keuntungan:* Menghindari kelebihan bayar (*overpayment*), menghindari sengketa retur pembelian yang rumit, dan memastikan posisi Hutang Usaha (AP) di neraca 100% valid secara hukum.
- **Pihak yang Terdampak:**
  - **Procurement Staff:** Bertanggung jawab memastikan kesepakatan PO sejalan dengan kapasitas gudang.
  - **Warehouse / QC Staff:** Wajib memisahkan jumlah diterima menjadi `accepted_qty` dan `rejected_qty` pada dokumen Receipt.
  - **Finance & Accounting:** Terlindungi dari penagihan barang cacat atau belum terkirim.
  - **Vendor:** Menerima pembayaran yang adil dan transparan sesuai bukti serah terima gudang.
- **Mitigasi:**
  - Form Goods Receipt di gudang memiliki kolom input terpisah untuk `received_qty`, `accepted_qty`, dan `rejected_qty` beserta catatan alasan penolakan.
  - Notifikasi otomatis untuk barang yang ditolak agar bagian Procurement dapat segera meminta penggantian atau nota retur dari supplier.
- **Test / Skenario Terkait:**
  - `T-PO-BILL-01`: PO dipesan 100 pcs. Barang diterima 100 pcs, tetapi QC menyatakan 80 diterima (`accepted_qty = 80`) dan 20 rusak (`rejected_qty = 20`). Finance mencoba membuat Vendor Bill untuk 100 pcs (Hasil: Sistem menolak, tagihan maksimal hanya 80 pcs).
  - `T-PO-BILL-02`: Vendor Bill diterbitkan 80 pcs. Upaya membuat Vendor Bill kedua atas PO tersebut ditolak karena sisa tagihan `accepted_qty - billed_qty = 0`.
