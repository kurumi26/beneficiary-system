import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
	let qrScannerModule = null;
	let qrScannerWorkerConfigured = false;

	const loadQrScanner = async () => {
		if (qrScannerModule !== null) {
			return qrScannerModule;
		}

		const [{ default: QrScanner }, { default: workerPath }] = await Promise.all([
			import('qr-scanner'),
			import('qr-scanner/qr-scanner-worker.min?url'),
		]);

		if (! qrScannerWorkerConfigured) {
			QrScanner.WORKER_PATH = workerPath;
			qrScannerWorkerConfigured = true;
		}

		qrScannerModule = QrScanner;

		return qrScannerModule;
	};

	Alpine.data('cameraUploadField', (config = {}) => ({
		fieldLabel: config.fieldLabel ?? 'Document',
		cameraFacingMode: config.cameraFacingMode ?? 'environment',
		enableDocumentCheck: config.enableDocumentCheck ?? false,
		autoCapture: config.autoCapture ?? false,
		documentAspectRatio: config.documentAspectRatio ?? 1.586,
		previewAlt: config.previewAlt ?? 'Upload preview',
		isCameraOpen: false,
		previewUrl: null,
		selectedFileName: '',
		statusTone: 'neutral',
		statusMessage: '',
		errorMessage: '',
		stream: null,
		scanTone: 'neutral',
		scanLabel: '',
		scanMessage: '',
		stablePasses: 0,
		captureCountdown: 0,
		analysisIntervalId: null,
		countdownIntervalId: null,
		analysisCanvas: null,
		analysisContext: null,
		isCapturing: false,

		init() {
			this.statusMessage = this.defaultFieldStatus();
			this.resetScanGuidance();
		},

		defaultFieldStatus() {
			return this.enableDocumentCheck
				? 'Camera capture auto-attaches only the framed ID area. Red warnings mean the ID is still unverified and needs manual review.'
				: 'Camera capture or file upload will attach the image automatically.';
		},

		resetScanGuidance() {
			this.scanTone = 'neutral';
			this.scanLabel = this.autoCapture ? 'Auto scan ready' : 'Camera ready';
			this.scanMessage = this.autoCapture
				? 'Point the camera at the ID and keep it inside the scanner frame. Capture will start automatically once the image is clear and steady.'
				: 'Frame the subject, then capture when ready.';
		},

		async onFileChange(event) {
			const file = event.target.files?.[0];

			if (!file) {
				this.clearPreview();
				return;
			}

			await this.attachFile(file);
		},

		async openCamera() {
			this.errorMessage = '';

			if (!navigator.mediaDevices?.getUserMedia) {
				this.errorMessage = 'Camera access is not supported on this device or browser.';
				return;
			}

			try {
				this.stream = await navigator.mediaDevices.getUserMedia({
					video: {
						facingMode: {
							ideal: this.cameraFacingMode,
						},
					},
					audio: false,
				});

				this.isCameraOpen = true;

				await this.$nextTick();

				const video = this.$refs.video;
				video.srcObject = this.stream;
				await video.play();

				this.resetCaptureLoop();

				if (this.autoCapture) {
					this.scanTone = 'neutral';
					this.scanLabel = 'Auto scan active';
					this.scanMessage = 'Align the valid ID inside the scanner frame. Only that framed region will be captured automatically.';
					this.startAutoCaptureLoop();
				}
			} catch (error) {
				this.errorMessage = 'Unable to access the camera. Check permissions and try again.';
				this.stopStream();
			}
		},

		closeCamera() {
			this.isCameraOpen = false;
			this.resetCaptureLoop();
			this.resetScanGuidance();
			this.stopStream();
		},

		resetCaptureLoop() {
			this.stablePasses = 0;
			this.captureCountdown = 0;
			this.isCapturing = false;

			if (this.analysisIntervalId) {
				clearInterval(this.analysisIntervalId);
				this.analysisIntervalId = null;
			}

			if (this.countdownIntervalId) {
				clearInterval(this.countdownIntervalId);
				this.countdownIntervalId = null;
			}
		},

		startAutoCaptureLoop() {
			this.resetCaptureLoop();

			this.analysisIntervalId = setInterval(() => {
				void this.evaluateAutoCaptureFrame();
			}, 650);
		},

		async evaluateAutoCaptureFrame() {
			if (!this.isCameraOpen || !this.autoCapture || this.isCapturing) {
				return;
			}

			const video = this.$refs.video;

			if (!video || !video.videoWidth || !video.videoHeight) {
				return;
			}

			const quality = this.measureFrameQuality(video);

			if (!quality.passes) {
				this.stablePasses = 0;
				this.stopCountdown();
				this.scanTone = 'warning';
				this.scanLabel = 'Adjust ID position';
				this.scanMessage = quality.reason;
				return;
			}

			this.stablePasses += 1;

			if (this.stablePasses < 2) {
				this.scanTone = 'neutral';
				this.scanLabel = 'ID detected';
				this.scanMessage = 'Hold steady for a moment so the scan can capture automatically.';
				return;
			}

			if (!this.countdownIntervalId) {
				this.beginCaptureCountdown();
			}
		},

		beginCaptureCountdown() {
			this.captureCountdown = 2;
			this.scanTone = 'success';
			this.scanLabel = 'Auto-capturing ID';
			this.scanMessage = `Hold still. Capturing in ${this.captureCountdown}...`;

			this.countdownIntervalId = setInterval(async () => {
				if (!this.isCameraOpen || this.isCapturing) {
					this.stopCountdown();
					return;
				}

				const video = this.$refs.video;
				const quality = video ? this.measureFrameQuality(video) : { passes: false, reason: 'Camera preview is not ready yet.' };

				if (!quality.passes) {
					this.stablePasses = 0;
					this.stopCountdown();
					this.scanTone = 'warning';
					this.scanLabel = 'Scan paused';
					this.scanMessage = quality.reason;
					return;
				}

				if (this.captureCountdown <= 1) {
					this.stopCountdown();
					this.scanTone = 'neutral';
					this.scanLabel = 'Capturing';
					this.scanMessage = 'Saving the ID image and attaching it to the form...';
					await this.captureFromCamera();
					return;
				}

				this.captureCountdown -= 1;
				this.scanMessage = `Hold still. Capturing in ${this.captureCountdown}...`;
			}, 1000);
		},

		stopCountdown() {
			if (this.countdownIntervalId) {
				clearInterval(this.countdownIntervalId);
				this.countdownIntervalId = null;
			}

			this.captureCountdown = 0;
		},

		measureFrameQuality(source) {
			const sampleWidth = 160;
			const sampleHeight = 120;
			const captureRegion = this.getCaptureRegion(source);

			if (!this.analysisCanvas) {
				this.analysisCanvas = document.createElement('canvas');
				this.analysisContext = this.analysisCanvas.getContext('2d', { willReadFrequently: true });
			}

			if (!this.analysisContext) {
				return {
					passes: true,
					reason: 'Image quality analysis is not available on this browser.',
				};
			}

			this.analysisCanvas.width = sampleWidth;
			this.analysisCanvas.height = sampleHeight;
			this.analysisContext.drawImage(
				source,
				captureRegion.x,
				captureRegion.y,
				captureRegion.width,
				captureRegion.height,
				0,
				0,
				sampleWidth,
				sampleHeight,
			);

			const { data } = this.analysisContext.getImageData(0, 0, sampleWidth, sampleHeight);
			const totalPixels = sampleWidth * sampleHeight;
			const grayscale = new Float32Array(totalPixels);

			let brightnessSum = 0;

			for (let index = 0; index < totalPixels; index += 1) {
				const offset = index * 4;
				const gray = (data[offset] * 0.299) + (data[offset + 1] * 0.587) + (data[offset + 2] * 0.114);
				grayscale[index] = gray;
				brightnessSum += gray;
			}

			const brightness = brightnessSum / totalPixels;

			let varianceSum = 0;
			let edgeSum = 0;
			let edgeCount = 0;

			for (let y = 0; y < sampleHeight; y += 1) {
				for (let x = 0; x < sampleWidth; x += 1) {
					const current = grayscale[(y * sampleWidth) + x];
					varianceSum += (current - brightness) ** 2;

					if (x < sampleWidth - 1) {
						edgeSum += Math.abs(current - grayscale[(y * sampleWidth) + x + 1]);
						edgeCount += 1;
					}

					if (y < sampleHeight - 1) {
						edgeSum += Math.abs(current - grayscale[((y + 1) * sampleWidth) + x]);
						edgeCount += 1;
					}
				}
			}

			const contrast = Math.sqrt(varianceSum / totalPixels);
			const sharpness = edgeCount > 0 ? edgeSum / edgeCount : 0;

			if (brightness < 55) {
				return { passes: false, reason: 'The frame is too dark. Add more light or move the ID closer to the camera.' };
			}

			if (brightness > 225) {
				return { passes: false, reason: 'The frame is too bright or reflective. Tilt the ID slightly to remove glare.' };
			}

			if (contrast < 22) {
				return { passes: false, reason: 'The ID text is too flat to read. Keep only the card inside the scanner frame and use a plain background.' };
			}

			if (sharpness < 16) {
				return { passes: false, reason: 'The image is blurry. Hold the camera steady until the auto-capture starts.' };
			}

			return {
				passes: true,
				brightness,
				contrast,
				sharpness,
			};
		},

		stopStream() {
			if (!this.stream) {
				return;
			}

			this.stream.getTracks().forEach((track) => track.stop());
			this.stream = null;
		},

		async captureFromCamera() {
			const video = this.$refs.video;

			if (!video || !video.videoWidth || !video.videoHeight) {
				this.errorMessage = 'Camera preview is not ready yet.';
				return;
			}

			if (!window.DataTransfer) {
				this.errorMessage = 'Automatic upload after camera capture is not supported on this browser.';
				return;
			}

			this.isCapturing = true;
			this.resetCaptureLoop();

			const captureRegion = this.getCaptureRegion(video);
			const canvas = document.createElement('canvas');
			canvas.width = captureRegion.width;
			canvas.height = captureRegion.height;

			const context = canvas.getContext('2d');
			context.drawImage(
				video,
				captureRegion.x,
				captureRegion.y,
				captureRegion.width,
				captureRegion.height,
				0,
				0,
				canvas.width,
				canvas.height,
			);

			const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.92));

			if (!blob) {
				this.errorMessage = 'Unable to capture the image.';
				return;
			}

			const file = new File([blob], `${this.fieldLabel.toLowerCase().replace(/\s+/g, '-')}-${Date.now()}.jpg`, {
				type: 'image/jpeg',
			});

			const dataTransfer = new DataTransfer();
			dataTransfer.items.add(file);
			this.$refs.input.files = dataTransfer.files;

			await this.attachFile(file);
			this.closeCamera();
		},

		getCaptureRegion(source) {
			const width = source.videoWidth ?? source.width ?? 0;
			const height = source.videoHeight ?? source.height ?? 0;

			if (!this.enableDocumentCheck || !width || !height) {
				return {
					x: 0,
					y: 0,
					width,
					height,
				};
			}

			let regionWidth = width * 0.82;
			let regionHeight = regionWidth / this.documentAspectRatio;
			const maxHeight = height * 0.58;

			if (regionHeight > maxHeight) {
				regionHeight = maxHeight;
				regionWidth = regionHeight * this.documentAspectRatio;
			}

			const x = Math.max(0, Math.round((width - regionWidth) / 2));
			const y = Math.max(0, Math.round((height - regionHeight) / 2));

			return {
				x,
				y,
				width: Math.round(regionWidth),
				height: Math.round(regionHeight),
			};
		},

		async attachFile(file) {
			this.clearPreview(false);
			this.selectedFileName = file.name;
			this.previewUrl = file.type.startsWith('image/') ? URL.createObjectURL(file) : null;

			if (this.enableDocumentCheck && file.type.startsWith('image/')) {
				await this.runDocumentCheck(file);
				return;
			}

			if (this.enableDocumentCheck) {
				this.statusTone = 'warning';
				this.statusMessage = 'Upload attached. Automatic ID pre-check works with camera images; final authenticity review is still manual.';
				return;
			}

			this.statusTone = 'success';
			this.statusMessage = 'Photo attached and ready to upload.';
		},

		async runDocumentCheck(file) {
			this.statusTone = 'neutral';
			this.statusMessage = 'Checking the captured ID for machine-readable markers and basic image quality...';

			try {
				const imageBitmap = await createImageBitmap(file);
				const quality = this.measureFrameQuality(imageBitmap);

				let matches = [];
				let detectorSupported = false;

				if ('BarcodeDetector' in window) {
					detectorSupported = true;
					const detector = new BarcodeDetector({
						formats: ['qr_code', 'pdf417', 'code_128', 'ean_13'],
					});
					matches = await detector.detect(imageBitmap);
				}

				if (typeof imageBitmap.close === 'function') {
					imageBitmap.close();
				}

				if (matches.length > 0) {
					this.statusTone = 'success';
					this.statusMessage = `Scannable ${matches[0].format.replace(/_/g, ' ')} marker detected. The ID image is attached automatically and ready for reviewer confirmation.`;
					return;
				}

				if (!quality.passes) {
					this.statusTone = 'danger';
					this.statusMessage = `Warning: this ID capture looks unreadable or unverified. ${quality.reason} Please rescan before saving.`;
					return;
				}

				if (!detectorSupported) {
					this.statusTone = 'warning';
					this.statusMessage = 'ID image attached automatically, but this browser cannot verify scannable markers. Manual review is still required.';
					return;
				}

				this.statusTone = 'danger';
				this.statusMessage = 'Warning: no scannable ID marker was detected. Treat this upload as unverified and manually review whether the ID is valid.';
			} catch (error) {
				this.statusTone = 'warning';
				this.statusMessage = 'The ID image is attached, but automatic authenticity checking is not available on this device.';
			}
		},

		clearPreview(resetInput = true) {
			if (this.previewUrl) {
				URL.revokeObjectURL(this.previewUrl);
				this.previewUrl = null;
			}

			this.selectedFileName = '';
			this.errorMessage = '';
			this.statusTone = 'neutral';
			this.statusMessage = this.defaultFieldStatus();
			this.resetScanGuidance();

			if (resetInput && this.$refs.input) {
				this.$refs.input.value = '';
			}
		},

		destroy() {
			this.resetCaptureLoop();
			this.stopStream();

			if (this.previewUrl) {
				URL.revokeObjectURL(this.previewUrl);
			}
		},
	}));

	Alpine.data('beneficiaryQrScanner', (config = {}) => ({
		verifyUrl: config.verifyUrl ?? '',
		scanner: null,
		scanning: false,
		starting: false,
		processing: false,
		result: null,
		resultModalOpen: false,
		error: null,
		lastPayload: null,

		openResultModal() {
			this.resultModalOpen = true;
			document.body.classList.add('overflow-y-hidden');
		},

		closeResultModal() {
			this.resultModalOpen = false;
			document.body.classList.remove('overflow-y-hidden');
		},

		async start() {
			this.error = null;

			if (! this.verifyUrl || this.starting || this.processing) {
				return;
			}

			const QrScanner = await loadQrScanner();

			if (! await QrScanner.hasCamera()) {
				this.error = 'No usable camera was detected on this device.';
				return;
			}

			if (this.scanner === null) {
				this.scanner = new QrScanner(this.$refs.video, (scanResult) => {
					void this.handleScan(scanResult);
				}, {
					preferredCamera: 'environment',
					highlightScanRegion: true,
					highlightCodeOutline: true,
					returnDetailedScanResult: true,
					maxScansPerSecond: 8,
				});
			}

			try {
				this.starting = true;
				await this.scanner.start();
				this.scanning = true;
			} catch (error) {
				this.error = error instanceof Error
					? error.message
					: 'Camera access was blocked or no camera is available.';
			} finally {
				this.starting = false;
			}
		},

		async stop() {
			if (this.scanner !== null) {
				await this.scanner.stop();
			}

			this.scanning = false;
		},

		async handleScan(scanResult) {
			const payload = typeof scanResult === 'string' ? scanResult : scanResult?.data ?? '';

			if (! payload || this.processing || payload === this.lastPayload) {
				return;
			}

			this.processing = true;
			this.lastPayload = payload;

			try {
				const response = await fetch(this.verifyUrl, {
					method: 'POST',
					headers: {
						'Accept': 'application/json',
						'Content-Type': 'application/json',
						'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
					},
					body: JSON.stringify({ payload }),
				});

				const data = await response.json();

				if (! response.ok) {
					throw data;
				}

				this.result = {
					...data,
					payload,
				};
				this.openResultModal();
			} catch (error) {
				const rejection = error && typeof error === 'object' ? error : {};

				this.result = {
					...rejection,
					is_legitimate: false,
					payload,
					message: rejection?.message ?? 'The scanned QR code could not be verified.',
					details: rejection?.details ?? 'Only beneficiary QR codes generated by this system can be validated here.',
					notice_title: rejection?.notice_title ?? 'Warning: Not a legitimate beneficiary QR code.',
					notice: rejection?.notice ?? 'This scanned QR code is not legitimate and should not be used for beneficiary verification.',
				};
				this.openResultModal();
			} finally {
				this.processing = false;
				await this.stop();
				window.setTimeout(() => {
					this.lastPayload = null;
				}, 1500);
			}
		},

		async scanAgain() {
			this.closeResultModal();
			this.result = null;
			this.error = null;
			await this.start();
		},
	}));
});

Alpine.start();
