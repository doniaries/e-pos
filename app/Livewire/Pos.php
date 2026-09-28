<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;
use App\Models\Pembayaran;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Produk;
use App\Services\ThermalPrinterService;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

class Pos extends Component
{
    use WithPagination;
    use \Livewire\WithFileUploads;

    // public $scanBarcode = ''; // Removed: using searchQuery for everything now
    public $cart = [];
    public $grandTotal = 0;
    public $customerType = 'umum';
    public $customer = null;
    public $paymentMethod = 'tunai';
    public $payment = 0;
    public $change = 0;
    public $showPaymentModal = false;
    public $showWarningOldTransactions = false;

    // New payment details
    public $paymentReference = '';
    public $paymentProof;

    // public $produks; // Removed to improve performance (now a computed property)
    public $showProductTable = false;
    public $showHistoryModal = false;
    public $showPendingModal = false;
    public $showCloseDayModal = false;

    public $searchQuery = '';
    public $searchResults = [];
    public $modalSearchQuery = '';

    public $activeShiftId;
    public $showShiftModal = false;

    public $printerConnectionMode = 'usb'; // default

    public function togglePrinterMode($mode)
    {
        if (in_array($mode, ['usb', 'bluetooth'])) {
            $this->printerConnectionMode = $mode;
            session()->put('printer_mode', $mode);
            // Optionally update database setting if user has permission?
            // For now, session based is safer for quick toggling.
        }
    }

    public function updatedSearchQuery()
    {
        // For partial search as-you-type, we only populate results.
        // We do NOT auto-add here to prevent double-adding during fast scans.
        if (empty($this->searchQuery)) {
            $this->searchResults = [];
            return;
        }

        $this->searchResults = Produk::where('kode_produk', 'like', '%' . $this->searchQuery . '%')
            ->orWhere('nama', 'like', '%' . $this->searchQuery . '%')
            ->limit(10)
            ->get();
    }

    public function performSearch($query = null)
    {
        $searchVal = $query ?? $this->searchQuery;

        if (empty($searchVal)) {
            $this->searchResults = [];
            return;
        }

        // Exact match check for direct submission (e.g., from Scanner or Enter key)
        $exactProduct = Produk::where('kode_produk', trim($searchVal))->first();

        if ($exactProduct) {
            $this->addToCart($exactProduct);
            $this->searchQuery = '';
            $this->searchResults = [];
            $this->dispatch('reset-search');
            return;
        }

        // If not exact match, just update the search results list
        $this->searchQuery = $searchVal;
        $this->updatedSearchQuery();
    }

    public function selectProduct($productId)
    {
        $this->addToCart($productId);
        $this->searchQuery = '';
        $this->searchResults = [];
        $this->dispatch('reset-search');
    }

    public function updatedModalSearchQuery()
    {
        // No longer need loadProducts() as it's computed
    }


    #[Computed]
    public function produks()
    {
        $query = Produk::query();

        if (!empty($this->modalSearchQuery)) {
            $query->where(function ($q) {
                $q->where('kode_produk', 'like', '%' . $this->modalSearchQuery . '%')
                    ->orWhere('nama', 'like', '%' . $this->modalSearchQuery . '%');
            });
        }

        return $query->limit(50)->get();
    }

    #[Computed]
    public function availableShifts()
    {
        return \App\Models\Shift::orderBy('jam_mulai')->get();
    }

    #[Computed]
    public function currentShift()
    {
        return $this->activeShiftId ? \App\Models\Shift::find($this->activeShiftId) : null;
    }


    public function toggleProductTable()
    {
        $this->showProductTable = !$this->showProductTable;

        if ($this->showProductTable) {
            $this->dispatch('reset-search'); // Focus modal search
        } else {
            $this->dispatch('modal-closed'); // Focus main search
        }
    }

    public function updated($field)
    {
        if ($field === 'payment') {
            $this->calculateChange();
        }
    }

