(function () {
  'use strict';

  var grid = document.getElementById('gallery-grid');
  var emptyEl = document.getElementById('gallery-empty');
  var errEl = document.getElementById('gallery-error');
  var lightbox = document.getElementById('gallery-lightbox');
  var lightboxImg = document.getElementById('gallery-lightbox-img');
  var lightboxVideo = document.getElementById('gallery-lightbox-video');
  var lightboxCap = document.getElementById('gallery-lightbox-caption');
  var closeBtn = document.getElementById('gallery-lightbox-close');

  if (!grid) return;

  function showError(msg) {
    if (errEl) {
      errEl.hidden = false;
      errEl.textContent = msg;
    }
    if (emptyEl) emptyEl.hidden = true;
  }

  function getMediaType(item) {
    if (item && typeof item.type === 'string' && item.type.trim()) {
      return item.type.trim().toLowerCase();
    }
    var file = item && item.file ? String(item.file).toLowerCase() : '';
    if (/\.(mp4|webm|ogg|mov|m4v)$/i.test(file)) return 'video';
    return 'image';
  }

  function openLightbox(src, caption, mediaType) {
    if (!lightbox) return;
    if (lightboxImg) {
      lightboxImg.hidden = mediaType === 'video';
      if (mediaType === 'image') {
        lightboxImg.src = src;
        lightboxImg.alt = caption || '';
      } else {
        lightboxImg.src = '';
        lightboxImg.alt = '';
      }
    }
    if (lightboxVideo) {
      lightboxVideo.hidden = mediaType !== 'video';
      if (mediaType === 'video') {
        lightboxVideo.src = src;
        lightboxVideo.setAttribute('aria-label', caption || 'Gallery video');
      } else {
        lightboxVideo.pause();
        lightboxVideo.removeAttribute('src');
        lightboxVideo.load();
      }
    }
    if (lightboxCap) lightboxCap.textContent = caption || '';
    lightbox.hidden = false;
    document.body.style.overflow = 'hidden';
  }

  function closeLightbox() {
    if (!lightbox) return;
    lightbox.hidden = true;
    if (lightboxImg) {
      lightboxImg.src = '';
      lightboxImg.alt = '';
    }
    if (lightboxVideo) {
      lightboxVideo.pause();
      lightboxVideo.removeAttribute('src');
      lightboxVideo.load();
      lightboxVideo.hidden = true;
    }
    document.body.style.overflow = '';
  }

  function render(items) {
    if (!Array.isArray(items) || items.length === 0) {
      if (emptyEl) emptyEl.hidden = false;
      return;
    }
    if (emptyEl) emptyEl.hidden = true;
    if (errEl) errEl.hidden = true;

    var base = 'assets/gallery/';
    var frag = document.createDocumentFragment();

    items.forEach(function (item) {
      if (!item || !item.file) return;
      var mediaType = getMediaType(item);
      var cap = (item.caption && String(item.caption).trim()) || item.file;
      var src = base + encodeURI(item.file);

      var article = document.createElement('article');
      article.className = 'gallery-card';

      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'gallery-card__trigger';
      btn.setAttribute('aria-label', 'View larger: ' + cap);

      var mediaEl;
      if (mediaType === 'video') {
        mediaEl = document.createElement('video');
        mediaEl.src = src;
        mediaEl.preload = 'metadata';
        mediaEl.muted = true;
        mediaEl.playsInline = true;
        mediaEl.setAttribute('aria-label', cap);

        var badge = document.createElement('span');
        badge.className = 'gallery-card__media-badge';
        badge.textContent = 'Video';
        article.appendChild(badge);
      } else {
        mediaEl = document.createElement('img');
        mediaEl.src = src;
        mediaEl.alt = cap;
        mediaEl.loading = 'lazy';
        mediaEl.decoding = 'async';
      }

      var figcap = document.createElement('p');
      figcap.className = 'gallery-card__caption';
      figcap.textContent = cap;

      btn.appendChild(mediaEl);
      article.appendChild(btn);
      article.appendChild(figcap);

      btn.addEventListener('click', function () {
        openLightbox(src, cap, mediaType);
      });

      frag.appendChild(article);
    });

    grid.appendChild(frag);
  }

  if (closeBtn) {
    closeBtn.addEventListener('click', closeLightbox);
  }
  if (lightbox) {
    lightbox.addEventListener('click', function (e) {
      if (e.target === lightbox) closeLightbox();
    });
  }
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && lightbox && !lightbox.hidden) closeLightbox();
  });

  fetch('assets/gallery/manifest.json')
    .then(function (res) {
      if (!res.ok) throw new Error('Could not load gallery list.');
      return res.json();
    })
    .then(function (data) {
      render(data);
    })
    .catch(function () {
      showError('Gallery could not be loaded. If you are viewing this file directly from disk, use a local web server, or check that assets/gallery/manifest.json is present.');
    });
})();
