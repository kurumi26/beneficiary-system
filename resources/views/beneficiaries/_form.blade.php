@php
    $nameParts = preg_split('/\s+/', trim((string) $beneficiary->full_name)) ?: [];
    $defaultFirstName = $nameParts[0] ?? '';
    $defaultLastName = count($nameParts) > 1 ? $nameParts[array_key_last($nameParts)] : '';
    $defaultMiddleName = count($nameParts) > 2 ? implode(' ', array_slice($nameParts, 1, -1)) : '';
@endphp

@csrf

<div class="grid gap-5 md:grid-cols-2">
    <div class="md:col-span-2">
        <p class="section-copy">Fields marked <span class="text-rose-600">*</span> are required.</p>
        @error('full_name') <p class="form-error mt-2">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="form-label" for="first_name">First Name <span class="text-rose-600">*</span></label>
        <input class="form-input" id="first_name" name="first_name" value="{{ old('first_name', $defaultFirstName) }}" required>
        @error('first_name') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="form-label" for="middle_name">Middle Name <span class="text-slate-400">(optional)</span></label>
        <input class="form-input" id="middle_name" name="middle_name" value="{{ old('middle_name', $defaultMiddleName) }}">
        @error('middle_name') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="form-label" for="last_name">Last Name <span class="text-rose-600">*</span></label>
        <input class="form-input" id="last_name" name="last_name" value="{{ old('last_name', $defaultLastName) }}" required>
        @error('last_name') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="form-label" for="barangay">Barangay <span class="text-rose-600">*</span></label>
        <input class="form-input" id="barangay" name="barangay" value="{{ old('barangay', $beneficiary->barangay) }}" required>
        @error('barangay') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div class="md:col-span-2">
        <label class="form-label" for="address">Address <span class="text-rose-600">*</span></label>
        <textarea class="form-input min-h-28" id="address" name="address" required>{{ old('address', $beneficiary->address) }}</textarea>
        @error('address') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="form-label" for="birthdate">Birthdate <span class="text-rose-600">*</span></label>
        <input class="form-input" id="birthdate" type="date" name="birthdate" value="{{ old('birthdate', optional($beneficiary->birthdate)->format('Y-m-d')) }}" required>
        @error('birthdate') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="form-label" for="gender">Gender <span class="text-rose-600">*</span></label>
        <select class="form-input" id="gender" name="gender" required>
            @foreach ($genders as $gender)
                <option value="{{ $gender }}" @selected(old('gender', $beneficiary->gender) === $gender)>{{ $gender }}</option>
            @endforeach
        </select>
        @error('gender') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="form-label" for="contact_number">Contact Number</label>
        <input class="form-input" id="contact_number" name="contact_number" value="{{ old('contact_number', $beneficiary->contact_number) }}">
        @error('contact_number') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="form-label" for="civil_status">Civil Status <span class="text-rose-600">*</span></label>
        <select class="form-input" id="civil_status" name="civil_status" required>
            @foreach ($civilStatuses as $civilStatus)
                <option value="{{ $civilStatus }}" @selected(old('civil_status', $beneficiary->civil_status) === $civilStatus)>{{ $civilStatus }}</option>
            @endforeach
        </select>
        @error('civil_status') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="form-label" for="category">Beneficiary Category <span class="text-rose-600">*</span></label>
        <select class="form-input" id="category" name="category" required>
            @foreach ($categories as $category)
                <option value="{{ $category }}" @selected(old('category', $beneficiary->category) === $category)>{{ $category }}</option>
            @endforeach
        </select>
        @error('category') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="form-label" for="status">Status <span class="text-rose-600">*</span></label>
        <select class="form-input" id="status" name="status" required>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(old('status', $beneficiary->status ?? 'pending') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        @error('status') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div class="md:col-span-2">
        <div class="grid gap-5 md:grid-cols-2 md:items-start">
            <div class="space-y-5">
                <div>
                    <label class="form-label" for="date_issued">Date Issued</label>
                    <input class="form-input" id="date_issued" type="date" name="date_issued" value="{{ old('date_issued', optional($beneficiary->date_issued)->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
                    @error('date_issued') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div x-data="cameraUploadField({ fieldLabel: 'Photo', cameraFacingMode: 'user', previewAlt: 'Beneficiary photo preview' })" class="capture-field min-w-0">
                    <label class="form-label" for="photo">Photo Capture / Upload</label>
                    <input x-ref="input" @change="onFileChange" class="form-input" id="photo" type="file" name="photo" accept="image/*" capture="user">
                    <div class="capture-actions">
                        <button class="button-secondary" type="button" @click="openCamera">Capture Photo With Camera</button>
                    </div>
                    <div class="capture-status" :class="`capture-status--${statusTone}`" x-text="statusMessage"></div>
                    <template x-if="previewUrl || selectedFileName">
                        <div class="capture-preview">
                            <template x-if="previewUrl">
                                <img :src="previewUrl" :alt="previewAlt">
                            </template>
                            <div class="capture-preview__meta">
                                <strong class="block text-slate-900">Attached file</strong>
                                <span x-text="selectedFileName"></span>
                            </div>
                        </div>
                    </template>
                    <p class="form-error" x-show="errorMessage" x-text="errorMessage"></p>
                    @error('photo') <p class="form-error">{{ $message }}</p> @enderror

                    <div class="camera-modal" x-cloak x-show="isCameraOpen" @keydown.escape.window="closeCamera()">
                        <div class="camera-panel">
                            <div class="section-heading mb-0">
                                <div>
                                    <h3 class="subsection-title">Capture Beneficiary Photo</h3>
                                    <p class="section-copy">Use the front camera and keep the face centered before capturing.</p>
                                </div>
                            </div>

                            <div class="camera-frame">
                                <video x-ref="video" class="camera-video" playsinline autoplay muted></video>
                            </div>

                            <div class="camera-toolbar">
                                <button class="button-secondary" type="button" @click="closeCamera">Cancel</button>
                                <button class="button-primary" type="button" @click="captureFromCamera">Capture And Attach</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div x-data="cameraUploadField({ fieldLabel: 'Valid ID', cameraFacingMode: 'environment', enableDocumentCheck: true, autoCapture: true, previewAlt: 'Valid ID preview' })" class="capture-field min-w-0">
                <label class="form-label" for="valid_id">Valid ID Upload</label>
                <input x-ref="input" @change="onFileChange" class="form-input" id="valid_id" type="file" name="valid_id" accept=".jpg,.jpeg,.png,.pdf,image/*">
                <div class="capture-actions">
                    <button class="button-secondary" type="button" @click="openCamera">Start Auto ID Scan</button>
                </div>
                <div class="capture-status" :class="`capture-status--${statusTone}`" x-text="statusMessage"></div>
                <template x-if="previewUrl || selectedFileName">
                    <div class="capture-preview">
                        <template x-if="previewUrl">
                            <img :src="previewUrl" :alt="previewAlt">
                        </template>
                        <div class="capture-preview__meta">
                            <strong class="block text-slate-900">Attached file</strong>
                            <span x-text="selectedFileName"></span>
                        </div>
                    </div>
                </template>
                <p class="form-error" x-show="errorMessage" x-text="errorMessage"></p>
                @error('valid_id') <p class="form-error">{{ $message }}</p> @enderror

                <div class="camera-modal" x-cloak x-show="isCameraOpen" @keydown.escape.window="closeCamera()">
                    <div class="camera-panel">
                        <div class="section-heading mb-0">
                            <div>
                                <h3 class="subsection-title">Scan Valid ID</h3>
                                <p class="section-copy">Use the rear camera and keep the document steady inside the frame.</p>
                            </div>
                        </div>

                        <div class="camera-frame camera-frame--document">
                            <video x-ref="video" class="camera-video" playsinline autoplay muted></video>
                            <div class="camera-guide" aria-hidden="true">
                                <div class="camera-guide__cutout">
                                    <div class="camera-guide__label">Place only the ID inside this frame</div>
                                </div>
                            </div>
                        </div>

                        <div class="capture-status mt-4" :class="`capture-status--${scanTone}`">
                            <strong class="mb-1 block" x-text="scanLabel"></strong>
                            <span x-text="scanMessage"></span>
                        </div>

                        <div class="camera-toolbar">
                            <button class="button-secondary" type="button" @click="closeCamera">Cancel</button>
                            <button class="button-primary" type="button" @click="captureFromCamera" :disabled="isCapturing">Capture Framed ID</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="md:col-span-2">
        <label class="form-label" for="supporting_documents">Supporting Documents</label>
        <input class="form-input" id="supporting_documents" type="file" name="supporting_documents[]" multiple accept=".jpg,.jpeg,.png,.pdf">
        @error('supporting_documents.*') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div class="md:col-span-2">
        <label class="form-label" for="notes">Notes</label>
        <textarea class="form-input min-h-28" id="notes" name="notes">{{ old('notes', $beneficiary->notes) }}</textarea>
        @error('notes') <p class="form-error">{{ $message }}</p> @enderror
    </div>
</div>

<div class="mt-6 flex flex-col-reverse justify-end gap-3 sm:flex-row">
    <a class="button-secondary" href="{{ route('beneficiaries.index') }}">Cancel</a>
    <button class="button-primary" type="submit">{{ $beneficiary->exists ? 'Update Record' : 'Register Beneficiary' }}</button>
</div>
