@php($s = app(\App\Settings\AparenciaSettings::class))

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
  :root{
    --brand: {{ $s->cor_primaria ?? '#16a34a' }};
  }
  html, body {
    font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans",
      "Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji";
  }

  /* ===== Troca de logo por tema ===== */
  .brand-dark  { display: none !important; }
  .brand-light { display: block !important; }

  /* Variações de toggling do Filament / Tailwind */
  html.dark .brand-dark,
  :root.dark .brand-dark,
  :root[data-theme="dark"] .brand-dark,
  :root[data-theme-mode="dark"] .brand-dark,
  html[data-theme="dark"] .brand-dark,
  html[data-mode="dark"] .brand-dark,
  html[class*="dark"] .brand-dark,
  .fi-theme-dark .brand-dark,
  .fi-dark .brand-dark { display: block !important; }

  html.dark .brand-light,
  :root.dark .brand-light,
  :root[data-theme="dark"] .brand-light,
  :root[data-theme-mode="dark"] .brand-light,
  html[data-theme="dark"] .brand-light,
  html[data-mode="dark"] .brand-light,
  html[class*="dark"] .brand-light,
  .fi-theme-dark .brand-light,
  .fi-dark .brand-light { display: none !important; }
</style>

<script>
(() => {
  function syncBrandLogo() {
    const r = document.documentElement;
    const isDark =
      r.classList.contains('dark') ||
      r.classList.contains('fi-theme-dark') ||
      r.dataset.theme === 'dark' ||
      r.dataset.themeMode === 'dark' ||
      r.getAttribute('data-mode') === 'dark';

    document.querySelectorAll('.brand-dark') .forEach(el => el.style.display = isDark ? 'block' : 'none');
    document.querySelectorAll('.brand-light').forEach(el => el.style.display = isDark ? 'none'  : 'block');
  }

  // sincroniza no load e sempre que o root muda (classe/atributo)
  window.addEventListener('load', syncBrandLogo);
  document.addEventListener('turbo:load', syncBrandLogo);
  new MutationObserver(syncBrandLogo).observe(document.documentElement, {
    attributes: true,
    attributeFilter: ['class','data-theme','data-theme-mode','data-mode'],
  });
})();
</script>
