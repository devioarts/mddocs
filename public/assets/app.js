(function () {
  const root = document.documentElement;
  const savedTheme = localStorage.getItem("docs-theme");

  if (savedTheme) {
    root.dataset.theme = savedTheme;
  }

  const themeToggle = document.querySelector("[data-theme-toggle]");
  const menuToggle = document.querySelector("[data-menu-toggle]");
  const dialog = document.querySelector("[data-search-dialog]");
  const input = document.querySelector("[data-search-input]");
  const results = document.querySelector("[data-search-results]");
  const searchOpen = document.querySelector("[data-search-open]");
  const currentDoc = document.body.dataset.currentDoc || document.querySelector("[data-doc]")?.dataset.doc || "";
  let searchTimer = 0;

  themeToggle?.addEventListener("click", function () {
    const next = root.dataset.theme === "dark" ? "light" : "dark";
    root.dataset.theme = next;
    localStorage.setItem("docs-theme", next);
  });

  menuToggle?.addEventListener("click", function () {
    document.body.classList.toggle("nav-open");
  });

  document.addEventListener("click", function (event) {
    const target = event.target;

    if (target instanceof Element && target.closest(".sidebar a")) {
      document.body.classList.remove("nav-open");
    }
  });

  function openSearch() {
    if (!dialog || !input) {
      return;
    }

    dialog.hidden = false;
    input.focus();
    input.select();
  }

  function closeSearch() {
    if (dialog) {
      dialog.hidden = true;
    }
  }

  searchOpen?.addEventListener("click", openSearch);

  dialog?.addEventListener("click", function (event) {
    if (event.target === dialog) {
      closeSearch();
    }
  });

  document.addEventListener("keydown", function (event) {
    if (event.key === "/" && !isTypingTarget(event.target)) {
      event.preventDefault();
      openSearch();
    }

    if (event.key === "Escape") {
      closeSearch();
      document.body.classList.remove("nav-open");
    }
  });

  input?.addEventListener("input", function () {
    clearTimeout(searchTimer);
    searchTimer = window.setTimeout(runSearch, 120);
  });

  async function runSearch() {
    if (!input || !results || !currentDoc) {
      return;
    }

    const query = input.value.trim();

    if (query.length < 2) {
      results.innerHTML = "";
      return;
    }

    const response = await fetch("/search?doc=" + encodeURIComponent(currentDoc) + "&q=" + encodeURIComponent(query));
    const items = await response.json();

    results.innerHTML = items.length
      ? items.map((item) => renderResult(item, query)).join("")
      : '<div class="search-result"><strong>No results found</strong><p>Try another search term.</p></div>';
  }

  function renderResult(item, query) {
    return '<a class="search-result" href="' + escapeHtml(item.url) + '">' +
      "<strong>" + escapeHtml(item.title) + "</strong>" +
      "<span>" + escapeHtml(item.breadcrumb || item.path) + "</span>" +
      "<p>" + highlight(item.excerpt || "", query) + "</p>" +
      "</a>";
  }

  function highlight(text, query) {
    const escaped = escapeHtml(text);
    const needle = query.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
    return escaped.replace(new RegExp("(" + needle + ")", "ig"), "<mark>$1</mark>");
  }

  function escapeHtml(value) {
    return String(value)
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;");
  }

  function isTypingTarget(target) {
    return target instanceof HTMLInputElement ||
      target instanceof HTMLTextAreaElement ||
      target instanceof HTMLSelectElement ||
      (target instanceof HTMLElement && target.isContentEditable);
  }

  const tocLinks = Array.from(document.querySelectorAll(".toc-links a"));
  const headings = tocLinks
    .map((link) => document.getElementById(decodeURIComponent(link.hash.slice(1))))
    .filter(Boolean);

  if ("IntersectionObserver" in window && headings.length) {
    const observer = new IntersectionObserver(function (entries) {
      const visible = entries
        .filter((entry) => entry.isIntersecting)
        .sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top)[0];

      if (!visible) {
        return;
      }

      tocLinks.forEach((link) => {
        link.classList.toggle("is-active", link.hash === "#" + visible.target.id);
      });
    }, { rootMargin: "-90px 0px -70% 0px", threshold: 0.01 });

    headings.forEach((heading) => observer.observe(heading));
  }

  if (tocLinks.length) {
    const lastLink = tocLinks[tocLinks.length - 1];

    window.addEventListener("scroll", function () {
      const atBottom = window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 2;

      if (atBottom) {
        tocLinks.forEach((link) => link.classList.toggle("is-active", link === lastLink));
      }
    }, { passive: true });
  }
})();
