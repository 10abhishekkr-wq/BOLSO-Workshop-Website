(function () {
  var prices = window.bolsoPrices || {};
  var workshop = document.getElementById('workshop');
  var mode = document.getElementById('mode');
  var priceDisplay = document.getElementById('priceDisplay');
  var priceNote = document.getElementById('priceNote');

  var optPayOffline = document.getElementById('optPayOffline');
  var optPayOnline = document.getElementById('optPayOnline');
  var onlineOnlyNotice = document.getElementById('onlineOnlyNotice');
  var submitBtn = document.querySelector('.registration-form .submit-btn');

  function updatePrice() {
    if (!workshop || !mode || !priceDisplay) return;
    var key = workshop.value + '_' + mode.value;
    var price = prices[key] || 399;
    priceDisplay.textContent = '₹' + Number(price).toLocaleString('en-IN');
    if (priceNote) {
      priceNote.textContent = mode.value === 'offline' && workshop.value === '5-day'
        ? 'Materials, colours & artist colour palettes provided to keep.'
        : mode.value === 'offline'
          ? 'Offline in-person studio batch.'
          : mode.value === 'online'
            ? 'Live classes over Google Meet.'
            : 'Final details shared after registration.';
    }
    updatePaymentMethods(price);
  }

  function updatePaymentMethods(currentPrice) {
    if (!mode) return;
    var isOffline = mode.value === 'offline';
    var offlineInput = optPayOffline ? optPayOffline.querySelector('input') : null;
    var onlineInput = optPayOnline ? optPayOnline.querySelector('input') : null;

    if (isOffline) {
      if (optPayOffline) optPayOffline.classList.remove('disabled-opt');
      if (offlineInput) offlineInput.disabled = false;
      if (onlineOnlyNotice) onlineOnlyNotice.classList.add('d-none');
    } else {
      if (optPayOffline) optPayOffline.classList.add('disabled-opt');
      if (offlineInput) {
        offlineInput.disabled = true;
        if (offlineInput.checked && onlineInput) {
          onlineInput.checked = true;
        }
      }
      if (onlineOnlyNotice) onlineOnlyNotice.classList.remove('d-none');
    }

    // Sync is-selected class on card containers
    if (optPayOnline) {
      if (onlineInput && onlineInput.checked) {
        optPayOnline.classList.add('is-selected');
      } else {
        optPayOnline.classList.remove('is-selected');
      }
    }
    if (optPayOffline) {
      if (offlineInput && offlineInput.checked && isOffline) {
        optPayOffline.classList.add('is-selected');
      } else {
        optPayOffline.classList.remove('is-selected');
      }
    }

    if (submitBtn) {
      var selectedMethod = document.querySelector('input[name="payment_method"]:checked');
      var methodVal = selectedMethod ? selectedMethod.value : 'online';
      var formattedPrice = currentPrice ? '₹' + Number(currentPrice).toLocaleString('en-IN') : priceDisplay.textContent;

      if (isOffline && methodVal === 'offline') {
        submitBtn.innerHTML = 'Reserve Spot &amp; Pay at Studio (' + formattedPrice + ') <i class="bi bi-geo-alt"></i>';
      } else {
        submitBtn.innerHTML = 'Proceed to Pay ' + formattedPrice + ' <i class="bi bi-arrow-up-right"></i>';
      }
    }
  }

  if (workshop) workshop.addEventListener('change', updatePrice);
  if (mode) mode.addEventListener('change', updatePrice);
  document.querySelectorAll('input[name="payment_method"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
      var key = (workshop ? workshop.value : '2-day') + '_' + (mode ? mode.value : 'online');
      var price = prices[key] || 399;
      updatePaymentMethods(price);
    });
  });

  // Initial call on page load if elements exist
  if (workshop && mode) {
    updatePrice();
  }

  document.querySelectorAll('a[href^="#"]').forEach(function (link) {
    link.addEventListener('click', function (event) {
      var href = link.getAttribute('href');
      if (!href || href === '#' || href.length <= 1) return;
      try {
        var target = document.querySelector(href);
        if (target) {
          event.preventDefault();
          target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      } catch (err) {}
    });
  });  // 3D Card Stack Slider (Cover-flow / Stacked Deck matching user reference)
  function initCardStack(container) {
    if (!container) return;
    var track = container.querySelector('.card-stack-track');
    var items = Array.prototype.slice.call(container.querySelectorAll('.card-stack-item'));
    var dots = Array.prototype.slice.call(container.querySelectorAll('.stack-dot'));
    var prevBtn = container.querySelector('.stack-prev');
    var nextBtn = container.querySelector('.stack-next');
    var total = items.length;
    if (total === 0) return;

    var currentIndex = 0;
    var autoPlayTimer = null;
    var isDragging = false;
    var hasDragged = false;
    var startX = 0;
    var currentX = 0;

    function updateStack() {
      var isMobile = window.innerWidth <= 767;
      var xStep = isMobile ? 40 : 60;
      var zStep = isMobile ? -35 : -45;
      var rotStep = isMobile ? 3 : 4.5;

      items.forEach(function (item, i) {
        var diff = (i - currentIndex) % total;
        if (diff > total / 2) diff -= total;
        if (diff < -total / 2) diff += total;

        item.classList.remove('active', 'prev', 'next', 'far-prev', 'far-next', 'hidden');

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
          item.style.transform = 'translateX(' + xStep + 'px) scale(0.92) translateZ(' + zStep + 'px) rotateY(-' + rotStep + 'deg)';
          item.style.zIndex = '8';
          item.style.opacity = '0.94';
          item.style.filter = 'brightness(0.95)';
          item.style.cursor = 'pointer';
          item.style.pointerEvents = 'auto';
        } else if (diff === 2) {
          // Far Right Card
          item.classList.add('far-next');
          item.style.transform = 'translateX(' + Math.round(xStep * 1.85) + 'px) scale(0.84) translateZ(' + (zStep * 2) + 'px) rotateY(-' + (rotStep * 1.5) + 'deg)';
          item.style.zIndex = '6';
          item.style.opacity = '0.82';
          item.style.filter = 'brightness(0.88)';
          item.style.cursor = 'pointer';
          item.style.pointerEvents = 'auto';
        } else if (diff === -1) {
          // Immediate Left Card
          item.classList.add('prev');
          item.style.transform = 'translateX(-' + xStep + 'px) scale(0.92) translateZ(' + zStep + 'px) rotateY(' + rotStep + 'deg)';
          item.style.zIndex = '8';
          item.style.opacity = '0.94';
          item.style.filter = 'brightness(0.95)';
          item.style.cursor = 'pointer';
          item.style.pointerEvents = 'auto';
        } else if (diff === -2) {
          // Far Left Card
          item.classList.add('far-prev');
          item.style.transform = 'translateX(-' + Math.round(xStep * 1.85) + 'px) scale(0.84) translateZ(' + (zStep * 2) + 'px) rotateY(' + (rotStep * 1.5) + 'deg)';
          item.style.zIndex = '6';
          item.style.opacity = '0.82';
          item.style.filter = 'brightness(0.88)';
          item.style.cursor = 'pointer';
          item.style.pointerEvents = 'auto';
        } else {
          // Hidden Behind
          item.classList.add('hidden');
          item.style.transform = 'translateX(0px) scale(0.70) translateZ(' + (zStep * 3) + 'px)';
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

    // Direct click on card to bring it to center
    items.forEach(function (item, index) {
      item.addEventListener('click', function (e) {
        if (hasDragged) return;
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

    // Arrow navigation
    if (prevBtn) {
      prevBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        prev();
        resetAutoplay();
      });
    }
    if (nextBtn) {
      nextBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        next();
        resetAutoplay();
      });
    }

    // Indicator dots
    dots.forEach(function (dot) {
      dot.addEventListener('click', function (e) {
        e.stopPropagation();
        var idx = parseInt(dot.getAttribute('data-index'), 10);
        if (!isNaN(idx)) {
          goTo(idx);
          resetAutoplay();
        }
      });
    });

    // Drag / Swipe Gestures
    function onPointerDown(e) {
      isDragging = true;
      hasDragged = false;
      startX = e.type.indexOf('touch') !== -1 ? e.touches[0].clientX : e.clientX;
      currentX = startX;
      if (track) track.classList.add('is-dragging');
      stopAutoplay();
    }

    function onPointerMove(e) {
      if (!isDragging) return;
      currentX = e.type.indexOf('touch') !== -1 ? e.touches[0].clientX : e.clientX;
      if (Math.abs(currentX - startX) > 8) {
        hasDragged = true;
      }
    }

    function onPointerUp() {
      if (!isDragging) return;
      isDragging = false;
      if (track) track.classList.remove('is-dragging');
      var delta = currentX - startX;
      if (delta > 35) {
        prev();
      } else if (delta < -35) {
        next();
      }
      setTimeout(function () {
        hasDragged = false;
      }, 60);
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

  // Initialize all sliders with [data-card-stack] or .card-stack-container
  var sliders = document.querySelectorAll('[data-card-stack], .card-stack-container');
  sliders.forEach(function (slider) {
    initCardStack(slider);
  });

  // Global password visibility toggle
  window.togglePasswordVisibility = function (fieldId, btn) {
    var field = document.getElementById(fieldId);
    if (!field) return;
    var icon = btn ? btn.querySelector('i') : null;
    if (field.type === 'password') {
      field.type = 'text';
      if (icon) {
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
      }
      if (btn) {
        btn.setAttribute('title', 'Hide password');
        btn.setAttribute('aria-label', 'Hide password');
      }
    } else {
      field.type = 'password';
      if (icon) {
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
      }
      if (btn) {
        btn.setAttribute('title', 'Show password');
        btn.setAttribute('aria-label', 'Show password');
      }
    }
  };

  // =========================================================================
  // BOLSO ARTISAN WELCOME & ENTRANCE SPLASH CONTROLLER
  // =========================================================================
  var splash = document.getElementById('bolsoSplashScreen');
  if (splash) {
    var isStandalone = splash.classList.contains('standalone-mode');

    // Safely read storage and cookies
    var hasSeenSplash = false;
    try {
      hasSeenSplash = sessionStorage.getItem('bolso_splash_seen') === '1' ||
                      localStorage.getItem('bolso_splash_seen') === '1' ||
                      document.cookie.indexOf('bolso_splash_seen=1') !== -1;
    } catch (e) {}

    var isReplay = window.location.search.indexOf('replay=1') !== -1;
    var isEnterReq = window.location.search.indexOf('enter=1') !== -1 ||
                     window.location.search.indexOf('nosplash=1') !== -1 ||
                     window.location.search.indexOf('home=1') !== -1;

    var btnEnter = document.getElementById('btnSplashEnter');
    var btnSkip = document.getElementById('btnSplashSkip');
    var ringFill = document.getElementById('splashRingFill');
    var autoDismissTimer = null;
    var progressInterval = null;
    var startTime = Date.now();
    var duration = 3800; // 3.8s total entrance experience
    var isDismissed = false;

    function markSeen() {
      try {
        sessionStorage.setItem('bolso_splash_seen', '1');
        localStorage.setItem('bolso_splash_seen', '1');
        document.cookie = 'bolso_splash_seen=1; path=/; max-age=' + (86400 * 30);
      } catch (e) {}
    }

    function dismissSplash(immediate) {
      if (isDismissed) return;
      isDismissed = true;
      if (autoDismissTimer) clearTimeout(autoDismissTimer);
      if (progressInterval) clearInterval(progressInterval);

      markSeen();

      if (isStandalone) {
        window.location.href = 'index.php?enter=1';
        return;
      }

      splash.classList.add('splash-fade-out');
      setTimeout(function () {
        splash.classList.add('is-hidden');
      }, 850);
    }

    // Check if should be shown
    if ((!isStandalone && hasSeenSplash && !isReplay) || isEnterReq) {
      splash.classList.add('is-hidden');
    } else {
      splash.classList.remove('is-hidden');

      // Animate circular progress ring
      if (ringFill) {
        var totalOffset = 107; // 2 * PI * 17
        ringFill.style.strokeDashoffset = totalOffset;
        progressInterval = setInterval(function () {
          var elapsed = Date.now() - startTime;
          var pct = Math.min(1, elapsed / duration);
          ringFill.style.strokeDashoffset = totalOffset * (1 - pct);
          if (pct >= 1) {
            clearInterval(progressInterval);
          }
        }, 30);
      }

      // Auto dismiss after 3.8s on home page
      if (!isStandalone) {
        autoDismissTimer = setTimeout(function () {
          dismissSplash(false);
        }, duration);
      }

      if (btnEnter) {
        btnEnter.addEventListener('click', function (e) {
          if (isStandalone) {
            markSeen();
            // Let normal link navigation proceed to index.php?enter=1
          } else {
            e.preventDefault();
            dismissSplash(false);
          }
        });
      }

      if (btnSkip) {
        btnSkip.addEventListener('click', function (e) {
          if (isStandalone) {
            markSeen();
            // Let normal link navigation proceed to index.php?enter=1
          } else {
            e.preventDefault();
            dismissSplash(true);
          }
        });
      }

      // Keyboard accessibility
      window.addEventListener('keydown', function (e) {
        if (!isDismissed && (e.key === 'Escape' || e.key === 'Enter' || e.key === ' ')) {
          e.preventDefault();
          dismissSplash(true);
        }
      });
    }
  }

  // Handle footer or nav replay button clicks
  var replayButtons = document.querySelectorAll('#btnReplayIntro, #btnReplayIntroBottom');
  replayButtons.forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      try {
        sessionStorage.removeItem('bolso_splash_seen');
        localStorage.removeItem('bolso_splash_seen');
        document.cookie = 'bolso_splash_seen=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT';
      } catch (err) {}
      var splashEl = document.getElementById('bolsoSplashScreen');
      if (splashEl && !splashEl.classList.contains('standalone-mode')) {
        e.preventDefault();
        window.location.href = 'index.php?replay=1';
      }
    });
  });

  /* ==========================================================================
     Interactive Before & After Garment Transformation Slider
     ========================================================================== */
  var comparisonSlider = document.getElementById('artisanComparisonSlider');
  if (comparisonSlider) {
    var overlay = document.getElementById('comparisonOverlay');
    var handle = document.getElementById('comparisonHandle');
    var isDragging = false;
    var sliderRect = null;

    function setSliderPosition(xPercentage) {
      var clamped = Math.max(0, Math.min(100, xPercentage));
      comparisonSlider.setAttribute('data-position', clamped);
      if (overlay) {
        overlay.style.clipPath = 'polygon(0 0, ' + clamped + '% 0, ' + clamped + '% 100%, 0 100%)';
      }
      if (handle) {
        handle.style.left = clamped + '%';
        handle.setAttribute('aria-valuenow', Math.round(clamped));
      }
    }

    function updateFromClientX(clientX) {
      if (!sliderRect) {
        sliderRect = comparisonSlider.getBoundingClientRect();
      }
      var offsetX = clientX - sliderRect.left;
      var pct = (offsetX / sliderRect.width) * 100;
      setSliderPosition(pct);
    }

    function onPointerDown(e) {
      isDragging = true;
      sliderRect = comparisonSlider.getBoundingClientRect();
      comparisonSlider.classList.add('is-dragging');
      var clientX = e.clientX !== undefined ? e.clientX : (e.touches && e.touches[0] ? e.touches[0].clientX : 0);
      updateFromClientX(clientX);
    }

    function onPointerMove(e) {
      if (!isDragging) return;
      var clientX = e.clientX !== undefined ? e.clientX : (e.touches && e.touches[0] ? e.touches[0].clientX : 0);
      updateFromClientX(clientX);
    }

    function onPointerUp() {
      if (isDragging) {
        isDragging = false;
        comparisonSlider.classList.remove('is-dragging');
      }
    }

    // Desktop mouse events
    comparisonSlider.addEventListener('mousedown', onPointerDown);
    window.addEventListener('mousemove', onPointerMove);
    window.addEventListener('mouseup', onPointerUp);

    // Mobile touch events
    comparisonSlider.addEventListener('touchstart', onPointerDown, { passive: true });
    window.addEventListener('touchmove', onPointerMove, { passive: true });
    window.addEventListener('touchend', onPointerUp);

    // Keyboard accessibility for handle
    if (handle) {
      handle.addEventListener('keydown', function (e) {
        var current = parseFloat(comparisonSlider.getAttribute('data-position')) || 50;
        if (e.key === 'ArrowLeft' || e.key === 'ArrowDown') {
          e.preventDefault();
          setSliderPosition(current - 5);
        } else if (e.key === 'ArrowRight' || e.key === 'ArrowUp') {
          e.preventDefault();
          setSliderPosition(current + 5);
        }
      });
    }

    // Refresh rect on resize
    window.addEventListener('resize', function () {
      sliderRect = comparisonSlider.getBoundingClientRect();
    });

    // Auto-hint subtle oscillation on first scroll into view
    if ('IntersectionObserver' in window) {
      var hintObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting && !comparisonSlider.dataset.hinted) {
            comparisonSlider.dataset.hinted = 'true';
            comparisonSlider.classList.add('hint-animating');
            setTimeout(function () {
              setSliderPosition(35);
              setTimeout(function () {
                setSliderPosition(65);
                setTimeout(function () {
                  setSliderPosition(50);
                  comparisonSlider.classList.remove('hint-animating');
                }, 450);
              }, 450);
            }, 350);
            hintObserver.disconnect();
          }
        });
      }, { threshold: 0.3 });
      hintObserver.observe(comparisonSlider);
    } else {
      setSliderPosition(50);
    }
  }
})();