<div
    x-data="{
        html5QrCode: null,
        isScanning: false,
        async initScanner() {
            if (typeof Html5Qrcode === 'undefined') {
                const script = document.createElement('script');
                script.src = 'https://unpkg.com/html5-qrcode/html5-qrcode.min.js';
                script.onload = () => this.startScanner();
                document.head.appendChild(script);
            } else {
                await this.startScanner();
            }
        },
        async startScanner() {
            if (this.isScanning) return;

            this.html5QrCode = new Html5Qrcode('camera-reader');
            const config = { fps: 10, qrbox: { width: 250, height: 150 } };

            try {
                await this.html5QrCode.start(
                    { facingMode: 'environment' },
                    config,
                    (decodedText) => {
                        this.stopScanner();
                        window.dispatchEvent(new CustomEvent('barcode-detected', { detail: { code: decodedText } }));
                        $dispatch('close-modal', { id: 'fi-modal' });
                    },
                    (errorMessage) => { /* ignore errors */ }
                );
                this.isScanning = true;
            } catch (err) {
                console.error('Unable to start scanning', err);
                alert('Gagal mengakses kamera. Pastikan izin kamera telah diberikan.');
            }
        },
        async stopScanner() {
            if (this.html5QrCode && this.isScanning) {
                try {
                    await this.html5QrCode.stop();
                } catch (e) {
                    // ignore
                }
                this.isScanning = false;
            }
        }
    }"
    x-init="initScanner()"
    class="flex flex-col items-center justify-center p-4">
    <div id="camera-reader" class="w-full max-w-md overflow-hidden rounded-lg shadow-lg border-4 border-gray-200 bg-black" style="min-height: 250px;"></div>
    <div class="mt-4 text-center text-sm text-gray-600">
        <p class="font-medium text-primary-600 italic">Posisikan barcode di tengah kotak kamera</p>
        <p class="mt-2 text-xs text-gray-400">Gunakan browser Chrome/Safari terbaru untuk performa terbaik.</p>
    </div>
</div>