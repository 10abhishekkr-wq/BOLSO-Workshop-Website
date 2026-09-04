(function () {
  var prices = window.bolsoPrices || {};
  var workshop = document.getElementById('workshop');
  var mode = document.getElementById('mode');
  var priceDisplay = document.getElementById('priceDisplay');
  var priceNote = document.getElementById('priceNote');

  function updatePrice() {
    if (!workshop || !mode || !priceDisplay) return;
    var key = workshop.value + '_' + mode.value;
    var price = prices[key] || 399;
    priceDisplay.textContent = '₹' + Number(price).toLocaleString('en-IN');
    if (priceNote) {
      priceNote.textContent = mode.value === 'offline' && workshop.value === '5-day'
        ? 'Materials and colours provided.'
        : mode.value === 'online'
          ? 'Live classes over Google Meet.'
          : 'Final details shared after registration.';
    }
  }

  if (workshop) workshop.addEventListener('change', updatePrice);
  if (mode) mode.addEventListener('change', updatePrice);

  document.querySelectorAll('a[href^="#"]').forEach(function (link) {
    link.addEventListener('click', function (event) {
      var target = document.querySelector(link.getAttribute('href'));
      if (target) {
        event.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });

  // 3D Card Stack Slider (matching user reference image)
  var container = document.getElementById('cardStackSlider');
  if (container) {
    var track = container.querySelector('.card-stack-track');
    var items = Array.prototype.slice.call(container.querySelectorAll('.card-stack-item'));
    var dots = Array.prototype.slice.call(container.querySelectorAll('.stack-dot'));
    var prevBtn = container.querySelector('.stack-prev');
    var nextBtn = container.querySelector('.stack-next');
    var total = items.length;
    var currentIndex = 0;
    var autoPlayTimer = null;
    var isDragging = false;
    var startX = 0;
    var currentX = 0;

    function updateStack() {
      var isMobile = window.innerWidth <= 767;
      var xStep = isMobile ? 38 : 55;
      var zStep = isMobile ? -40 : -50;
      var rotStep = isMobile ? 4 : 6;

      items.forEach(function (item, i) {
        var diff = (i - currentIndex) % total;
        if (diff > total / 2) diff -= total;
        if (diff < -total / 2) diff += total;

        item.classList.remove('active', 'prev', 'next', 'hidden');

        if (diff === 0) {
          // Active Front Center Card
          item.classList.add('active');
          item.style.transform = 'translateX(0px) scale(1) translateZ(0px) rotateY(0deg)';
          item.style.zIndex = '10';
          item.style.opacity = '1';
          item.style.filter = 'none';
          item.style.cursor = 'grab';
          item.style.pointerEvents = 'auto';
        } else if (diff === 1) {
          // Immediate Right Card
          item.classList.add('next');
          item.style.transform = 'translateX(' + xStep + 'px) scale(0.91) translateZ(' + zStep + 'px) rotateY(-' + rotStep + 'deg)';
          item.style.zIndex = '8';
          item.style.opacity = '0.92';
          item.style.filter = 'brightness(0.93)';
          item.style.cursor = 'pointer';
          item.style.pointerEvents = 'auto';
        } else if (diff === 2) {
          // Far Right Card
          item.style.transform = 'translateX(' + Math.round(xStep * 1.85) + 'px) scale(0.82) translateZ(' + (zStep * 2) + 'px) rotateY(-' + (rotStep * 1.6) + 'deg)';
          item.style.zIndex = '6';
          item.style.opacity = '0.78';
          item.style.filter = 'brightness(0.85)';
          item.style.cursor = 'pointer';
          item.style.pointerEvents = 'auto';
        } else if (diff === -1) {
          // Immediate Left Card
          item.classList.add('prev');
          item.style.transform = 'translateX(-' + xStep + 'px) scale(0.91) translateZ(' + zStep + 'px) rotateY(' + rotStep + 'deg)';
          item.style.zIndex = '8';
          item.style.opacity = '0.92';
          item.style.filter = 'brightness(0.93)';
          item.style.cursor = 'pointer';
          item.style.pointerEvents = 'auto';
        } else if (diff === -2) {
          // Far Left Card
          item.style.transform = 'translateX(-' + Math.round(xStep * 1.85) + 'px) scale(0.82) translateZ(' + (zStep * 2) + 'px) rotateY(' + (rotStep * 1.6) + 'deg)';
          item.style.zIndex = '6';
          item.style.opacity = '0.78';
          item.style.filter = 'brightness(0.85)';
          item.style.cursor = 'pointer';
          item.style.pointerEvents = 'auto';
        } else {
          // Hidden Behind
          item.classList.add('hidden');
          item.style.transform = 'translateX(0px) scale(0.72) translateZ(' + (zStep * 3) + 'px)';
          item.style.zIndex = '1';
          item.style.opacity = '0';
          item.style.pointerEvents = 'none';
        }
      });

      dots.forEach(function (dot, idx) {
        dot.classList.toggle('active', idx === currentIndex);
      });
    }

    function goTo(index) {
      currentIndex = (index + total) % total;
      updateStack();
    }

    function next() {
      goTo(currentIndex + 1);
    }

    function prev() {
      goTo(currentIndex - 1);
    }

    // Click card to jump to it
    items.forEach(function (item, index) {
      item.addEventListener('click', function (e) {
        var diff = (index - currentIndex) % total;
        if (diff > total / 2) diff -= total;
        if (diff < -total / 2) diff += total;
        if (diff !== 0) {
          e.preventDefault();
          goTo(index);
          resetAutoplay();
        }
      });
    });

    // Controls
    if (prevBtn) prevBtn.addEventListener('click', function () { prev(); resetAutoplay(); });
    if (nextBtn) nextBtn.addEventListener('click', function () { next(); resetAutoplay(); });

    // Indicators
    dots.forEach(function (dot) {
      dot.addEventListener('click', function () {
        var idx = parseInt(dot.getAttribute('data-index'), 10);
        goTo(idx);
        resetAutoplay();
      });
    });

    // Drag / Swipe Gestures
    function onPointerDown(e) {
      isDragging = true;
      startX = e.type.indexOf('touch') !== -1 ? e.touches[0].clientX : e.clientX;
      currentX = startX;
      stopAutoplay();
    }

    function onPointerMove(e) {
      if (!isDragging) return;
      currentX = e.type.indexOf('touch') !== -1 ? e.touches[0].clientX : e.clientX;
    }

    function onPointerUp() {
      if (!isDragging) return;
      isDragging = false;
      var delta = currentX - startX;
      if (delta > 40) {
        prev();
      } else if (delta < -40) {
        next();
      }
      startAutoplay();
    }

    if (track) {
      track.addEventListener('mousedown', onPointerDown);
      window.addEventListener('mousemove', onPointerMove);
      window.addEventListener('mouseup', onPointerUp);

      track.addEventListener('touchstart', onPointerDown, { passive: true });
      window.addEventListener('touchmove', onPointerMove, { passive: true });
      window.addEventListener('touchend', onPointerUp);
    }

    // Autoplay
    function startAutoplay() {
      stopAutoplay();
      autoPlayTimer = setInterval(next, 3800);
    }

    function stopAutoplay() {
      if (autoPlayTimer) clearInterval(autoPlayTimer);
      autoPlayTimer = null;
    }

    function resetAutoplay() {
      stopAutoplay();
      startAutoplay();
    }

    container.addEventListener('mouseenter', stopAutoplay);
    container.addEventListener('mouseleave', startAutoplay);
    window.addEventListener('resize', updateStack);

    updateStack();
    startAutoplay();
  }
})();