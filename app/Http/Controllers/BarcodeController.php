<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Picqer\Barcode\BarcodeGeneratorPNG;

class BarcodeController extends Controller
{
    public function printLabel103(Request $request)
    {
        // Get product IDs from request (comma separated or array)
        $productIds = is_array($request->products)
            ? $request->products
            : explode(',', $request->products);

        // Fetch products
        $products = Produk::whereIn('id', $productIds)->get();

        if ($products->isEmpty()) {
            abort(404, 'Produk tidak ditemukan');
        }

        // If only 1 product, duplicate it to fill all 12 labels (3x4)
        if ($products->count() === 1) {
            $singleProduct = $products->first();
            $products = collect(array_fill(0, 12, $singleProduct));
        }

        // Generate barcodes
        $generator = new BarcodeGeneratorPNG();
        $barcodes = [];

        foreach ($products as $product) {
            $barcodeData = $product->kode_produk;
            $barcodeImage = base64_encode($generator->getBarcode(
                $barcodeData,
                $generator::TYPE_CODE_128,
                3,
                50
            ));

            $barcodes[] = [
                'image' => $barcodeImage,
                'code' => $barcodeData,
                'name' => $product->nama,
                'price' => 'Rp ' . number_format($product->harga_jual, 0, ',', '.'),
            ];
        }

        // Load PDF view (convert to collection for blade compatibility)
        $pdf = Pdf::loadView('barcode.label-103', [
            'barcodes' => collect($barcodes)
        ]);

        // Set custom paper size for label 103 (width x height in mm)
        // Standar Label 103: 3 kolom x 4 baris
        // Margin atas 9mm + (4 x 38mm pitch) = 161mm
        $pdf->setPaper([0, 0, 595.28, 456.38], 'portrait'); // 210mm x 161mm

        // Return PDF
        return $pdf->stream('barcode-label-103.pdf');
    }
}
