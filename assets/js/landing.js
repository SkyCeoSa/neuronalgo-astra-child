/* ==========================================================================
 * NeuronAlgo - landing.js  ::  "The Desk" concept engine  (PRO POLISH)
 * Vanilla ES6, drop-in: <script src="../assets/js/landing.js" defer>
 *
 * Modules:
 *   - deckChart     : central live equity chart (crosshair + timeframes + now-marker)
 *   - storyMorph    : scroll-driven morphing protagonist chart (the thread)
 *   - cmdk          : command palette (Cmd/Ctrl+K) + focus trap -- the primary CTA
 *   - signalFeed    : streaming BUY/SELL feed (market-correlated)
 *   - tradeFeed     : streaming trade log (transparency, now live)
 *   - heatmap       : live market heatmap tiles w/ sparklines + price-flash
 *   - ticker | clock | livePnl (odometer) | countUp | scrollProgress | deskGlow
 *
 * Pro layer:
 *   - timer manager pauses every interval when the tab is hidden or the
 *     owning section is off-screen (battery/CPU friendly)
 *   - tweened odometer P&L, price-flash cells, correlated "market mood"
 *   - staggered reveal, cursor-reactive hero glow, focus-trapped palette
 * Degrades gracefully under prefers-reduced-motion.
 * ========================================================================== */
