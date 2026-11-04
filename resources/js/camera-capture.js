/**
 * Capture foto bukti langsung dari kamera perangkat (tablet/iPad) memakai
 * getUserMedia — tanpa membuka galeri atau aplikasi kamera bawaan OS.
 *
 * Hasil bidikan dikonversi menjadi File lalu disuntikkan ke input berkas
 * tersembunyi (`photo`) pada form multipart yang sudah ada, sehingga backend
 * dan validasinya tidak perlu diubah.
 *
 * Catatan kompatibilitas perangkat:
 * - getUserMedia hanya tersedia pada secure context (HTTPS atau localhost).
 * - Safari iOS/iPadOS membutuhkan atribut `playsinline`, `muted`, dan `autoplay`
 *   pada elemen <video> agar pratinjau tidak dibuka layar penuh.
 * - Izin kamera wajib dipicu gesture pengguna (tekan tombol), bukan otomatis.
 * - Hasil dikompres ke lebar maksimum dan kualitas JPEG agar tetap di bawah 5 MB.
 */

const MAX_WIDTH = 1280;
const JPEG_QUALITY = 0.85;

/**
 * Tentukan pesan yang ramah pengguna dari galat izin/akses kamera.
 */
function describeCameraError(error) {
    switch (error && error.name) {
        case 'NotAllowedError':
        case 'SecurityError':
            return 'Akses kamera ditolak. Buka Pengaturan perangkat > browser > Kamera, izinkan akses, lalu muat ulang halaman ini.';
        case 'NotFoundError':
        case 'DevicesNotFoundError':
            return 'Kamera tidak ditemukan pada perangkat ini.';
        case 'NotReadableError':
        case 'TrackStartError':
            return 'Kamera sedang dipakai aplikasi lain. Tutup aplikasi tersebut lalu coba lagi.';
        case 'OverconstrainedError':
            return 'Kamera perangkat tidak mendukung mode yang diminta. Coba nyalakan ulang kamera.';
        default:
            return 'Kamera tidak dapat diakses. Periksa izin kamera pada browser lalu coba lagi.';
    }
}

/**
 * Aktifkan komponen capture kamera bila ada di halaman.
 */
