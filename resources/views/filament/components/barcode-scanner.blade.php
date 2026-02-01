<div
    x-data="{
        html5QrCode: null,
        isScanning: false,
        errorMessage: '',
        async checkCameraPermission() {
            try {
                // Request camera permission explicitly
                const stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { facingMode: 'environment' } 
                });
                // Stop the stream immediately after getting permission
                stream.getTracks().forEach(track => track.stop());
                return true;
            } catch (err) {
                console.error('Camera permission error:', err);
                if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                    this.errorMessage = 'Izin kamera ditolak. Silakan izinkan akses kamera di pengaturan browser Anda.';
                } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
                    this.errorMessage = 'Kamera tidak ditemukan. Pastikan perangkat memiliki kamera.';
                } else if (err.name === 'NotReadableError' || err.name === 'TrackStartError') {
                    this.errorMessage = 'Kamera sedang digunakan oleh aplikasi lain. Tutup aplikasi lain yang menggunakan kamera.';
                } else if (err.name === 'OverconstrainedError') {
                    this.errorMessage = 'Kamera tidak mendukung pengaturan yang diminta.';
                } else if (err.name === 'NotSupportedError') {
                    this.errorMessage = 'Browser tidak mendukung akses kamera. Gunakan HTTPS atau browser yang lebih baru.';
                } else {
                    this.errorMessage = 'Gagal mengakses kamera: ' + err.message;
                }
                alert(this.errorMessage);
                return false;
            }
        },
        async initScanner() {
            // Check camera permission first
            const hasPermission = await this.checkCameraPermission();
            if (!hasPermission) {
                return;
            }

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

            try {
                this.html5QrCode = new Html5Qrcode('camera-reader');
                const config = { 
                    fps: 10, 
                    qrbox: { width: 250, height: 150 },
                    aspectRatio: 1.777778
                };

                await this.html5QrCode.start(
                    { facingMode: 'environment' },
                    config,
                    (decodedText) => {
                        this.stopScanner();
                        window.dispatchEvent(new CustomEvent('barcode-detected', { detail: { code: decodedText } }));
                        $dispatch('close-modal', { id: 'fi-modal' });
                    },
                    (errorMessage) => { /* ignore scanning errors */ }
                );
                this.isScanning = true;
            } catch (err) {
                console.error('Unable to start scanning', err);
                this.errorMessage = 'Gagal memulai scanner. Pastikan izin kamera telah diberikan dan browser mendukung fitur ini.';
                alert(this.errorMessage);
            }
        },
        async stopScanner() {
            if (this.html5QrCode && this.isScanning) {
                try {
                    await this.html5QrCode.stop();
                    this.html5QrCode.clear();
                } catch (e) {
                    console.error('Error stopping scanner:', e);
                }
                this.isScanning = false;
            }
        }
    }"
    x-init="initScanner()"
    @close-modal.window="stopScanner()"
    class="flex flex-col items-center justify-center p-4">
    <div id="camera-reader" class="w-full max-w-md overflow-hidden rounded-lg shadow-lg border-4 border-gray-200 bg-black" style="min-height: 250px;"></div>
    <div class="mt-4 text-center text-sm text-gray-600">
        <p class="font-medium text-primary-600 italic">Posisikan barcode di tengah kotak kamera</p>
        <p class="mt-2 text-xs text-gray-400">Gunakan browser Chrome/Safari terbaru untuk performa terbaik.</p>
        <p class="mt-2 text-xs text-orange-500 font-semibold">⚠️ Pastikan Anda mengizinkan akses kamera saat diminta oleh browser</p>
    </div>
</div>