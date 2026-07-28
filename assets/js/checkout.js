(function () {
  function ready(callback) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", callback);
    } else {
      callback();
    }
  }

  ready(function () {
    var options = document.querySelectorAll(".payment-option");

    function refreshPaymentState() {
      options.forEach(function (option) {
        var radio = option.querySelector("input[type='radio']");
        option.classList.toggle("active", !!(radio && radio.checked));
      });
    }

    options.forEach(function (option) {
      option.addEventListener("click", function () {
        var radio = option.querySelector("input[type='radio']");
        if (radio) {
          radio.checked = true;
          radio.dispatchEvent(new Event("change", { bubbles: true }));
        }
      });
    });

    document.querySelectorAll(".payment-option input[type='radio']").forEach(function (radio) {
      radio.addEventListener("change", refreshPaymentState);
    });

    refreshPaymentState();

    var checkoutForm = document.querySelector("form[action='checkout.php']");
    if (checkoutForm) {
      checkoutForm.addEventListener("submit", function (event) {
        if (!checkoutForm.querySelector("input[name='payment_method']:checked")) {
          event.preventDefault();
          var paymentCard = document.querySelector(".payment-card");
          var existing = paymentCard && paymentCard.querySelector(".form-feedback");
          if (!existing && paymentCard) {
            existing = document.createElement("div");
            existing.className = "form-feedback";
            existing.textContent = "Please choose a payment method.";
            paymentCard.insertBefore(existing, paymentCard.querySelector(".place-order"));
          }
          if (paymentCard) {
            paymentCard.scrollIntoView({ behavior: "smooth", block: "center" });
          }
        }
      });
    }
  });
})();

(function () {
  const radios = document.querySelectorAll('input[name="payment_method"]');
  const box = document.getElementById('paymentTransferBox');
  if (!radios.length || !box) return;

  function updatePaymentBox() {
    const selected = document.querySelector('input[name="payment_method"]:checked');
    if (!selected) return;
    if (selected.value === 'instapay') {
      box.style.display = 'block';
      box.textContent = 'Transfer Instapay payment to: 01012345678';
    } else if (selected.value === 'vodafone_cash') {
      box.style.display = 'block';
      box.textContent = 'Transfer Vodafone Cash payment to: 01123456789';
    } else {
      box.style.display = 'none';
      box.textContent = '';
    }
  }

  radios.forEach(function (radio) {
    radio.addEventListener('change', updatePaymentBox);
  });
  updatePaymentBox();
})();
