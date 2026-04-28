document.addEventListener('DOMContentLoaded', () => {
  // Count-up KPI
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

  // Search + filter cards (timeline)
  const q = document.getElementById('mesureSearch');
  const cards = Array.from(document.querySelectorAll('[data-item="mesure"]'));
  if (q) {
    q.addEventListener('input', () => {
      const query = q.value.toLowerCase().trim();
      cards.forEach(c => {
        const blob = (c.dataset.blob || '').toLowerCase();
        c.style.display = (query === '' || blob.includes(query)) ? '' : 'none';
      });
    });
  }
});