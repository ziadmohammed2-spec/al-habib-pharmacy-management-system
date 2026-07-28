(function () {
  function ready(callback) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", callback);
    } else {
      callback();
    }
  }

  ready(function () {
    document.querySelectorAll(".quantity button, form button[name='add_to_cart']").forEach(function (button) {
      button.addEventListener("click", function () {
        var form = button.closest("form");
        if (!form) {
          return;
        }

        window.setTimeout(function () {
          button.disabled = true;
          button.dataset.originalText = button.textContent;
          button.textContent = "Working...";
        }, 0);
      });
    });

    document.querySelectorAll("input[type='number'][name='quantity']").forEach(function (input) {
      input.addEventListener("change", function () {
        var min = Number(input.getAttribute("min") || "1");
        var max = Number(input.getAttribute("max") || "9999");
        var value = Number(input.value || min);

        if (value < min) {
          input.value = String(min);
        } else if (value > max) {
          input.value = String(max);
        }
      });
    });

    document.querySelectorAll("button[name='clear_cart'], button[name='remove_item']").forEach(function (button) {
      button.addEventListener("click", function (event) {
        var text = button.name === "clear_cart" ? "Clear all cart items?" : "Remove this item from cart?";
        if (!window.confirm(text)) {
          event.preventDefault();
        }
      });
    });
  });
})();
