@props([
    'image' => 'assets/img/3.webp',
    'position' => 'center', // center | top | bottom | 50% 30%
    'title' => Str::title(str_replace('-', ' ', Route::currentRouteName())),
    'description' => null,
])

<section
    class="relative rounded-b-3xl banner-image py-40 mt-0 overflow-hidden bg-cover bg-no-repeat"
    style="
        background-image: url('{{ asset($image) }}');
        background-position: {{ $position }};
    "
>
    <!-- Overlay -->
    <div class="absolute inset-0 bg-black/55"></div>

    <!-- Mobile crop helper -->
    <div class="absolute inset-0 md:hidden"
         style="background: linear-gradient(to bottom, rgba(0,0,0,0.35), rgba(0,0,0,0.65));">
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center text-white">
            <h1 class="text-3xl md:text-5xl font-bold mb-4">
                {{ $title }}
            </h1>

            <p class="max-w-3xl mx-auto text-base md:text-lg text-white/90">
                {{ $description ?? 'Providing trusted, professional services with care and excellence.' }}
            </p>
        </div>
    </div>
</section>