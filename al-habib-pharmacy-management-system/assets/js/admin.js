(function () {
  function ready(callback) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", callback);
    } else {
      callback();
    }
  }

  ready(function () {
    document.querySelectorAll("[data-confirm]").forEach(function (element) {
      element.addEventListener("click", function (event) {
        if (!window.confirm(element.getAttribute("data-confirm"))) {
          event.preventDefault();
        }
      });
    });

    document.querySelectorAll("form").forEach(function (form) {
      form.addEventListener("submit", function (event) {
        if (form.getAttribute("onsubmit")) {
          return;
        }

        if (event.submitter && (event.submitter.getAttribute("data-confirm") || event.submitter.getAttribute("onclick"))) {
          return;
        }

        var deleteButton = event.submitter && (
          event.submitter.classList.contains("delete-btn") ||
          event.submitter.classList.contains("delete") ||
          event.submitter.value === "delete" ||
          event.submitter.name === "delete_product"
        );

        var deleteAction = form.querySelector("input[name='action'][value^='delete']");
        if ((deleteButton || deleteAction) && !form.dataset.confirmed) {
          if (!window.confirm("Delete this record?")) {
            event.preventDefault();
          }
        }
      });
    });

    var productImageInput = document.getElementById("product_image");
    var productImagePreview = document.getElementById("productImagePreview");
    if (productImageInput && productImagePreview) {
      productImageInput.addEventListener("change", function () {
        var file = productImageInput.files && productImageInput.files[0];
        if (file) {
          productImagePreview.src = URL.createObjectURL(file);
        }
      });
    }

    document.querySelectorAll("form").forEach(function (form) {
      form.addEventListener("submit", function (event) {
        var invalid = false;
        form.querySelectorAll("[required]").forEach(function (field) {
          field.classList.remove("field-error");
          if (!field.value.trim()) {
            invalid = true;
            field.classList.add("field-error");
          }
        });

        if (invalid) {
          event.preventDefault();
          var first = form.querySelector(".field-error");
          if (first) {
            first.focus();
          }
        }
      });
    });
  });
})();
