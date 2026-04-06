@props([
    'name' => 'avatar',
    'label' => 'Image',
    'preview' => null,
    'maxSize' => 10, // MB
])

@php
    use Illuminate\Support\Str;

    $previewUrl = '';
    if ($preview) {
        $previewUrl = Str::startsWith($preview, ['http://', 'https://', '//'])
            ? $preview
            : asset($preview);
    }
@endphp

<div class="flex flex-col space-y-2">
    <label class="label">{{ $label }}</label>

    <div
        x-data="{
            imagePreview: '{{ $previewUrl }}',
            error: null,

            handleFile(event) {
                this.error = null

                const file = event.target.files[0]
                if (!file) return

                // Validate type
                if (!file.type.startsWith('image/')) {
                    this.error = 'Only image files are allowed.'
                    event.target.value = ''
                    this.imagePreview = ''
                    return
                }

                // Validate size
                const maxBytes = {{ $maxSize }} * 1024 * 1024
                if (file.size > maxBytes) {
                    this.error = 'Image must not exceed {{ $maxSize }}MB.'
                    event.target.value = ''
                    this.imagePreview = ''
                    return
                }

                // Preview
                this.imagePreview = URL.createObjectURL(file)
            }
        }"
    >

        <label
            class="w-32 h-32 border border-dashed rounded-md flex flex-col items-center justify-center cursor-pointer bg-white overflow-hidden">

            <template x-if="!imagePreview">
                <div class="flex flex-col items-center justify-center text-gray-500">
                    <span class="text-3xl">+</span>
                    <span>Upload</span>
                </div>
            </template>

            <template x-if="imagePreview">
                <img :src="imagePreview" class="w-full h-full object-cover" />
            </template>

            <input
                type="file"
                class="hidden"
                name="{{ $name }}"
                accept="image/*"
                @change="handleFile"
            >
        </label>

        <!-- Client-side error -->
        <template x-if="error">
            <p class="text-red-500 text-sm mt-1" x-text="error"></p>
        </template>

        <!-- Server-side error -->
        @error($name)
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>
</div>
