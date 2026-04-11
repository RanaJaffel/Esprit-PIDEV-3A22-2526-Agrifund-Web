document.addEventListener('DOMContentLoaded', () => {
  // KPI count-up
  document.querySelectorAll('[data-countup]').forEach(el => {
    const target = parseInt(el.dataset.countup || '0', 10);
    let cur = 0;
    const step = Math.max(1, Math.floor(target / 35));
    const t = setInterval(() => {
      cur += step;
      if (cur >= target) { cur = target; clearInterval(t); }
      el.textContent = cur;
    }, 18);
  });

  // copy API URL
  const btn = document.getElementById('copyApiUrl');
  if (btn) {
    btn.addEventListener('click', async () => {
      const url = btn.dataset.url;
      try {
        await navigator.clipboard.writeText(url);
        btn.textContent = 'Copié';
        setTimeout(() => btn.textContent = 'Copier', 1200);
      } catch(e) {}
    });
  }
});