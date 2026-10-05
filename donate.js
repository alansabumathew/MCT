// Donation form: validation + submit
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('donate-form');
  if (!form) return;

  const MAX_BYTES = 5 * 1024 * 1024;
  const FILE_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
  const status = document.getElementById('donate-status');
  const button = document.getElementById('donate-submit');

  const rules = {
    name: (v) => v.trim().length >= 2,
    pan: (v) => /^[A-Z]{5}[0-9]{4}[A-Z]$/.test(v.trim().toUpperCase()),
    phone: (v) => /^[6-9][0-9]{9}$/.test(v.trim()),
    email: (v) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()),
    amount: (v) => Number(v) > 0,
    screenshot: () => {
      const f = form.screenshot.files[0];
      return !!f && f.size <= MAX_BYTES && FILE_TYPES.includes(f.type);
    },
  };

  function check(field) {
    const card = form.querySelector('[data-field="' + field + '"]');
    const input = form.elements[field];
    const ok = rules[field](input.value);
    card.classList.toggle('invalid', !ok);
    return ok;
  }

  Object.keys(rules).forEach((field) => {
    form.elements[field].addEventListener('blur', () => check(field));
    form.elements[field].addEventListener('input', () => {
      const card = form.querySelector('[data-field="' + field + '"]');
      if (card.classList.contains('invalid')) check(field);
    });
  });

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    const results = Object.keys(rules).map(check);
    if (results.includes(false)) {
      const bad = form.querySelector('.invalid');
      if (bad) bad.scrollIntoView({ behavior: 'smooth', block: 'center' });
      return;
    }

    const data = new FormData(form);
    data.set('pan', data.get('pan').trim().toUpperCase());

    button.disabled = true;
    status.textContent = 'Submitting...';
    status.className = '';

    fetch('api/submit.php', { method: 'POST', body: data })
      .then((r) => r.json().then((j) => ({ ok: r.ok, body: j })))
      .then(({ ok, body }) => {
        if (!ok || !body.success) throw new Error(body.error || 'Submission failed.');
        form.hidden = true;
        document.getElementById('donate-success').hidden = false;
        window.scrollTo({ top: 0, behavior: 'smooth' });
      })
      .catch((err) => {
        status.textContent = err.message || 'Something went wrong. Please try again.';
        status.className = 'error';
        button.disabled = false;
      });
  });
});
