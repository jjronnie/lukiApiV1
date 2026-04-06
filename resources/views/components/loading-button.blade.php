<button type="submit" class="btn-submit  {{ $class }}">
    <span class="btn-text">{{ $text }}</span>
    <i data-lucide="loader" class="w-4 h-4 ml-2 animate-spin btn-spinner hidden"></i>
</button>

<script>
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const btn = form.querySelector('.btn-submit')
            const text = form.querySelector('.btn-text')
            const spinner = form.querySelector('.btn-spinner')

            btn.disabled = true
            text.textContent = 'Processing...'
            spinner.classList.remove('hidden')
        })
    })
</script>
