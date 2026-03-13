@extends('layouts.verification')

@php
    $pageTitle = 'Verify your account';
@endphp

@section('content')
    <section class="hero">
        <p class="eyebrow">Secure Verification</p>
        <h1>Confirm your identity in one secure step.</h1>
        <p class="subtitle">
            Upload a clear selfie and both sides of your identification document. This secure session expires on
            {{ $expiresAt?->timezone(config('app.timezone'))->format('D, d M Y \a\t H:i') }}.
        </p>
        <div class="meta-bar">
            <span class="pill">Selfie required</span>
            <span class="pill">Front + back required</span>
            <span class="pill">Images only</span>
        </div>
    </section>

    <section class="card stack">
        <div class="notice warning">
            Use clear, well-lit photos. Files must be image files only and 10MB or less each.
        </div>

        @if ($errors->any())
            <div class="notice danger">
                <ul class="error-list">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="verification-form" class="stack" method="POST" action="{{ $submitUrl }}" enctype="multipart/form-data">
            @csrf

            <div class="section">
                <h2>Step 1. Selfie</h2>
                <p>Use the front camera only. Make sure your face is fully visible and centered.</p>
                <div class="field-card">
                    <div class="field">
                        <div class="field-meta">
                            <label for="selfie">Selfie image</label>
                            <span class="field-hint">Camera only</span>
                        </div>
                        <div class="preview-frame" data-preview-frame="selfie">
                            <div class="preview-placeholder">Your selfie preview will appear here after capture.</div>
                        </div>
                        <div class="action-row single">
                            <button class="btn-inline" type="button" data-open-camera="selfie">Take Selfie</button>
                        </div>
                        <div id="selfie-note" class="file-note">Image only, up to 10MB.</div>
                        <input
                            id="selfie"
                            class="visually-hidden"
                            name="selfie"
                            type="file"
                            accept="image/*"
                            capture="user"
                            required
                        >
                    </div>
                </div>
            </div>

            <div class="section">
                <h2>Step 2. Document details</h2>
                <p>Select the document type you are uploading.</p>
                <div class="field">
                    <label for="id_type">ID type</label>
                    <select id="id_type" name="id_type" required>
                        <option value="">Choose ID type</option>
                        <option value="passport" @selected(old('id_type') === 'passport')>Passport</option>
                        <option value="national_id" @selected(old('id_type', 'national_id') === 'national_id')>National ID</option>
                        <option value="drivers_license" @selected(old('id_type') === 'drivers_license')>Driver's license</option>
                    </select>
                </div>
            </div>

            <div class="section">
                <h2>Step 3. Document images</h2>
                <p>Upload the front and back of the same document. You can take a new photo or choose one from your files.</p>
                <div class="field-card">
                    <div class="field">
                        <label for="id_front">ID front image</label>
                        <div class="preview-frame" data-preview-frame="id_front">
                            <div class="preview-placeholder">The front of your ID will preview here.</div>
                        </div>
                        <div class="action-row">
                            <button class="btn-inline" type="button" data-open-camera="id_front">Take Photo</button>
                            <button class="btn-inline" type="button" data-open-file="id_front">Choose File</button>
                        </div>
                        <div id="id_front-note" class="file-note">Image only, up to 10MB.</div>
                        <input
                            id="id_front"
                            class="visually-hidden"
                            name="id_front"
                            type="file"
                            accept="image/*"
                            required
                        >
                    </div>
                </div>
                <div class="field-card">
                    <div class="field">
                        <label for="id_back">ID back image</label>
                        <div class="preview-frame" data-preview-frame="id_back">
                            <div class="preview-placeholder">The back of your ID will preview here.</div>
                        </div>
                        <div class="action-row">
                            <button class="btn-inline" type="button" data-open-camera="id_back">Take Photo</button>
                            <button class="btn-inline" type="button" data-open-file="id_back">Choose File</button>
                        </div>
                        <div id="id_back-note" class="file-note">Image only, up to 10MB.</div>
                        <input
                            id="id_back"
                            class="visually-hidden"
                            name="id_back"
                            type="file"
                            accept="image/*"
                            required
                        >
                    </div>
                </div>
            </div>

            <div class="button-row">
                <button id="submit-button" class="btn" type="submit" disabled>Submit Verification</button>
            </div>
        </form>

        <p class="footer-note">
            After submission, return to the app to refresh your status. Your images are processed server-side and attached to your account for review.
        </p>
    </section>

    <script>
        const form = document.getElementById('verification-form');
        const submitButton = document.getElementById('submit-button');
        const idType = document.getElementById('id_type');
        const maxFileSize = 10 * 1024 * 1024;
        const fieldConfig = {
            selfie: {
                input: document.getElementById('selfie'),
                frame: document.querySelector('[data-preview-frame="selfie"]'),
                note: document.getElementById('selfie-note'),
                placeholder: 'Your selfie preview will appear here after capture.',
            },
            id_front: {
                input: document.getElementById('id_front'),
                frame: document.querySelector('[data-preview-frame="id_front"]'),
                note: document.getElementById('id_front-note'),
                placeholder: 'The front of your ID will preview here.',
            },
            id_back: {
                input: document.getElementById('id_back'),
                frame: document.querySelector('[data-preview-frame="id_back"]'),
                note: document.getElementById('id_back-note'),
                placeholder: 'The back of your ID will preview here.',
            },
        };

        const setFieldMessage = (config, message, isError = false) => {
            if (!config.note) {
                return;
            }

            config.note.textContent = message;
            config.note.classList.toggle('error', isError);
        };

        const setPlaceholder = (config) => {
            if (!config.frame) {
                return;
            }

            config.frame.classList.remove('has-image');
            config.frame.innerHTML = `<div class="preview-placeholder">${config.placeholder}</div>`;
        };

        const renderPreview = (file, config) => {
            if (!file || !config.frame) {
                return;
            }

            const imageUrl = URL.createObjectURL(file);
            config.frame.classList.add('has-image');
            config.frame.innerHTML = `<img src="${imageUrl}" alt="Selected verification image">`;
        };

        const validateFile = (file) => {
            if (!file) {
                return 'Please add this image before submitting.';
            }

            if (!(file.type || '').startsWith('image/')) {
                return 'Only image files are allowed.';
            }

            if (file.size > maxFileSize) {
                return 'Each image must be 10MB or less.';
            }

            return null;
        };

        const updateSubmitState = () => {
            const hasValidIdType = (idType.value || '').trim().length > 0;
            const allFieldsReady = Object.values(fieldConfig).every(({ input }) => {
                const file = input.files && input.files[0];
                return validateFile(file) === null;
            });

            submitButton.disabled = !(hasValidIdType && allFieldsReady);
        };

        Object.values(fieldConfig).forEach((config) => {
            setPlaceholder(config);
            setFieldMessage(config, 'Image only, up to 10MB.');
        });

        Object.entries(fieldConfig).forEach(([fieldName, config]) => {
            if (!config.input) {
                return;
            }

            config.input.addEventListener('change', (event) => {
                const file = event.target.files && event.target.files[0];
                const validationError = validateFile(file);

                if (validationError !== null) {
                    config.input.value = '';
                    setPlaceholder(config);
                    setFieldMessage(config, validationError, true);
                    updateSubmitState();
                    return;
                }

                renderPreview(file, config);
                setFieldMessage(
                    config,
                    `${file.name} • ${(file.size / (1024 * 1024)).toFixed(2)} MB`,
                );
                updateSubmitState();
            });
        });

        document.querySelectorAll('[data-open-camera]').forEach((button) => {
            button.addEventListener('click', () => {
                const fieldName = button.getAttribute('data-open-camera');
                const config = fieldConfig[fieldName];
                if (!config || !config.input) {
                    return;
                }

                config.input.setAttribute('capture', fieldName === 'selfie' ? 'user' : 'environment');
                config.input.click();
            });
        });

        document.querySelectorAll('[data-open-file]').forEach((button) => {
            button.addEventListener('click', () => {
                const fieldName = button.getAttribute('data-open-file');
                const config = fieldConfig[fieldName];
                if (!config || !config.input) {
                    return;
                }

                config.input.removeAttribute('capture');
                config.input.click();
            });
        });

        idType.addEventListener('change', updateSubmitState);
        updateSubmitState();

        form.addEventListener('submit', () => {
            submitButton.disabled = true;
            submitButton.textContent = 'Submitting verification...';
        });
    </script>
@endsection