export function initCameraCapture() {
    const root = document.querySelector('[data-camera-capture]');

    if (!root) {
        return;
    }

    const video = root.querySelector('[data-camera-video]');
    const canvas = root.querySelector('[data-camera-canvas]');
    const preview = root.querySelector('[data-camera-preview]');
    const placeholder = root.querySelector('[data-camera-placeholder]');
    const input = root.querySelector('[data-camera-input]');
    const statusEl = root.querySelector('[data-camera-status]');
    const startBtn = root.querySelector('[data-camera-start]');
    const shootBtn = root.querySelector('[data-camera-shoot]');
    const retakeBtn = root.querySelector('[data-camera-retake]');
    const submitBtn = document.querySelector('[data-camera-submit]');

    let stream = null;

    const setStatus = (message, tone = 'info') => {
        if (!statusEl) {
            return;
        }

        statusEl.textContent = message;
        statusEl.classList.toggle('text-rose-600', tone === 'error');
        statusEl.classList.toggle('text-emerald-700', tone === 'success');
        statusEl.classList.toggle('text-slate-500', tone === 'info');
    };

    const setSubmitting = (enabled) => {
        if (!submitBtn) {
            return;
        }

        submitBtn.disabled = !enabled;
        submitBtn.classList.toggle('opacity-50', !enabled);
        submitBtn.classList.toggle('cursor-not-allowed', !enabled);
    };

    const stopStream = () => {
        if (stream) {
            stream.getTracks().forEach((track) => track.stop());
            stream = null;
        }

        if (video) {
            video.srcObject = null;
        }
    };

    const toggleStage = (stage) => {
        if (video) {
            video.hidden = stage !== 'live';
        }
        if (preview) {
            preview.hidden = stage !== 'preview';
        }
        if (placeholder) {
            placeholder.hidden = stage !== 'idle';
        }
        if (startBtn) {
            startBtn.hidden = stage !== 'idle';
        }
        if (shootBtn) {
            shootBtn.hidden = stage !== 'live';
        }
        if (retakeBtn) {
            retakeBtn.hidden = stage !== 'preview';
        }
    };

    // iPad/tablet tanpa secure context tidak akan pernah diberi izin kamera oleh browser.
    const cameraAvailable = window.isSecureContext
        && !!navigator.mediaDevices
        && typeof navigator.mediaDevices.getUserMedia === 'function';

    if (!cameraAvailable) {
        setStatus(
            window.isSecureContext
                ? 'Perangkat atau browser ini tidak mendukung akses kamera langsung. Gunakan tablet/iPad dengan browser terbaru.'
                : 'Kamera hanya dapat diakses lewat koneksi aman (HTTPS). Hubungi panitia bila halaman ini masih dibuka melalui http://.',
            'error',
        );

        if (startBtn) {
            startBtn.disabled = true;
            startBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }

        return;
    }

    setSubmitting(false);
    toggleStage('idle');
    setStatus('Tekan "Nyalakan Kamera" untuk mulai mengambil foto bukti.');

    const startCamera = async () => {
        setStatus('Meminta izin kamera...');
        if (startBtn) {
            startBtn.disabled = true;
        }

        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: { ideal: 'environment' },
                    width: { ideal: 1920 },
                    height: { ideal: 1080 },
                },
                audio: false,
            });

            video.srcObject = stream;
            video.setAttribute('playsinline', '');
            video.muted = true;
            await video.play();

            toggleStage('live');
            setStatus('Arahkan kamera ke momen serah-terima, lalu tekan "Ambil Foto".');
        } catch (error) {
            if (startBtn) {
                startBtn.disabled = false;
            }
            toggleStage('idle');
            setStatus(describeCameraError(error), 'error');
        }
    };

    const capture = () => {
        if (!stream || !video.videoWidth || !video.videoHeight) {
            setStatus('Kamera belum siap. Tunggu sebentar lalu coba lagi.', 'error');

            return;
        }

        const scale = Math.min(1, MAX_WIDTH / video.videoWidth);
        canvas.width = Math.round(video.videoWidth * scale);
        canvas.height = Math.round(video.videoHeight * scale);
        canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

        canvas.toBlob((blob) => {
            if (!blob) {
                setStatus('Gagal memproses foto. Coba ambil ulang.', 'error');

                return;
            }

            try {
                const transfer = new DataTransfer();
                transfer.items.add(new File([blob], `bukti-${Date.now()}.jpg`, { type: 'image/jpeg' }));
                input.files = transfer.files;
            } catch (error) {
                setStatus('Browser ini tidak mendukung penyimpanan hasil foto. Perbarui browser lalu coba lagi.', 'error');

                return;
            }

            if (preview.src) {
                URL.revokeObjectURL(preview.src);
            }
            preview.src = URL.createObjectURL(blob);

            toggleStage('preview');
            setSubmitting(true);
            setStatus('Foto bukti siap. Tekan "Konfirmasi Serah-terima" untuk mengirim.', 'success');
            stopStream();
        }, 'image/jpeg', JPEG_QUALITY);
    };

    const retake = () => {
        if (input) {
            input.value = '';
        }
        if (preview) {
            if (preview.src) {
                URL.revokeObjectURL(preview.src);
            }
            preview.removeAttribute('src');
        }

        setSubmitting(false);
        startCamera();
    };

    startBtn?.addEventListener('click', startCamera);
    shootBtn?.addEventListener('click', capture);
    retakeBtn?.addEventListener('click', retake);

    // Lepaskan kamera saat halaman ditinggalkan agar tidak dikunci oleh tab lain.
    window.addEventListener('pagehide', stopStream);
}
