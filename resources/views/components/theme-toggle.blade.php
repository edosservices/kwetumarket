<button
    type="button"
    {{ $attributes->class('inline-flex h-10 w-10 items-center justify-center rounded-full border border-twende-line text-twende-dark hover:border-twende-red hover:text-twende-red dark:border-white/15 dark:text-white') }}
    aria-label="{{ __('ui.nav.theme') }}"
    onclick="const dark = document.documentElement.classList.toggle('dark'); localStorage.setItem('theme', dark ? 'dark' : 'light');"
>
    <span class="dark:hidden"><x-icon name="moon" /></span>
    <span class="hidden dark:inline"><x-icon name="sun" /></span>
</button>
