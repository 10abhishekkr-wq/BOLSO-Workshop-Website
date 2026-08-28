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
})();