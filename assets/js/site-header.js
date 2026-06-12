/* NeuronAlgo — Custom Site Header (HF-1.2: scroll state, mobile menu, ⌘K command palette) */
(function () {
	"use strict";

	var header = document.getElementById("na-site-header");
	if (!header) return;

	/* -------------------- Scrolled state -------------------- */
	var ticking = false;
	function onScroll() {
		if (ticking) return;
		ticking = true;
		window.requestAnimationFrame(function () {
			header.classList.toggle("is-scrolled", window.scrollY > 8);
			ticking = false;
		});
	}
	window.addEventListener("scroll", onScroll, { passive: true });
	onScroll();

	/* -------------------- Mobile menu -------------------- */
	var toggle = header.querySelector(".na-site-header__toggle");
	var mobile = document.getElementById("na-site-header-mobile");
	var mobileCloseTimer = null;

	function isMobileOpen() {
		return !!mobile && mobile.getAttribute("aria-expanded") === "true";
	}
	function openMobile() {
		if (!mobile) return;
		if (mobileCloseTimer) {
			clearTimeout(mobileCloseTimer);
			mobileCloseTimer = null;
		}
		mobile.hidden = false;
		void mobile.offsetHeight; // force reflow so the transition runs
		mobile.setAttribute("aria-expanded", "true");
		if (toggle) toggle.setAttribute("aria-expanded", "true");
		document.body.classList.add("na-header-open");
	}
	function closeMobile() {
		if (!mobile) return;
		mobile.setAttribute("aria-expanded", "false");
		if (toggle) toggle.setAttribute("aria-expanded", "false");
		document.body.classList.remove("na-header-open");
		mobileCloseTimer = window.setTimeout(function () {
			mobile.hidden = true;
		}, 320);
	}
	if (toggle && mobile) {
		toggle.addEventListener("click", function () {
			if (isMobileOpen()) closeMobile();
			else openMobile();
		});
		mobile.addEventListener("click", function (e) {
			if (e.target.closest("a")) closeMobile();
		});
	}

	/* Swipe up to dismiss the mobile menu (arms only at the top of the scroll) */
	if (mobile) {
		var swStartY = 0;
		var swStartX = 0;
		var swArmed = false;
		mobile.addEventListener(
			"touchstart",
			function (e) {
				if (!isMobileOpen() || e.touches.length !== 1) {
					swArmed = false;
					return;
				}
				swArmed = mobile.scrollTop <= 0;
				swStartY = e.touches[0].clientY;
				swStartX = e.touches[0].clientX;
			},
			{ passive: true }
		);
		mobile.addEventListener(
			"touchend",
			function (e) {
				if (!swArmed) return;
				swArmed = false;
				var t = e.changedTouches && e.changedTouches[0];
				if (!t) return;
				var dy = t.clientY - swStartY;
				var dx = t.clientX - swStartX;
				if (dy < -55 && Math.abs(dy) > Math.abs(dx) * 1.4) {
					closeMobile();
				}
			},
			{ passive: true }
		);
	}

	/* -------------------- Command palette (⌘K) -------------------- */
	var cmdk = document.getElementById("na-cmdk");
	if (cmdk) {
		var input = cmdk.querySelector(".na-cmdk__input");
		var list = cmdk.querySelector(".na-cmdk__list");
		var lastFocused = null;
		var items = [];
		var filtered = [];
		var activeIndex = 0;

		function buildItems() {
			var out = [];
			var seen = {};
			var links = header.querySelectorAll(
				".na-site-header__nav .na-nav__menu a"
			);
			Array.prototype.forEach.call(links, function (a) {
				var label = (a.textContent || "").trim();
				var href = a.getAttribute("href");
				if (!label || !href || seen[label]) return;
				seen[label] = true;
				out.push({ label: label, href: href, glyph: "\u21B3" });
			});
			var ctaLabel = cmdk.getAttribute("data-cta-label");
			var ctaUrl = cmdk.getAttribute("data-cta-url");
			if (ctaLabel && ctaUrl && !seen[ctaLabel]) {
				out.push({ label: ctaLabel, href: ctaUrl, glyph: "\u2192" });
			}
			return out;
		}

		function render() {
			list.innerHTML = "";
			if (!filtered.length) {
				var empty = document.createElement("li");
				empty.className = "na-cmdk__empty";
				empty.textContent = "No matches";
				list.appendChild(empty);
				return;
			}
			filtered.forEach(function (it, i) {
				var li = document.createElement("li");
				li.className =
					"na-cmdk__item" + (i === activeIndex ? " is-active" : "");
				li.setAttribute("role", "option");

				var glyph = document.createElement("span");
				glyph.className = "na-cmdk__glyph";
				glyph.textContent = it.glyph || "\u21B3";

				var label = document.createElement("span");
				label.className = "na-cmdk__label";
				label.textContent = it.label;

				li.appendChild(glyph);
				li.appendChild(label);
				li.addEventListener("click", function () {
					go(it);
				});
				li.addEventListener("mousemove", function () {
					if (activeIndex !== i) {
						activeIndex = i;
						updateActive();
					}
				});
				list.appendChild(li);
			});
		}

		function updateActive() {
			var nodes = list.querySelectorAll(".na-cmdk__item");
			Array.prototype.forEach.call(nodes, function (n, i) {
				var on = i === activeIndex;
				n.classList.toggle("is-active", on);
				if (on && n.scrollIntoView) n.scrollIntoView({ block: "nearest" });
			});
		}

		function applyFilter() {
			var q = (input.value || "").trim().toLowerCase();
			filtered = q
				? items.filter(function (it) {
						return it.label.toLowerCase().indexOf(q) !== -1;
				  })
				: items.slice();
			activeIndex = 0;
			render();
		}

		function go(it) {
			if (!it) return;
			closeCmdk();
			if (it.href) window.location.href = it.href;
		}

		function isCmdkOpen() {
			return !cmdk.hidden;
		}
		function openCmdk() {
			if (!cmdk.hidden) return;
			lastFocused = document.activeElement;
			items = buildItems();
			input.value = "";
			applyFilter();
			cmdk.hidden = false;
			cmdk.setAttribute("aria-hidden", "false");
			void cmdk.offsetHeight;
			cmdk.classList.add("is-open");
			document.body.classList.add("na-header-open");
			if (isMobileOpen()) closeMobile();
			window.setTimeout(function () {
				if (input) input.focus();
			}, 20);
		}
		function closeCmdk() {
			if (cmdk.hidden) return;
			cmdk.classList.remove("is-open");
			cmdk.setAttribute("aria-hidden", "true");
			document.body.classList.remove("na-header-open");
			window.setTimeout(function () {
				cmdk.hidden = true;
			}, 200);
			if (lastFocused && lastFocused.focus) lastFocused.focus();
		}

		Array.prototype.forEach.call(
			document.querySelectorAll("[data-na-cmdk-open]"),
			function (btn) {
				btn.addEventListener("click", function (e) {
					e.preventDefault();
					openCmdk();
				});
			}
		);
		Array.prototype.forEach.call(
			cmdk.querySelectorAll("[data-na-cmdk-close]"),
			function (el) {
				el.addEventListener("click", closeCmdk);
			}
		);

		document.addEventListener("keydown", function (e) {
			var key = e.key || "";
			if ((e.metaKey || e.ctrlKey) && (key === "k" || key === "K")) {
				e.preventDefault();
				if (isCmdkOpen()) closeCmdk();
				else openCmdk();
				return;
			}
			if (!isCmdkOpen()) {
				if (key === "Escape" && isMobileOpen()) closeMobile();
				return;
			}
			if (key === "Escape") {
				e.preventDefault();
				closeCmdk();
			} else if (key === "ArrowDown") {
				e.preventDefault();
				if (filtered.length) {
					activeIndex = (activeIndex + 1) % filtered.length;
					updateActive();
				}
			} else if (key === "ArrowUp") {
				e.preventDefault();
				if (filtered.length) {
					activeIndex =
						(activeIndex - 1 + filtered.length) % filtered.length;
					updateActive();
				}
			} else if (key === "Enter") {
				e.preventDefault();
				go(filtered[activeIndex]);
			}
		});

		if (input) input.addEventListener("input", applyFilter);
	}
})();
