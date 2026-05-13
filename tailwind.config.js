/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './resources/views/**/*.blade.php',
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './vendor/livewire/flux-pro/stubs/**/*.blade.php',
    './vendor/livewire/flux/stubs/**/*.blade.php',
  ],
  safelist: [
    // Sidebar colors
    'text-indigo-600', 'dark:text-indigo-400',
    'text-amber-600', 'dark:text-amber-400',
    'text-blue-600', 'dark:text-blue-400',
    'text-purple-600', 'dark:text-purple-400',
    'text-emerald-600', 'dark:text-emerald-400',
  ],
}
