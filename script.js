// Feature flags: flip to true to bring the Donate pages back.
const FEATURES = {
  donate: false,
};

function applyFeatureFlags(root) {
  if (FEATURES.donate) return;
  root.querySelectorAll('a[href="donate"], a[href="donate.html"]').forEach(function (a) {
    (a.closest('li') || a).remove();
  });
}

if (!FEATURES.donate && /\/donate(\.html)?\/?$/.test(location.pathname)) {
  location.replace('./');
}

document.addEventListener('DOMContentLoaded', function () {
  applyFeatureFlags(document);
});

// Making Navbar Responsive
$(document).ready(function () {
  $('.hamburger i').click(function () {
    $(this).toggleClass('fa-times');
    $('.nav-links').toggleClass('mobile-view');
  });
});

document.addEventListener('DOMContentLoaded', function () {
  fetch('footer.html')
    .then((response) => response.text())
    .then((data) => {
      document.getElementById('footer').innerHTML = data;
      applyFeatureFlags(document.getElementById('footer'));

      // set current year
      const year = document.getElementById('year');
      if (year) {
        year.textContent = new Date().getFullYear();
      }
    })
    .catch((error) => console.error('Error loading footer:', error));
});
