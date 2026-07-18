(function () {
  const root = document.documentElement;

  function setCookie(name, value) {
    document.cookie = name + "=" + value + "; path=/; max-age=31536000; SameSite=Lax";
  }

  const themeToggle = document.querySelector("[data-theme-toggle]");
  const layoutToggle = document.querySelector("[data-layout-toggle]");
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
    setCookie("docs-theme", next);
  });

  layoutToggle?.addEventListener("click", function () {
    const next = root.dataset.layout === "full" ? "current" : "full";
    root.dataset.layout = next;
    setCookie("docs-layout", next);
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

  function collapsedNavKeys() {
    return Array.from(document.querySelectorAll(".sidebar .nav-item.has-children.is-collapsed"))
      .map((item) => item.dataset.navKey)
      .filter(Boolean);
  }

  function persistNavState() {
    setCookie("docs-nav-collapsed", encodeURIComponent(JSON.stringify(collapsedNavKeys())));
  }

  document.addEventListener("click", function (event) {
    const target = event.target;

    if (!(target instanceof Element)) {
      return;
    }

    const toggle = target.closest("[data-nav-toggle]");
    const label = target.closest(".nav-row > span");
    const trigger = toggle || label;
    const item = trigger?.closest(".nav-item.has-children");

    if (!item) {
      return;
    }

    event.preventDefault();
    const collapsed = item.classList.toggle("is-collapsed");
    item.querySelector(":scope > .nav-row > [data-nav-toggle]")?.setAttribute("aria-expanded", collapsed ? "false" : "true");
    persistNavState();
  });

  function updateActiveNavItem(pathname) {
    document.querySelectorAll(".sidebar .nav-item.is-active").forEach((item) => item.classList.remove("is-active"));

    const activeLink = Array.from(document.querySelectorAll(".sidebar .nav-row > a")).find((link) => link.pathname === pathname);

    if (!activeLink) {
      return;
    }

    const activeItem = activeLink.closest(".nav-item");
    activeItem?.classList.add("is-active");

    let ancestor = activeItem?.parentElement?.closest(".nav-item.has-children");

    while (ancestor) {
      ancestor.classList.remove("is-collapsed");
      ancestor.querySelector(":scope > .nav-row > [data-nav-toggle]")?.setAttribute("aria-expanded", "true");
      ancestor = ancestor.parentElement?.closest(".nav-item.has-children");
    }
  }

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

  let tocObserver = null;
  let tocScrollHandler = null;

  function setupToc() {
    if (tocObserver) {
      tocObserver.disconnect();
      tocObserver = null;
    }

    if (tocScrollHandler) {
      window.removeEventListener("scroll", tocScrollHandler);
      tocScrollHandler = null;
    }

    const tocLinks = Array.from(document.querySelectorAll(".toc-links a"));
    const headings = tocLinks
      .map((link) => document.getElementById(decodeURIComponent(link.hash.slice(1))))
      .filter(Boolean);

    if ("IntersectionObserver" in window && headings.length) {
      tocObserver = new IntersectionObserver(function (entries) {
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

      headings.forEach((heading) => tocObserver.observe(heading));
    }

    if (tocLinks.length) {
      const lastLink = tocLinks[tocLinks.length - 1];

      tocScrollHandler = function () {
        const atBottom = window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 2;

        if (atBottom) {
          tocLinks.forEach((link) => link.classList.toggle("is-active", link === lastLink));
        }
      };

      window.addEventListener("scroll", tocScrollHandler, { passive: true });
    }
  }

  setupToc();

  const contentEl = document.querySelector(".content");
  const tocEl = document.querySelector(".toc-inner");
  const appShell = document.querySelector(".app-shell");

  function isPjaxable(anchor) {
    if (!appShell || !contentEl) {
      return false;
    }

    if (anchor.target === "_blank" || anchor.hasAttribute("download")) {
      return false;
    }

    if (anchor.origin !== window.location.origin) {
      return false;
    }

    if (anchor.pathname === "/" || anchor.pathname.startsWith("/_asset/") || anchor.pathname === "/search") {
      return false;
    }

    return true;
  }

  document.addEventListener("click", function (event) {
    if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
      return;
    }

    const anchor = event.target instanceof Element ? event.target.closest("a[href]") : null;

    if (!anchor || !isPjaxable(anchor)) {
      return;
    }

    const samePage = anchor.pathname === window.location.pathname && anchor.search === window.location.search;

    if (samePage) {
      if (anchor.hash && anchor.hash !== window.location.hash) {
        return;
      }

      event.preventDefault();
      return;
    }

    event.preventDefault();
    closeSearch();
    navigateTo(anchor.href);
  });

  window.addEventListener("popstate", function () {
    navigateTo(window.location.href, false);
  });

  let navToken = 0;

  async function navigateTo(url, push) {
    const shouldPush = push !== false;
    const token = ++navToken;

    root.classList.add("is-loading");

    try {
      const response = await fetch(url, { headers: { "X-Requested-With": "fetch" } });

      if (!response.ok) {
        throw new Error("Navigation request failed");
      }

      const html = await response.text();

      if (token !== navToken) {
        return;
      }

      const parsed = new DOMParser().parseFromString(html, "text/html");
      const newShell = parsed.querySelector(".app-shell");
      const newContent = parsed.querySelector(".content");

      if (!newShell || !newContent || !appShell || newShell.dataset.doc !== appShell.dataset.doc) {
        window.location.href = url;
        return;
      }

      document.title = parsed.title;
      contentEl.innerHTML = newContent.innerHTML;

      const newToc = parsed.querySelector(".toc-inner");

      if (tocEl && newToc) {
        tocEl.innerHTML = newToc.innerHTML;
      }

      if (shouldPush) {
        history.pushState({}, "", url);
      }

      updateActiveNavItem(new URL(url, window.location.href).pathname);
      window.scrollTo(0, 0);
      document.body.classList.remove("nav-open");

      if (window.Prism) {
        Prism.highlightAllUnder(contentEl);
      }

      setupToc();
    } catch (error) {
      window.location.href = url;
    } finally {
      if (token === navToken) {
        root.classList.remove("is-loading");
      }
    }
  }
})();