(function () {
	"use strict";

	const REDUCED = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
	const $ = (s, r) => (r || document).querySelector(s);
	const $$ = (s, r) => Array.prototype.slice.call((r || document).querySelectorAll(s));
	const pad2 = (n) => String(n).padStart(2, "0");
	const fmt = (v, d) => v.toLocaleString("en-US", { minimumFractionDigits: d || 0, maximumFractionDigits: d || 0 });
	const clamp = (v, a, b) => Math.max(a, Math.min(b, v));

	/* shared market state so panels move together (correlation) */
	const market = { mood: 0 };

	/* ---- timer manager: auto-pause on hidden tab / off-screen section ------- */
	const timers = [];
	function every(ms, fn, gateEl) {
		const t = { h: null, running: false, visible: true, allowed: !document.hidden };
		function sync() {
			const should = t.visible && t.allowed;
			if (should && !t.running) { t.h = setInterval(fn, ms); t.running = true; }
			else if (!should && t.running) { clearInterval(t.h); t.running = false; }
		}
		t.setVisible = function (v) { t.visible = v; sync(); };
		t.setAllowed = function (a) { t.allowed = a; sync(); };
		timers.push(t);
		if (gateEl && "IntersectionObserver" in window) {
			t.visible = false;
			new IntersectionObserver(function (es) { t.setVisible(es[0].isIntersecting); }, { threshold: 0 }).observe(gateEl);
		}
		sync();
		return t;
	}
	document.addEventListener("visibilitychange", function () {
		const on = !document.hidden;
		timers.forEach(function (t) { t.setAllowed(on); });
	});

	/* ---- motion helpers ----------------------------------------------------- */
	function tween(from, to, dur, onUpdate, onDone) {
		if (REDUCED) { onUpdate(to); if (onDone) onDone(); return; }
		const s = performance.now();
		(function f(now) {
			const k = Math.min(1, (now - s) / dur), e = 1 - Math.pow(1 - k, 3);
			onUpdate(from + (to - from) * e);
			if (k < 1) requestAnimationFrame(f); else if (onDone) onDone();
		})(s);
	}
	function flashCell(el, up) {
		if (REDUCED || !el) return;
		el.classList.remove("na-flash-up", "na-flash-down");
		void el.offsetWidth;
		el.classList.add(up ? "na-flash-up" : "na-flash-down");
		setTimeout(function () { el.classList.remove("na-flash-up", "na-flash-down"); }, 700);
	}

	function mulberry32(seed) {
		let a = seed >>> 0;
		return function () {
			a |= 0;
			a = (a + 0x6d2b79f5) | 0;
			let t = Math.imul(a ^ (a >>> 15), 1 | a);
			t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t;
			return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
		};
	}
	function series(n, start, drift, vol, seed) {
		const rng = mulberry32(seed);
		const out = [];
		let v = start;
		for (let i = 0; i < n; i++) {
			v += drift + (rng() - 0.5) * vol;
			if (v < start * 0.4) v = start * 0.4;
			out.push(+v.toFixed(2));
		}
		return out;
	}
	function geometry(values, W, H, padT, padB) {
		const n = values.length;
		const min = Math.min.apply(null, values);
		const max = Math.max.apply(null, values);
		const range = max - min || 1;
		const xStep = W / (n - 1);
		const arr = values.map(function (v, i) {
			const x = +(i * xStep).toFixed(2);
			const y = +(H - padB - ((v - min) / range) * (H - padT - padB)).toFixed(2);
			return { x: x, y: y, v: v };
		});
		const points = arr.map((p) => p.x + "," + p.y).join(" ");
		const area = "M " + arr.map((p) => p.x + " " + p.y).join(" L ") + " L " + W + " " + H + " L 0 " + H + " Z";
		return { points: points, area: area, arr: arr };
	}
	function drawLine(line, animate, dur) {
		if (!line || typeof line.getTotalLength !== "function") return;
		if (!animate || REDUCED) {
			line.style.strokeDasharray = "none";
			line.style.strokeDashoffset = "0";
			return;
		}
		const len = line.getTotalLength();
		line.style.transition = "none";
		line.style.strokeDasharray = len;
		line.style.strokeDashoffset = len;
		void line.getBoundingClientRect();
		line.style.transition = "stroke-dashoffset " + (dur || 1.3) + "s cubic-bezier(0.65,0,0.35,1)";
		line.style.strokeDashoffset = "0";
	}

	/* ===================================================================== *
	 * DECK CHART (hero centerpiece)
	 * ===================================================================== */
	const DECK_W = 560, DECK_H = 260;

	/* ---- real-data bootstrap (WordPress injects #na-landing-bootstrap) ------ *
	 * Backward compatible: the standalone prototype has no island, so BOOT is
	 * null and the chart falls back to the original simulated series. */
	function readBootstrap() {
		const el = document.getElementById("na-landing-bootstrap");
		if (!el) return null;
		try {
			const d = JSON.parse(el.textContent || "{}");
			if (d && Array.isArray(d.equity) && d.equity.length > 1) return d;
		} catch (e) {}
		return null;
	}
	const BOOT = readBootstrap();
	const EQUITY = BOOT ? BOOT.equity.map((p) => +p.v).filter((v) => isFinite(v)) : null;

	/* tail-slice + downsample a real equity series for a given timeframe */
	function tfSlice(values, tf) {
		const frac = { "1D": 0.012, "1W": 0.03, "1M": 0.08, "1Y": 0.28, "ALL": 1 }[tf] || 1;
		const n = Math.min(values.length, Math.max(8, Math.round(values.length * frac)));
		let part = values.slice(values.length - n);
		const MAX = 72;
		if (part.length > MAX) {
			const out = [], stp = (part.length - 1) / (MAX - 1);
			for (let i = 0; i < MAX; i++) out.push(part[Math.round(i * stp)]);
			part = out;
		}
		return part;
	}
	const TF = {
		"1D": { n: 16, dr: 0.5, vol: 1.3, seed: 21 },
		"1W": { n: 20, dr: 0.8, vol: 1.6, seed: 53 },
		"1M": { n: 24, dr: 1.0, vol: 1.4, seed: 88 },
		"1Y": { n: 14, dr: 1.4, vol: 1.1, seed: 134 },
		"ALL": { n: 18, dr: 1.9, vol: 1.5, seed: 167 }
	};
	const deck = { tf: "1Y", arr: [], base: 41230, displayed: 41230, scale: EQUITY ? 1 : 1000 };

	function deckRedraw(animate) {
		const line = $("#na-eq-line"), area = $("#na-eq-area");
		if (!line || !area) return;
		const t = TF[deck.tf];
		const values = EQUITY ? tfSlice(EQUITY, deck.tf) : series(t.n, 20, t.dr, t.vol, t.seed);
		const geo = geometry(values, DECK_W, DECK_H, 34, 36);
		deck.arr = geo.arr;
		area.setAttribute("d", geo.area);
		line.setAttribute("points", geo.points);
		const last = geo.arr[geo.arr.length - 1], first = geo.arr[0];
		const dot = $("#na-eq-dot");
		if (dot) { dot.setAttribute("cx", last.x); dot.setAttribute("cy", last.y); }
		const now = $("#na-eq-now");
		if (now) { now.setAttribute("x1", last.x); now.setAttribute("x2", last.x); }
		const change = ((last.v - first.v) / first.v) * 100;
		const badge = $("#na-eq-change");
		if (badge) {
			badge.textContent = (change >= 0 ? "+" : "") + fmt(change, 1) + "% \u00b7 " + deck.tf;
			badge.classList.toggle("is-pos", change >= 0);
			badge.classList.toggle("is-neg", change < 0);
		}
		deck.base = Math.round(last.v * deck.scale);
		deck.displayed = deck.base;
		const pnl = $("#na-deck-pnl");
		if (pnl) pnl.textContent = "$" + fmt(deck.base, 0);
		drawLine(line, animate, 1.3);
	}

	function deckCrosshair() {
		const wrap = $(".na-deck-chart-wrap");
		const svg = $(".na-deck-chart", wrap);
		const cross = $("#na-eq-cross"), cur = $("#na-eq-cursor"), tip = $("#na-eq-tip");
		if (!wrap || !svg || !cross || !cur || !tip) return;
		function move(e) {
			if (!deck.arr.length) return;
			const rect = svg.getBoundingClientRect();
			const cx = (e.touches ? e.touches[0].clientX : e.clientX) - rect.left;
			const svgX = (cx / rect.width) * DECK_W;
			const step = DECK_W / (deck.arr.length - 1);
			const p = deck.arr[clamp(Math.round(svgX / step), 0, deck.arr.length - 1)];
			cross.setAttribute("x1", p.x); cross.setAttribute("x2", p.x); cross.style.opacity = "1";
			cur.setAttribute("cx", p.x); cur.setAttribute("cy", p.y); cur.style.opacity = "1";
			tip.textContent = "$" + fmt(p.v * deck.scale, 0);
			tip.style.left = (p.x / DECK_W) * rect.width + "px";
			tip.style.top = (p.y / DECK_H) * rect.height + "px";
			tip.style.opacity = "1";
		}
		function leave() { cross.style.opacity = "0"; cur.style.opacity = "0"; tip.style.opacity = "0"; }
		wrap.addEventListener("mousemove", move);
		wrap.addEventListener("mouseleave", leave);
		wrap.addEventListener("touchmove", move, { passive: true });
		wrap.addEventListener("touchend", leave);
	}

	function initDeckChart() {
		const pills = $$(".na-tf");
		pills.forEach(function (pill) {
			if (pill.classList.contains("is-active")) deck.tf = pill.dataset.tf || deck.tf;
			pill.setAttribute("aria-pressed", pill.classList.contains("is-active") ? "true" : "false");
			pill.addEventListener("click", function () {
				const tf = pill.dataset.tf;
				if (!tf || tf === deck.tf) return;
				pills.forEach((p) => { p.classList.remove("is-active"); p.setAttribute("aria-pressed", "false"); });
				pill.classList.add("is-active");
				pill.setAttribute("aria-pressed", "true");
				deck.tf = tf;
				deckRedraw(true);
			});
		});
		deckRedraw(true);
		deckCrosshair();
	}

	/* ===================================================================== *
	 * STORY MORPH (sticky scrollytelling protagonist)
	 * ===================================================================== */
	const MORPH_W = 600, MORPH_H = 320;
	function initStory() {
		const stage = $("#na-morph");
		if (!stage) return;
		const master = series(26, 16, 1.35, 2.2, 909);
		const geo = geometry(master, MORPH_W, MORPH_H, 40, 44);
		const line = $("#na-morph-line"), area = $("#na-morph-area"),
			dd = $("#na-morph-dd"), dots = $("#na-morph-dots"),
			live = $("#na-morph-live"), val = $("#na-morph-val");
		if (line) line.setAttribute("points", geo.points);
		if (area) area.setAttribute("d", geo.area);

		if (dots) {
			const rng = mulberry32(404);
			dots.innerHTML = geo.arr.map(function (p) {
				const jx = (rng() - 0.5) * 10, jy = (rng() - 0.5) * 22;
				return '<circle cx="' + (p.x + jx).toFixed(1) + '" cy="' + clamp(p.y + jy, 30, MORPH_H - 30).toFixed(1) + '" r="2.6"></circle>';
			}).join("");
		}
		if (dd) {
			const ddPath = "M " + geo.arr.map(function (p, i) {
				const depth = 24 + Math.abs(Math.sin(i * 0.7)) * 30;
				return p.x + " " + clamp(MORPH_H - 30 - (i % 3 === 0 ? depth : depth * 0.5), 120, MORPH_H - 12);
			}).join(" L ") + " L " + MORPH_W + " " + MORPH_H + " L 0 " + MORPH_H + " Z";
			dd.setAttribute("d", ddPath);
		}
		if (live) { live.setAttribute("cx", geo.arr[geo.arr.length - 1].x); live.setAttribute("cy", geo.arr[geo.arr.length - 1].y); }
		if (val) val.textContent = "312";
		const LABELS = ["RAW DATA", "MODEL FIT", "BACKTEST", "LIVE", "COMPOUND"];
		const labelEl = $("#na-morph-stagelabel");

		function setStage(i) {
			if (stage.dataset.stage === String(i)) return;
			stage.dataset.stage = String(i);
			if (labelEl && LABELS[i]) labelEl.textContent = LABELS[i];
			if (i >= 1) drawLine(line, true, 1.1);
		}
		setStage(0);

		const steps = $$(".na-story-step");
		if ("IntersectionObserver" in window && steps.length) {
			const obs = new IntersectionObserver(function (entries) {
				entries.forEach(function (en) {
					if (en.isIntersecting) {
						steps.forEach((s) => s.classList.remove("is-active"));
						en.target.classList.add("is-active");
						setStage(parseInt(en.target.dataset.step || "0", 10));
					}
				});
			}, { rootMargin: "-45% 0px -45% 0px", threshold: 0 });
			steps.forEach((s) => obs.observe(s));
		} else {
			steps.forEach((s) => s.classList.add("is-active"));
			setStage(4);
		}
	}

	/* ===================================================================== *
	 * COMMAND PALETTE (Cmd/Ctrl + K) -- primary CTA, focus-trapped
	 * ===================================================================== */
	function initCmdk() {
		const modal = $("#na-cmdk");
		if (!modal) return;
		const input = $("#na-cmdk-input"), list = $("#na-cmdk-list");
		const items = $$(".na-cmdk-item", list);
		let active = 0, lastFocus = null;

		function open() {
			lastFocus = document.activeElement;
			modal.classList.add("is-open");
			modal.setAttribute("aria-hidden", "false");
			if (input) { input.value = ""; filter(""); setTimeout(() => input.focus(), 30); }
		}
		function close() {
			modal.classList.remove("is-open");
			modal.setAttribute("aria-hidden", "true");
			if (lastFocus && typeof lastFocus.focus === "function") lastFocus.focus();
		}
		function visible() { return items.filter((it) => it.style.display !== "none"); }
		function paint() {
			const vis = visible();
			items.forEach((it) => it.classList.remove("is-active"));
			if (vis[active]) vis[active].classList.add("is-active");
		}
		function filter(q) {
			q = (q || "").toLowerCase();
			items.forEach(function (it) {
				it.style.display = it.textContent.toLowerCase().indexOf(q) > -1 ? "" : "none";
			});
			active = 0; paint();
		}
		function run(it) {
			if (!it) return;
			const target = it.dataset.target;
			close();
			if (target) {
				const el = $(target);
				if (el) el.scrollIntoView({ behavior: REDUCED ? "auto" : "smooth", block: "start" });
			}
		}
		$$("[data-cmdk-open]").forEach((b) => b.addEventListener("click", function (e) { e.preventDefault(); open(); }));
		$$("[data-cmdk-close]", modal).forEach((b) => b.addEventListener("click", close));
		items.forEach(function (it) {
			it.addEventListener("click", () => run(it));
			it.addEventListener("mousemove", function () {
				const vis = visible(); const idx = vis.indexOf(it);
				if (idx > -1) { active = idx; paint(); }
			});
		});
		if (input) input.addEventListener("input", () => filter(input.value));
		document.addEventListener("keydown", function (e) {
			const k = e.key.toLowerCase();
			if ((e.metaKey || e.ctrlKey) && k === "k") { e.preventDefault(); modal.classList.contains("is-open") ? close() : open(); return; }
			if (!modal.classList.contains("is-open")) return;
			if (k === "escape") { close(); }
			else if (k === "arrowdown") { e.preventDefault(); active = Math.min(active + 1, visible().length - 1); paint(); }
			else if (k === "arrowup") { e.preventDefault(); active = Math.max(active - 1, 0); paint(); }
			else if (k === "enter") { e.preventDefault(); run(visible()[active]); }
			else if (k === "tab") { e.preventDefault(); if (input) input.focus(); }
		});
		modal.addEventListener("click", function (e) { if (e.target === modal) close(); });
	}

	/* ===================================================================== *
	 * STREAMING SIGNAL FEED (market-correlated)
	 * ===================================================================== */
	const SYMS = ["BTC/USD", "ETH/USD", "EUR/USD", "SPX500", "AAPL", "GBP/JPY", "XAU/USD", "NDX100", "TSLA", "USD/JPY"];
	const STRATS = ["Momentum Alpha", "Mean-Reversion FX", "Trend Rider", "Vol Breakout"];
	function initSignalFeed() {
		const feed = $("#na-signal-feed");
		if (!feed) return;
		const MAX = 6;
		function row() {
			const side = Math.random() > (0.42 - market.mood * 0.14) ? "BUY" : "SELL";
			const d = new Date();
			const li = document.createElement("li");
			li.className = "na-sig-row";
			li.innerHTML =
				'<span class="na-sig-t na-tab">' + pad2(d.getUTCHours()) + ":" + pad2(d.getUTCMinutes()) + ":" + pad2(d.getUTCSeconds()) + "</span>" +
				'<span class="na-sig-side ' + (side === "BUY" ? "is-buy" : "is-sell") + '">' + side + "</span>" +
				'<span class="na-sig-sym na-tab">' + SYMS[Math.floor(Math.random() * SYMS.length)] + "</span>" +
				'<span class="na-sig-st">' + STRATS[Math.floor(Math.random() * STRATS.length)] + "</span>" +
				'<span class="na-sig-p na-tab">+' + (Math.random() * 3.2 + 0.1).toFixed(2) + "%</span>";
			return li;
		}
		function push() {
			const r = row();
			feed.insertBefore(r, feed.firstChild);
			if (!REDUCED) { r.classList.add("is-new"); setTimeout(() => r.classList.remove("is-new"), 650); }
			while (feed.children.length > MAX) feed.removeChild(feed.lastChild);
		}
		for (let i = 0; i < MAX; i++) push();
		if (!REDUCED) every(3000, push, feed);
	}

	/* ===================================================================== *
	 * LIVE TRADE LOG (transparency, streaming)
	 * ===================================================================== */
	function initTradeFeed() {
		const body = $("#na-trade-feed");
		if (!body) return;
		const MAX = 8;
		function row() {
			const side = Math.random() > 0.4 ? "BUY" : "SELL";
			const ret = (Math.random() * 4 - 1.1);
			const d = new Date();
			const tr = document.createElement("tr");
			tr.innerHTML =
				"<td class='na-tab'>" + pad2(d.getUTCHours()) + ":" + pad2(d.getUTCMinutes()) + ":" + pad2(d.getUTCSeconds()) + "</td>" +
				"<td class='na-tab'>" + SYMS[Math.floor(Math.random() * SYMS.length)] + "</td>" +
				"<td><span class='na-sig-side " + (side === "BUY" ? "is-buy" : "is-sell") + "'>" + side + "</span></td>" +
				"<td class='na-ta-r na-tab " + (ret >= 0 ? "na-pos" : "na-neg") + "'>" + (ret >= 0 ? "+" : "") + ret.toFixed(2) + "%</td>";
			return tr;
		}
		function push() {
			const r = row();
			body.insertBefore(r, body.firstChild);
			if (!REDUCED) { r.classList.add("is-new"); setTimeout(() => r.classList.remove("is-new"), 650); }
			while (body.children.length > MAX) body.removeChild(body.lastChild);
		}
		for (let i = 0; i < MAX; i++) push();
		if (!REDUCED) every(2600, push, body);
	}

	/* ===================================================================== *
	 * MARKET HEATMAP (sparklines + price-flash + correlated drift)
	 * ===================================================================== */
	function initHeatmap() {
		const grid = $("#na-heatmap");
		if (!grid) return;
		const tiles = ["BTC", "ETH", "SOL", "SPX", "NDX", "AAPL", "TSLA", "NVDA", "EUR", "GBP", "JPY", "XAU"];
		const state = tiles.map(() => (Math.random() * 5 - 2));
		const spark = tiles.map((_, i) => series(14, 10, 0.15, 2.4, 300 + i));
		function cls(v) { return v >= 1.2 ? "h2" : v >= 0 ? "h1" : v >= -1.2 ? "l1" : "l2"; }
		function sparkPts(arr) {
			const min = Math.min.apply(null, arr), max = Math.max.apply(null, arr), rg = max - min || 1;
			const st = 100 / (arr.length - 1);
			return arr.map((v, i) => (i * st).toFixed(1) + "," + (21 - ((v - min) / rg) * 18).toFixed(1)).join(" ");
		}
		const nodes = tiles.map(function (s) {
			const d = document.createElement("div");
			d.innerHTML = '<div class="na-heat-top"><span class="na-heat-s">' + s +
				'</span><span class="na-heat-v na-tab"></span></div>' +
				'<svg class="na-heat-spark" viewBox="0 0 100 24" preserveAspectRatio="none"><polyline points=""></polyline></svg>';
			grid.appendChild(d);
			return d;
		});
		function render(i) {
			const v = state[i], n = nodes[i];
			n.className = "na-heat na-heat-" + cls(v);
			n.querySelector(".na-heat-v").textContent = (v >= 0 ? "+" : "") + v.toFixed(2) + "%";
			n.querySelector("polyline").setAttribute("points", sparkPts(spark[i]));
		}
		tiles.forEach((_, i) => render(i));
		if (!REDUCED) every(1400, function () {
			const i = Math.floor(Math.random() * state.length);
			const prev = state[i];
			state[i] = clamp(state[i] + (Math.random() - 0.5) * 1.4 + market.mood * 0.5, -3.5, 3.5);
			spark[i].push(spark[i][spark[i].length - 1] + (state[i] - prev));
			spark[i].shift();
			render(i);
			flashCell(nodes[i], state[i] >= prev);
		}, grid);
	}

	/* ===================================================================== *
	 * TICKER  |  CLOCK  |  LIVE P&L  |  COUNT-UP  |  SCROLL PROGRESS
	 * ===================================================================== */
	const TICK = [["BTC/USD", "68,412", 1.24], ["ETH/USD", "3,842", 0.86], ["SPX500", "5,431", 0.42], ["NDX100", "19,210", 0.71], ["EUR/USD", "1.0842", -0.18], ["XAU/USD", "2,388", 0.33], ["AAPL", "214.30", -0.52], ["TSLA", "248.10", 2.11], ["GBP/JPY", "198.42", 0.27]];
	function initTicker() {
		const track = $("#na-ticker-track");
		if (!track) return;
		const html = TICK.map(function (t) {
			const up = t[2] >= 0;
			return '<span class="na-tick-item"><span class="na-tick-sym">' + t[0] + '</span> <span class="na-tab">' + t[1] +
				'</span> <span class="' + (up ? "na-up" : "na-down") + ' na-tab">' + (up ? "\u25B2" : "\u25BC") + " " + Math.abs(t[2]).toFixed(2) + "%</span></span>";
		}).join("");
		track.innerHTML = html + html;
	}
	function initClock() {
		const el = $("#na-clock");
		if (!el) return;
		function tick() {
			const d = new Date();
			el.textContent = pad2(d.getUTCHours()) + ":" + pad2(d.getUTCMinutes()) + ":" + pad2(d.getUTCSeconds()) + " UTC";
		}
		tick();
		if (!REDUCED) every(1000, tick);
	}
	function initLivePnl() {
		const el = $("#na-deck-pnl");
		if (!el || REDUCED) return;
		every(2200, function () {
			const target = deck.base + (Math.random() - 0.45) * 120;
			const up = target >= deck.displayed;
			el.classList.remove("is-up", "is-down");
			void el.offsetWidth;
			el.classList.add(up ? "is-up" : "is-down");
			const from = deck.displayed;
			tween(from, target, 600, function (v) { el.textContent = "$" + fmt(v, 0); });
			deck.displayed = target;
			market.mood = clamp(market.mood * 0.7 + (up ? 0.3 : -0.3), -1, 1);
		}, $(".na-deck-main"));
	}
	function countUp(el) {
		const to = parseFloat(el.dataset.to);
		if (isNaN(to)) return;
		const dec = parseInt(el.dataset.decimals || "0", 10);
		const pre = el.dataset.prefix || "", suf = el.dataset.suffix || "";
		if (REDUCED) { el.textContent = pre + fmt(to, dec) + suf; return; }
		const start = performance.now(), dur = 1300;
		(function frame(now) {
			const t = Math.min(1, (now - start) / dur), e = 1 - Math.pow(1 - t, 3);
			el.textContent = pre + fmt(to * e, dec) + suf;
			if (t < 1) requestAnimationFrame(frame);
		})(start);
	}
	function initCountUp() {
		const els = $$("[data-countup]");
		if (!els.length) return;
		if (REDUCED || !("IntersectionObserver" in window)) { els.forEach(countUp); return; }
		const obs = new IntersectionObserver(function (ents) {
			ents.forEach(function (en) { if (en.isIntersecting) { countUp(en.target); obs.unobserve(en.target); } });
		}, { threshold: 0.4 });
		els.forEach((el) => obs.observe(el));
	}
	function initScrollProgress() {
		const bar = $("#na-scroll-progress");
		if (!bar) return;
		let ticking = false;
		function update() {
			const h = document.documentElement, max = h.scrollHeight - h.clientHeight;
			bar.style.width = (max > 0 ? (h.scrollTop / max) * 100 : 0) + "%";
			ticking = false;
		}
		window.addEventListener("scroll", function () { if (!ticking) { requestAnimationFrame(update); ticking = true; } }, { passive: true });
		update();
	}

	/* ---- reveal on scroll (staggered, cohesive entrance) -------------------- */
	function initReveal() {
		const els = $$("[data-reveal]");
		if (!els.length) return;
		if (REDUCED || !("IntersectionObserver" in window)) { els.forEach((e) => e.classList.add("is-in")); return; }
		els.forEach(function (el) {
			const sibs = $$("[data-reveal]", el.parentNode).filter((s) => s.parentNode === el.parentNode);
			el.__d = Math.min(sibs.indexOf(el), 5) * 80;
		});
		const obs = new IntersectionObserver(function (ents) {
			ents.forEach(function (en) {
				if (en.isIntersecting) {
					en.target.style.transitionDelay = (en.target.__d || 0) + "ms";
					en.target.classList.add("is-in");
					obs.unobserve(en.target);
				}
			});
		}, { threshold: 0.15 });
		els.forEach((e) => obs.observe(e));
	}

	/* ---- cursor-reactive hero glow (signature) ----------------------------- */
	function initDeskGlow() {
		const desk = $(".na-desk");
		if (!desk || REDUCED) return;
		let raf = null, mx = 50, my = 16;
		desk.addEventListener("mousemove", function (e) {
			const r = desk.getBoundingClientRect();
			mx = ((e.clientX - r.left) / r.width) * 100;
			my = ((e.clientY - r.top) / r.height) * 100;
			if (!raf) raf = requestAnimationFrame(function () {
				desk.style.setProperty("--na-mx", mx.toFixed(1) + "%");
				desk.style.setProperty("--na-my", my.toFixed(1) + "%");
				raf = null;
			});
		});
	}

	function boot() {
		initDeckChart();
		initStory();
		initCmdk();
		initSignalFeed();
		initTradeFeed();
		initHeatmap();
		initTicker();
		initClock();
		initLivePnl();
		initCountUp();
		initScrollProgress();
		initReveal();
		initDeskGlow();
	}
	if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", boot);
	else boot();
})();
