(function () {
  function ready(callback) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", callback);
    } else {
      callback();
    }
  }

  ready(function () {
    var header = document.querySelector(".top-header, .header, body > header");
    if (!header) {
      return;
    }

    var nav = header.querySelector(".main-nav, .nav, nav");
    if (nav && !header.querySelector(".mobile-menu-toggle")) {
      var toggle = document.createElement("button");
      toggle.type = "button";
      toggle.className = "mobile-menu-toggle";
      toggle.setAttribute("aria-label", "Toggle menu");
      toggle.setAttribute("aria-expanded", "false");
      toggle.textContent = "Menu";
      header.insertBefore(toggle, nav);

      toggle.addEventListener("click", function () {
        var isOpen = nav.classList.toggle("is-open");
        toggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
      });
    }

    var currentPage = window.location.pathname.split("/").pop() || "home.php";
    document.querySelectorAll(".main-nav a, .nav a, header nav a").forEach(function (link) {
      var linkPage = (link.getAttribute("href") || "").split("?")[0].split("#")[0];
      if (linkPage === currentPage) {
        link.classList.add("active");
      }
    });

    document.querySelectorAll("form[data-validate], .simple-form, .auth-card form, .box form").forEach(function (form) {
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
          var firstInvalid = form.querySelector(".field-error");
          if (firstInvalid) {
            firstInvalid.focus();
          }
        }
      });
    });
  });
})();