    public function addToCart($productOrId)
    {
        $product = $productOrId instanceof Produk
            ? $productOrId
            : Produk::find($productOrId);

        if (!$product) return;

        if ($product->stok <= 0) {
            $this->dispatch('pos-error', [
                'message' => 'Stok produk "' . $product->nama . '" sudah habis!'
            ]);
            return;
        }

        $productId = $product->id;
        $newItem = [];
        $isNewProduct = !isset($this->cart[$productId]);

        if (isset($this->cart[$productId])) {
            if ($this->cart[$productId]['quantity'] < $product->stok) {
                $newItem = $this->cart[$productId];
                $newItem['quantity']++;
                $newItem['subtotal'] = $newItem['quantity'] * $newItem['price'];
            } else {
                $this->dispatch('pos-error', [
                    'message' => 'Jumlah produk "' . $product->nama . '" melebihi stok yang tersedia!'
                ]);
                return; // Stock limit reached
            }
        } else {
            $newItem = [
                'id' => $product->id,
                'kode_produk' => $product->kode_produk,
                'name' => $product->nama,
                'price' => $product->harga_jual,
                'quantity' => 1,
                'subtotal' => $product->harga_jual,
                'satuan_id' => $product->satuan_id,
                'catatan' => null
            ];
        }

        // Remove existing item to re-insert at top
        unset($this->cart[$productId]);

        // Prepend to cart (Union operator maintains keys and puts left-hand side first)
        $this->cart = [$productId => $newItem] + $this->cart;

        $this->calculateTotal();
        // $this->showProductTable = false; // Keep modal open

        // Dispatch with product details
        $this->dispatch('product-added', [
            'name' => $newItem['name'],
            'quantity' => $newItem['quantity'],
            'isNew' => $isNewProduct
        ]);

        $this->modalSearchQuery = '';
        $this->searchQuery = '';
        $this->searchResults = [];
        $this->dispatch('reset-search');
    }

