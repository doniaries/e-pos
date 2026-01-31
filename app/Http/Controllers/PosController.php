<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\Setting;
use App\Services\ThermalPrinterService;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function printStruk($id)
    {
        $penjualan = Penjualan::with(['details.produk', 'pelanggan', 'user', 'pembayaran'])->findOrFail($id);
        $setting = Setting::first();

        return view('print.struk-thermal', compact('penjualan', 'setting'));
    }

    public function testPrint()
    {
        $setting = Setting::first();
        return view('print.test-struk', compact('setting'));
    }

    /**
     * Direct print to thermal printer using ESC/POS
     */
    public function directPrint($id)
    {
        try {
            $penjualan = Penjualan::with(['details.produk', 'pelanggan', 'kasir'])->findOrFail($id);
            $printerService = new ThermalPrinterService();
            $result = $printerService->printReceipt($penjualan);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Print gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test direct print to thermal printer
     */
    public function testDirectPrint()
    {
        try {
            $printerService = new ThermalPrinterService();
            $result = $printerService->testPrint();

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Test print gagal: ' . $e->getMessage()
            ], 500);
        }
    }
}
