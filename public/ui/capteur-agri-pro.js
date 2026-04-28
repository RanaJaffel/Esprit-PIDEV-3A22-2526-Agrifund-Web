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

  // Live filter cards
  const q = document.getElementById('capteurSearch');
  const fStatus = document.getElementById('capteurFilterStatus');
  const fType = document.getElementById('capteurFilterType');
  const btnReset = document.getElementById('capteurReset');
  const cards = Array.from(document.querySelectorAll('[data-item="capteur"]'));

  function applyFilter(){
    const query = (q?.value || '').toLowerCase().trim();
    const st = (fStatus?.value || '');
    const type = (fType?.value || '');

    cards.forEach(c => {
      const blob = (c.dataset.blob || '').toLowerCase();
      const okQuery = query === '' || blob.includes(query);
      const okSt = st === '' || c.dataset.status === st;
      const okType = type === '' || c.dataset.type === type;
      c.style.display = (okQuery && okSt && okType) ? '' : 'none';
    });
  }

  [q,fStatus,fType].forEach(x => x && x.addEventListener('input', applyFilter));
  if(btnReset){
    btnReset.addEventListener('click', () => {
      if(q) q.value = '';
      if(fStatus) fStatus.value = '';
      if(fType) fType.value = '';
      applyFilter();
    });
  }
});