    public function incrementQty($productId)
    {
        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['quantity']++;
            $this->cart[$productId]['subtotal'] = $this->cart[$productId]['quantity'] * $this->cart[$productId]['price'];
            $this->calculateTotal();
            $this->dispatch('cart-updated');
        }
    }

    public function decrementQty($productId)
    {
        if (isset($this->cart[$productId])) {
            if ($this->cart[$productId]['quantity'] > 1) {
                $this->cart[$productId]['quantity']--;
                $this->cart[$productId]['subtotal'] = $this->cart[$productId]['quantity'] * $this->cart[$productId]['price'];
            } else {
                unset($this->cart[$productId]);
            }
            $this->calculateTotal();
            $this->dispatch('cart-updated');
        }
    }

    public function updateItemNote($productId, $note)
    {
        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['catatan'] = $note;
            $this->dispatch('cart-updated');
        }
    }

    public function updateQuantity($productId, $change)
    {
        if (!isset($this->cart[$productId])) return;

        $newQty = $this->cart[$productId]['quantity'] + $change;

        if ($newQty > 0) {
            $product = Produk::find($productId);
            if ($product && $newQty > $product->stok) {
                $this->dispatch('pos-error', [
                    'message' => 'Stok produk "' . ($product->nama ?? 'Item') . '" tidak mencukupi (Sisa: ' . $product->stok . ')!'
                ]);
                return;
            }
            $this->cart[$productId]['quantity'] = $newQty;
            $this->cart[$productId]['subtotal'] = $this->cart[$productId]['quantity'] * $this->cart[$productId]['price'];
        } else {
            unset($this->cart[$productId]);
        }

        $this->calculateTotal();
        $this->dispatch('cart-updated');
    }

    public function mount()
    {
        // Check for unclosed transactions from previous days
        $oldUnclosedTransactions = Penjualan::whereNull('laporan_harian_id')
            ->where('status', 'selesai')
            ->where('created_at', '<', today())
            ->exists();

        if ($oldUnclosedTransactions) {
            $this->showWarningOldTransactions = true;
            Log::warning('POS: Found old unclosed transactions from previous days.'); // Added logging
        }

        // No longer need to pre-load products into public property

        // Load printer mode from session or setting
        $this->printerConnectionMode = session('printer_mode', Setting::first()->printer_koneksi ?? 'usb');
        // If stored setting is incompatible (e.g. browser), default to usb or map it
        if (!in_array($this->printerConnectionMode, ['usb', 'bluetooth'])) {
            $this->printerConnectionMode = 'usb';
        }

        // Initialize Shift
        $this->activeShiftId = \App\Helpers\ShiftHelper::getActiveShiftId();
        if (!$this->activeShiftId) {
            $shift = \App\Helpers\ShiftHelper::determineShiftByTime();
            if ($shift) {
                $this->activeShiftId = $shift->id;
                \App\Helpers\ShiftHelper::setActiveShift($this->activeShiftId);
            }
        }
    }

    public function changeShift($shiftId)
    {
        $this->activeShiftId = $shiftId;
        \App\Helpers\ShiftHelper::setActiveShift($shiftId);
        $this->showShiftModal = false;

        $shift = \App\Models\Shift::find($shiftId);
        $this->dispatch('pos-notification', [
            'message' => 'Shift diubah ke: ' . ($shift->nama ?? 'N/A')
        ]);
    }

    public function removeFromCart($productId)
    {
        unset($this->cart[$productId]);
        $this->calculateTotal();
        $this->dispatch('cart-updated');
    }

    public function calculateTotal()
    {
        $this->grandTotal = array_sum(array_column($this->cart, 'subtotal'));
        $this->calculateChange();
    }

    public function calculateChange()
    {
        $payment = intval($this->payment); // Pastikan konversi ke integer
        $this->change = max(0, $payment - $this->grandTotal);
    }

    public function processSale()
    {
        if (!$this->canProcessSale()) {
            session()->flash('error', 'Pastikan pembayaran cukup dan keranjang tidak kosong');
            return;
        }

        try {
            // [S6 Fix] Upload file SEBELUM transaksi DB dimulai.
            // Jika upload gagal, tidak ada transaksi setengah jadi.
            // Jika DB rollback setelah ini, file akan dibersihkan secara terpisah
            // (atau gunakan queue job untuk cleanup yatim piatu).
            $buktiPath = null;
            if ($this->paymentProof) {
                $buktiPath = $this->paymentProof->store('bukti_pembayaran', 'public');
            }

            $retryCount = 0;
            $maxRetries = 3;
            $penjualan  = null;

            while ($retryCount < $maxRetries) {
                try {
                    DB::beginTransaction();

                    $paymentStatus = $this->calculatePaymentStatus();
                    $customerId    = $this->customerType === 'pelanggan' ? $this->customer : null;

                    // Cek apakah ini transaksi baru atau melanjutkan draft
                    if ($this->activeSaleId) {
                        $penjualan = Penjualan::find($this->activeSaleId);
                        $penjualan->update([
                            'pelanggan_id'      => $customerId,
                            'subtotal'          => $this->grandTotal,
                            'total'             => $this->grandTotal,
                            'bayar'             => $this->payment,
                            'kembali'           => $this->change,
                            'metode_pembayaran' => strtolower($this->paymentMethod),
                            'nama_bank'         => $this->paymentReference,
                            'status_pembayaran' => $paymentStatus,
                            'status'            => Penjualan::STATUS_COMPLETED,
                            'user_id'           => auth()->id(),
                        ]);
                        $penjualan->details()->delete();
                    } else {
                        $penjualan = Penjualan::create([
                            'nomor'             => Penjualan::generateNomor(), // [Bug #4] generateNomor kini lockForUpdate
                            'user_id'           => auth()->id(),
                            'shift_id'          => $this->activeShiftId,
                            'pelanggan_id'      => $customerId,
                            'subtotal'          => $this->grandTotal,
                            'diskon_persen'     => 0,
                            'diskon_nilai'      => 0,
                            'pajak_persen'      => 0,
                            'pajak_nilai'       => 0, // [S2 Fix] Kini masuk $fillable, tidak diabaikan lagi
                            'total'             => $this->grandTotal,
                            'bayar'             => $this->payment,
                            'kembali'           => $this->change,
                            'metode_pembayaran' => strtolower($this->paymentMethod),
                            'nama_bank'         => $this->paymentReference,
                            'status_pembayaran' => $paymentStatus,
                            'status'            => Penjualan::STATUS_COMPLETED,
                        ]);
                    }

                    // Update hutang pelanggan jika belum lunas
                    if ($paymentStatus !== Penjualan::PAYMENT_PAID) {
                        $this->updateCustomerDebt($customerId, $this->grandTotal, $this->payment);
                    }

                    foreach ($this->cart as $productId => $item) {
                        // [Bug #4 Fix] lockForUpdate mencegah oversell oleh dua kasir bersamaan
                        $produk = Produk::lockForUpdate()->findOrFail($productId);

                        if ($produk->stok < $item['quantity']) {
                            throw new \Exception("Stok {$produk->nama} tidak mencukupi! Sisa: {$produk->stok}");
                        }

                        // [S3 Fix] Simpan snapshot nama & harga modal saat transaksi
                        PenjualanDetail::create([
                            'penjualan_id'  => $penjualan->id,
                            'produk_id'     => $productId,
                            'nama_produk'   => $produk->nama,       // Snapshot
                            'harga_beli'    => $produk->harga_beli, // Snapshot modal
                            'jumlah'        => $item['quantity'],
                            'satuan_id'     => $item['satuan_id'],
                            'harga'         => $item['price'],
                            'diskon_persen' => 0,
                            'diskon_nilai'  => 0,
                            'subtotal'      => $item['subtotal'],
                            'catatan'       => $item['catatan'] ?? null,
                        ]);

                        // [Bug #2 & #3 Fix] Catat ke ledger stoks (negatif = keluar)
                        // StokObserver::created() akan update produks.stok secara otomatis
                        // PenjualanObserver::deleted() akan hapus baris ini → StokObserver::deleted()
                        // menjalankan decrement(-qty) = increment(qty) → stok kembali saat void
                        \App\Models\Stok::create([
                            'produk_id'      => $productId,
                            'jenis'          => 'penjualan',
                            'jumlah'         => -$item['quantity'], // Negatif = stok keluar
                            'referensi_type' => Penjualan::class,
                            'referensi_id'   => $penjualan->id,
                            'keterangan'     => 'Penjualan POS #' . $penjualan->nomor,
                            'user_id'        => auth()->id(),
                            'shift_id'       => $this->activeShiftId,
                        ]);
                    }

                    Pembayaran::create([
                        'penjualan_id'   => $penjualan->id,
                        'metode'         => strtolower($this->paymentMethod),
                        'jumlah'         => $this->payment,
                        'catatan'        => 'Pembayaran POS',
                        'keterangan'     => $this->paymentReference,
                        'bukti_pembayaran' => $buktiPath, // [S6] Path sudah ada dari sebelum transaksi
                    ]);

                    DB::commit();
                    break; // Berhasil, keluar dari loop retry

                } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                    // [Bug #4 Fix] Portable exception (Laravel 10+), tidak pakai errorInfo[1] == 1062 MySQL-specific
                    DB::rollBack();
                    if ($retryCount < $maxRetries - 1) {
                        $retryCount++;
                        usleep(100000 * $retryCount); // Backoff bertahap
                        continue;
                    }
                    throw new \Exception('Gagal membuat nomor nota unik. Coba lagi.');
                } catch (\Exception $e) {
                    DB::rollBack();
                    throw $e;
                }
            }

            $this->showPaymentModal = false;
            $this->resetCart();
            $this->dispatch('transaction-success', [
                'nomor' => $penjualan->nomor ?? '',
            ]);

        } catch (\Exception $e) {
            // [S7 Fix] Log error internal, tampilkan pesan yang ramah ke kasir
            \Illuminate\Support\Facades\Log::error('POS processSale error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'cart'    => $this->cart,
            ]);
            session()->flash('error', 'Transaksi gagal: ' . $e->getMessage());
        }
    }


    public function render()
    {
        $setting = \App\Models\Setting::first();

        return view('livewire.pos', compact('setting'))
            ->layout('layouts.pos-layout');
    }

    public function canProcessSale()
    {
        // Allow processing even with partial or no payment for registered customers
        if (!empty($this->cart)) {
            // If umum customer, require full payment
            if ($this->customerType === 'umum') {
                return $this->payment >= $this->grandTotal;
            }
            // If registered customer (pelanggan), allow any payment amount
            return true;
        }
        return false;
    }

    private function calculatePaymentStatus()
    {
        if ($this->payment >= $this->grandTotal) {
            return Penjualan::PAYMENT_PAID;
        } elseif ($this->payment > 0) {
            return Penjualan::PAYMENT_PARTIAL;
        } else {
            return Penjualan::PAYMENT_UNPAID;
        }
    }

    private function updateCustomerDebt($customerId, $totalAmount, $paidAmount)
    {
        if (!$customerId) return;

        $debtAmount = $totalAmount - $paidAmount;
        if ($debtAmount > 0) {
            \App\Models\Pelanggan::findOrFail($customerId)->increment('hutang', $debtAmount, [
                'tanggal_hutang_terakhir' => now()
            ]);
        }
    }

    public function resetCart()
    {
        $this->reset(['cart', 'grandTotal', 'payment', 'change', 'customerType', 'customer', 'activeSaleId', 'paymentReference', 'paymentProof']);
    }

    public function updatedPaymentMethod($value)
    {
        if ($value !== 'tunai') {
            $this->payment = $this->grandTotal;
        }
    }

    public function updatedPayment() // Tambahkan method ini
    {
        $this->calculateChange();
    }

    private function validateStock($productId, $qty)
    {
        $product = Produk::find($productId);
        if (!$product || $product->stok < $qty) {
            throw new \Exception("Stok produk {$product->nama} tidak mencukupi!");
        }
        return $product;
    }
    // Close Day Properties
    public $closeDaySummary = [
        'count' => 0,
        'omset' => 0,
        'cash' => 0,
        'non_cash' => 0,
        'cash_in_drawer' => 0, // Input manual
    ];

    public function openCloseDayModal()
    {
        // Calculate pending report sales
        $pendingSales = Penjualan::whereNull('laporan_harian_id')
            ->where('status', 'selesai')
            ->with('pembayarans')
            ->get();

        // Calculate cash and non-cash from pembayarans table
        $totalCash = 0;
        $totalNonCash = 0;

        foreach ($pendingSales as $sale) {
            if ($sale->pembayarans->count() > 0) {
                $cashTendered = 0;
                $nonCashTendered = 0;

                foreach ($sale->pembayarans as $payment) {
                    if (strtolower($payment->metode) === 'tunai') {
                        $cashTendered += $payment->jumlah;
                    } else {
                        $nonCashTendered += $payment->jumlah;
                    }
                }

                // Kurangi kembalian dari total uang tunai yang diterima
                // Kembalian selalu diambil dari uang tunai
                $netCash = max(0, $cashTendered - $sale->kembali);

                $totalCash += $netCash;
                $totalNonCash += $nonCashTendered;
            } else {
                // Fallback: Gunakan total transaksi (asumsi pas)
                if (strtolower($sale->metode_pembayaran) === 'tunai') {
                    $totalCash += $sale->total;
                } else {
                    $totalNonCash += $sale->total;
                }
            }
        }

        $this->closeDaySummary = [
            'count' => $pendingSales->count(),
            'omset' => $pendingSales->sum('total'),
            'cash' => $totalCash,
            'non_cash' => $totalNonCash,
            'cash_in_drawer' => 0,
        ];

        $this->showCloseDayModal = true;
    }

    public function processCloseDay()
    {
        try {
            DB::beginTransaction();

            // Get Sales Again to be safe (Concurrency check ideally needed but OK for now)
            $pendingSales = Penjualan::whereNull('laporan_harian_id')
                ->where('status', 'selesai')
                ->lockForUpdate()
                ->get();

            if ($pendingSales->isEmpty()) {
                session()->flash('error', 'Tidak ada transaksi penjualan untuk ditutup.');
                $this->showCloseDayModal = false;
                DB::rollBack();
                return;
            }

            // Create Laporan Harian
            $laporan = \App\Models\LaporanHarian::create([
                'tanggal' => now()->toDateString(),
                'waktu_tutup' => now(),
                'user_id' => auth()->id(),
                'jumlah_transaksi' => $this->closeDaySummary['count'],
                'total_omset' => $this->closeDaySummary['omset'],
                'total_tunai' => $this->closeDaySummary['cash'],
                'total_nontunai' => $this->closeDaySummary['non_cash'],
                'uang_tunai_di_laci' => $this->closeDaySummary['cash_in_drawer'],
                'selisih' => $this->closeDaySummary['cash'] - $this->closeDaySummary['cash_in_drawer'],
                'catatan' => 'Tutup Hari dari POS'
            ]);

            // Hubungkan semua penjualan yang belum dilaporkan ke laporan baru ini
            Penjualan::whereNull('laporan_harian_id')
                ->where('status', 'selesai')
                ->update(['laporan_harian_id' => $laporan->id]);

            DB::commit();

            $this->showCloseDayModal = false;

            // Generate PDF untuk otomatis didownload sebagai bukti tutup hari
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.laporan-harian-detail', [
                'record' => $laporan,
                'setting' => \App\Models\Setting::first()
            ]);

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, "laporan-harian-" . now()->format('Y-m-d') . ".pdf");
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('POS tutupHari error: ' . $e->getMessage(), ['user_id' => auth()->id()]);
            session()->flash('error', 'Tutup Hari gagal. Silakan coba lagi atau hubungi admin.');
        }
    }
    #[Computed]
    public function pelangganList()
    {
        return \App\Models\Pelanggan::orderBy('nama')->get();
    }

    public $activeSaleId = null;

    #[Computed]
    public function pendingTransactions()
    {
        return Penjualan::where('user_id', auth()->id())
            ->where('status', 'draft')
            ->latest()
            ->get();
    }

    #[Computed]
    public function recentTransactions()
    {
        return Penjualan::where('user_id', auth()->id())
            ->where('status', 'selesai')
            ->where('created_at', '>=', today())
            ->with(['pelanggan'])
            ->withSum('details as item_count', 'jumlah')
            ->latest()
            ->paginate(10, ['*'], 'historyPage');
    }

    #[Computed]
    public function todayTotalTransactions()
    {
        return Penjualan::where('user_id', auth()->id())
            ->where('status', 'selesai')
            ->where('created_at', '>=', today())
            ->count();
    }

    #[Computed]
    public function todayTotalRevenue()
    {
        return Penjualan::where('user_id', auth()->id())
            ->where('status', 'selesai')
            ->where('created_at', '>=', today())
            ->sum('total');
    }

    public function openPendingModal()
    {
        $this->showPendingModal = true;
    }

    public function openHistoryModal()
    {
        $this->showHistoryModal = true;
    }

    public function pendingTransaction()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Keranjang kosong, tidak bisa pending.');
            return;
        }

        try {
            DB::beginTransaction();

            if ($this->activeSaleId) {
                // Jika sudah ada ID aktif (melanjutkan draft), update draft yang ada
                $penjualan = Penjualan::find($this->activeSaleId);
                $penjualan->update([
                    'pelanggan_id' => $this->customerType === 'pelanggan' ? $this->customer : null,
                    'subtotal' => $this->grandTotal,
                    'total' => $this->grandTotal,
                    'catatan' => 'Updated Pending Transaction'
                ]);
                // Hapus detail lama agar tidak duplikat saat disimpan kembali
                $penjualan->details()->delete();
            } else {
                // Jika tunda belanja baru, buat record draft baru
                $penjualan = Penjualan::create([
                    'nomor' => Penjualan::generateNomor(),
                    'user_id' => auth()->id(),
                    'pelanggan_id' => $this->customerType === 'pelanggan' ? $this->customer : null,
                    'subtotal' => $this->grandTotal,
                    'diskon_persen' => 0,
                    'diskon_nilai' => 0,
                    'pajak_persen' => 0,
                    'pajak_nilai' => 0,
                    'total' => $this->grandTotal,
                    'bayar' => 0,
                    'kembali' => 0,
                    'status_pembayaran' => Penjualan::PAYMENT_UNPAID,
                    'status' => Penjualan::STATUS_DRAFT,
                    'catatan' => 'Pending Transaction'
                ]);
            }

            foreach ($this->cart as $productId => $item) {
                PenjualanDetail::create([
                    'penjualan_id' => $penjualan->id,
                    'produk_id' => $productId,
                    'jumlah' => $item['quantity'],
                    'satuan_id' => $item['satuan_id'],
                    'harga' => $item['price'],
                    'diskon_persen' => 0,
                    'diskon_nilai' => 0,
                    'subtotal' => $item['subtotal']
                ]);
                // NOTE: Stock is NOT deducted for drafts, or we can choose to deduct to reserve it.
                // User requirement: "resume later", usually implies reservation or just saving state.
                // Let's NOT deduct stock for now to keep it simple, or deduct?
                // Standard POS: Drafts usually don't deduct until finalized, but "Hold" might reserve.
                // Re-reading request: "pending and can continue new transaction".
                // Safest to NOT deduct stock yet to avoid "stok habis" for real customers if they hold everything.
                // BUT, if they take physical items, stock should be held.
                // Let's NOT deduct for now. Deduction happens at processSale.
            }

            DB::commit();
            $this->resetCart();
            session()->flash('message', 'Transaksi berhasil di-pending.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('POS savePending error: ' . $e->getMessage(), ['user_id' => auth()->id()]);
            session()->flash('error', 'Gagal menyimpan sebagai pending. Silakan coba lagi.');
        }
    }

    public function recallTransaction($id)
    {
        $penjualan = Penjualan::with('details.produk')->find($id);

        if (!$penjualan) {
            session()->flash('error', 'Transaksi tidak ditemukan.');
            return;
        }

        if ($penjualan->status !== 'draft') {
            session()->flash('error', 'Transaksi bukan draft.');
            return;
        }

        $this->resetCart();
        $this->activeSaleId = $penjualan->id;
        $this->customerType = $penjualan->pelanggan_id ? 'pelanggan' : 'umum';
        $this->customer = $penjualan->pelanggan_id;

        foreach ($penjualan->details as $detail) {
            $this->cart[$detail->produk_id] = [
                'id' => $detail->produk_id,
                'kode_produk' => $detail->produk->kode_produk,
                'name' => $detail->produk->nama,
                'price' => $detail->harga,
                'quantity' => $detail->jumlah,
                'subtotal' => $detail->subtotal,
                'satuan_id' => $detail->satuan_id
            ];
        }

        $this->calculateTotal();
        $this->showPendingModal = false;

        // Kami tidak menghapus draft di sini agar ID tetap tersimpan di activeSaleId.
        // Draft akan di-update atau dihapus (diubah statusnya) saat 'Selesai' atau di-tunda lagi.
    }

    public function deletePending($id)
    {
        $penjualan = Penjualan::find($id);
        if ($penjualan && $penjualan->status === 'draft') {
            $penjualan->details()->delete();
            $penjualan->delete();
            $this->showPendingModal = false; // Close after delete
            session()->flash('message', 'Draft dihapus.');
        }
    }

    public function openPaymentModal()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Keranjang kosong, tidak bisa checkout.');
            return;
        }

        $this->showPaymentModal = true;
    }

    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
    }

    public function processAndPrint()
    {
        if (!$this->canProcessSale()) {
            session()->flash('error', 'Pastikan pembayaran cukup dan keranjang tidak kosong');
            return;
        }

        try {
            DB::beginTransaction();

            if ($this->activeSaleId) {
                $penjualan     = Penjualan::find($this->activeSaleId);
                $paymentStatus = $this->calculatePaymentStatus();
                $customerId    = $this->customerType === 'pelanggan' ? $this->customer : null;

                $penjualan->update([
                    'pelanggan_id'      => $customerId,
                    'subtotal'          => $this->grandTotal,
                    'total'             => $this->grandTotal,
                    'bayar'             => $this->payment,
                    'kembali'           => $this->change,
                    'status_pembayaran' => $paymentStatus,
                    'status'            => Penjualan::STATUS_COMPLETED,
                    'user_id'           => auth()->id(),
                ]);

                if ($paymentStatus !== Penjualan::PAYMENT_PAID) {
                    $this->updateCustomerDebt($customerId, $this->grandTotal, $this->payment);
                }
                $penjualan->details()->delete();
            } else {
                $paymentStatus = $this->calculatePaymentStatus();
                $customerId    = $this->customerType === 'pelanggan' ? $this->customer : null;

                $penjualan = Penjualan::create([
                    'nomor'             => Penjualan::generateNomor(), // [Bug #4] lockForUpdate di dalam generateNomor
                    'user_id'           => auth()->id(),
                    'pelanggan_id'      => $customerId,
                    'subtotal'          => $this->grandTotal,
                    'diskon_persen'     => 0,
                    'diskon_nilai'      => 0,
                    'pajak_persen'      => 0,
                    'pajak_nilai'       => 0, // [S2 Fix] Kini ada di $fillable
                    'total'             => $this->grandTotal,
                    'bayar'             => $this->payment,
                    'kembali'           => $this->change,
                    'status_pembayaran' => $paymentStatus,
                    'status'            => Penjualan::STATUS_COMPLETED,
                ]);

                if ($paymentStatus !== Penjualan::PAYMENT_PAID) {
                    $this->updateCustomerDebt($customerId, $this->grandTotal, $this->payment);
                }
            }

            foreach ($this->cart as $productId => $item) {
                // [Bug #4 Fix] lockForUpdate cegah oversell konkuren
                $produk = Produk::lockForUpdate()->findOrFail($productId);

                if ($produk->stok < $item['quantity']) {
                    throw new \Exception("Stok {$produk->nama} tidak mencukupi! Sisa: {$produk->stok}");
                }

                // [S3 Fix] Simpan snapshot nama & harga modal
                PenjualanDetail::create([
                    'penjualan_id'  => $penjualan->id,
                    'produk_id'     => $productId,
                    'nama_produk'   => $produk->nama,
                    'harga_beli'    => $produk->harga_beli,
                    'jumlah'        => $item['quantity'],
                    'satuan_id'     => $item['satuan_id'],
                    'harga'         => $item['price'],
                    'diskon_persen' => 0,
                    'diskon_nilai'  => 0,
                    'subtotal'      => $item['subtotal'],
                ]);

                // [Bug #2 & #3 Fix] Catat ke ledger stoks (negatif = keluar)
                \App\Models\Stok::create([
                    'produk_id'      => $productId,
                    'jenis'          => 'penjualan',
                    'jumlah'         => -$item['quantity'],
                    'referensi_type' => Penjualan::class,
                    'referensi_id'   => $penjualan->id,
                    'keterangan'     => 'Penjualan POS #' . $penjualan->nomor,
                    'user_id'        => auth()->id(),
                ]);
            }

            Pembayaran::create([
                'penjualan_id' => $penjualan->id,
                'metode'       => strtolower($this->paymentMethod),
                'jumlah'       => $this->payment,
                'catatan'      => 'Pembayaran POS',
            ]);

            DB::commit();

            // Cetak struk
            $connectionType = $this->printerConnectionMode;
            Log::info('POS Print Flow - Connection Type: ' . $connectionType);

            if ($connectionType === 'bluetooth') {
                Log::info('POS: Taking Bluetooth print path');
                try {
                    $printerService = new ThermalPrinterService();
                    $binaryData     = $printerService->getReceiptBinary($penjualan);

                    if ($binaryData) {
                        $this->dispatch('print-bluetooth', ['data' => $binaryData]);
                        Log::info('POS: Dispatched print-bluetooth for transaction ' . $penjualan->nomor);
                    } else {
                        throw new \Exception('Gagal menghasilkan data binary struk.');
                    }
                } catch (\Exception $e) {
                    Log::error('Bluetooth print exception: ' . $e->getMessage());
                    $this->dispatch('pos-error', ['message' => 'Gagal cetak Bluetooth: ' . $e->getMessage()]);
                }
            } elseif ($connectionType === 'usb') {
                Log::info('POS: Taking Direct USB print path');
                try {
                    $printerService = new ThermalPrinterService();
                    $result         = $printerService->printReceipt($penjualan);

                    if (!$result['success']) {
                        throw new \Exception($result['message']);
                    }

                    $this->dispatch('transaction-success', ['nomor' => $penjualan->nomor]);
                } catch (\Exception $e) {
                    Log::error('USB Print Error: ' . $e->getMessage());
                    $this->dispatch('pos-error', ['message' => 'Gagal cetak USB: ' . $e->getMessage()]);
                }
            } else {
                Log::info('POS: Taking browser print path');
                $this->dispatch('open-print-window', ['id' => $penjualan->id]);
            }

            $this->showPaymentModal = false;
            $this->resetCart();
            $this->dispatch('transaction-success', [
                'nomor' => $penjualan->nomor ?? '',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            // [S7 Fix] Log detail, tampilkan pesan ramah ke kasir
            Log::error('POS processAndPrint error: ' . $e->getMessage(), ['user_id' => auth()->id()]);
            session()->flash('error', 'Transaksi gagal: ' . $e->getMessage());
        }
    }


    public function reprint($id)
    {
        try {
            $penjualan = Penjualan::with(['details.produk', 'kasir', 'pelanggan'])->findOrFail($id);
            $setting = Setting::first();
            $connectionType = $setting?->printer_koneksi ?? 'browser';

            if (str_contains(strtolower($connectionType), 'bluetooth')) {
                // Bluetooth Flow
                try {
                    $printerService = new ThermalPrinterService();
                    $binaryData = $printerService->getReceiptBinary($penjualan, true);
                    if ($binaryData) {
                        $this->dispatch('print-bluetooth', ['data' => $binaryData]);
                        Log::info('POS: Dispatched print-bluetooth (reprint) for transaction ' . $penjualan->nomor);
                    } else {
                        throw new \Exception('Gagal menghasilkan data binary struk.');
                    }
                } catch (\Exception $e) {
                    Log::error('Bluetooth browser reprint exception: ' . $e->getMessage());
                    $this->dispatch('pos-error', ['message' => 'Gagal cetak Bluetooth: ' . $e->getMessage()]);
                }
            } elseif ($connectionType === 'usb') {
                // USB Direct Print
                try {
                    $printerService = new ThermalPrinterService();
                    $result = $printerService->printReceipt($penjualan, true); // true = isReprint

                    if (!$result['success']) {
                        throw new \Exception($result['message']);
                    }
                    // Success silently or notify?
                    // $this->dispatch('transaction-success', ...); // Maybe not needed for reprint
                } catch (\Exception $e) {
                    Log::error('USB Reprint Error: ' . $e->getMessage());
                    $this->dispatch('pos-error', ['message' => 'Gagal cetak USB: ' . $e->getMessage()]);
                    // Fallback removed to prevent popping up window as per user request
                    // $this->dispatch('open-print-window', ['id' => $penjualan->id]);
                }
            } else {
                // Browser Print
                $this->dispatch('open-print-window', ['id' => $penjualan->id]);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('POS reprint error: ' . $e->getMessage(), ['user_id' => auth()->id()]);
            session()->flash('error', 'Gagal cetak ulang. Silakan coba lagi.');
        }
    }
}
