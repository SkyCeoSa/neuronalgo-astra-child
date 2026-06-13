/**
 * NeuronAlgo — Backtest Trade Distribution (FE-3.3b)
 * Smart Long/Short direction card + hourly / weekday / duration distribution
 * charts for the single-backtest template. Data is injected server-side via
 * window.NA_TD (see inc/enqueue/class-conditional-assets.php). Long-only and
 * short-only strategies render only the active side(s); empty sides are hidden.
 * Depends on the global ApexCharts (na-apexcharts).
 */
(function () {
    'use strict';

    var DATA = window.NA_TD;
    if (!DATA || typeof DATA !== 'object') { return; }

    var T = {
        accent: '#3d7dff', cyan: '#38bdf8', violet: '#a855f7', green: '#22c55e',
        red: '#ef4444', amber: '#f5b94a', muted: '#637094', grid: '#1a2638',
        heading: '#f5f7fb', border: '#233048'
    };
    var MONO = "'JetBrains Mono',ui-monospace,Menlo,Consolas,monospace";

    function num(v) { return (typeof v === 'number' && isFinite(v)) ? v : 0; }
    function money(v) { v = num(v); return (v < 0 ? '-$' : '$') + Math.abs(v).toLocaleString('en-US', { maximumFractionDigits: 0 }); }
    function esc(s) { return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }

    function tip(b, label) {
        var tr = num(b.trades), wr = tr > 0 ? (num(b.wins) / tr) * 100 : 0, pnl = num(b.net_pnl);
        return '<div class="na-tt"><div class="na-tt-h">' + esc(label) + '</div>'
            + '<div class="na-tt-r"><span>trades</span><b>' + tr + '</b></div>'
            + '<div class="na-tt-r"><span>win rate</span><b>' + wr.toFixed(1) + '%</b></div>'
            + '<div class="na-tt-r"><span>net p&amp;l</span><b class="' + (pnl >= 0 ? 'pp' : 'nn') + '">' + money(pnl) + '</b></div></div>';
    }

    function directionHtml() {
        var ls = DATA.longShort;
        if (!ls || typeof ls !== 'object') { return ''; }
        var L = ls.long || {}, S = ls.short || {};
        var lOn = num(L.trades) > 0, sOn = num(S.trades) > 0;
        if (!lOn && !sOn) { return ''; }

        var badge, donut, legend, note = '';
        if (lOn && sOn) {
            var lt = num(L.trades), st = num(S.trades), tot = lt + st;
            var share = tot > 0 ? lt / tot : 0;
            badge = 'LONG + SHORT';
            donut = '<div class="donut" style="background:conic-gradient(' + T.accent + ' ' + (share * 360).toFixed(1) + 'deg,' + T.violet + ' 0)"><div class="dc"><b>' + tot + '</b><span>trades</span></div></div>';
            legend = '<div class="legend"><div class="row"><span class="dot" style="background:' + T.accent + '"></span>Long<span class="v">' + lt + '</span></div>'
                + '<div class="row"><span class="dot" style="background:' + T.violet + '"></span>Short<span class="v">' + st + '</span></div></div>';
        } else {
            var d = lOn ? L : S;
            var w = num(d.wins), l = num(d.losses), wl = w + l, ws = wl > 0 ? w / wl : 0;
            badge = lOn ? 'LONG-ONLY' : 'SHORT-ONLY';
            donut = '<div class="donut" style="background:conic-gradient(' + T.green + ' ' + (ws * 360).toFixed(1) + 'deg,' + T.red + ' 0)"><div class="dc"><b>' + (ws * 100).toFixed(1) + '%</b><span>win</span></div></div>';
            legend = '<div class="legend"><div class="row"><span class="dot" style="background:' + T.green + '"></span>Wins<span class="v">' + w + '</span></div>'
                + '<div class="row"><span class="dot" style="background:' + T.red + '"></span>Losses<span class="v">' + l + '</span></div>'
                + '<div class="row"><span class="dot" style="background:' + T.border + '"></span>Total<span class="v">' + num(d.trades) + '</span></div></div>';
            note = '<div class="na-td-note">// ' + (lOn ? 'short' : 'long') + ' trades: 0 — single-direction system</div>';
        }
        return '<div class="tlet na-td-tile"><div class="glet-bar"><span>direction_split</span><span class="s"></span></div>'
            + '<div class="tlet-in"><span class="na-td-badge">' + badge + '</span><div class="donut-wrap">' + donut + legend + '</div>' + note + '</div></div>';
    }

    function perfHtml() {
        var ls = DATA.longShort;
        if (!ls || typeof ls !== 'object') { return ''; }
        var L = ls.long || {}, S = ls.short || {};
        var sides = [];
        if (num(L.trades) > 0) { sides.push(['LONG', L]); }
        if (num(S.trades) > 0) { sides.push(['SHORT', S]); }
        if (!sides.length) { return ''; }
        var maxPnl = 1;
        sides.forEach(function (s) { maxPnl = Math.max(maxPnl, Math.abs(num(s[1].net_pnl))); });
        var rows = sides.map(function (s) {
            var key = s[0], d = s[1];
            var wr = num(d.win_rate) * 100;
            var pnl = num(d.net_pnl);
            var pw = Math.round(Math.abs(pnl) / maxPnl * 100);
            return '<div class="na-td-perf-grp"><div class="na-td-perf-h">' + key + ' · ' + num(d.trades) + ' trades</div>'
                + '<div class="line"><div class="top"><span>Win rate</span><span class="num">' + wr.toFixed(2) + '%</span></div><div class="bar"><i style="width:' + Math.min(100, wr).toFixed(1) + '%;background:' + T.cyan + '"></i></div></div>'
                + '<div class="line"><div class="top"><span>Net P&amp;L</span><span class="num ' + (pnl >= 0 ? 'pos' : 'neg') + '">' + money(pnl) + '</span></div><div class="bar"><i style="width:' + pw + '%;background:' + (pnl >= 0 ? T.green : T.red) + '"></i></div></div></div>';
        }).join('');
        return '<div class="tlet na-td-tile"><div class="glet-bar"><span>direction_performance</span><span class="s"></span></div><div class="tlet-in"><div class="cmp">' + rows + '</div></div></div>';
    }

    function chartWrap(id, arg, note) {
        return '<div class="na-td-chartwrap"><p class="cmd"><span class="p">$</span> plot <span class="arg">' + arg + '</span><span class="caret"></span></p><p class="note">' + note + '</p><div class="chart-box"><div class="na-td-chart" id="' + id + '"></div></div></div>';
    }

    function hasBuckets(o) { return o && o.buckets && o.buckets.length; }

    function init() {
        var root = document.getElementById('na-backtest-single');
        if (!root) { return; }

        var dirGrid = directionHtml() + perfHtml();
        var charts = '';
        if (hasBuckets(DATA.hourly)) { charts += chartWrap('na-td-hourly', '--by-hour', 'trade count by entry hour (UTC) · hover for win-rate &amp; P&amp;L'); }
        if (hasBuckets(DATA.weekday)) { charts += chartWrap('na-td-weekday', '--by-weekday', 'trade count by weekday · hover for win-rate &amp; P&amp;L'); }
        if (hasBuckets(DATA.duration)) { charts += chartWrap('na-td-duration', '--by-holding-time', 'trade count by holding time · hover for win-rate &amp; P&amp;L'); }

        if (!dirGrid && !charts) { return; }

        var html = '<div class="winbar"><span class="dots"><i></i><i></i><i></i></span><span class="fname"><b>trade_distribution</b> --analyze</span><span class="wstat">ok</span></div>'
            + '<div class="winbody"><p class="cmd"><span class="p">$</span> trades <span class="arg">--distribution</span><span class="caret"></span></p>'
            + '<p class="note">direction split + time-of-day, weekday &amp; holding-time distributions</p>'
            + (dirGrid ? '<div class="tb-grid na-td-grid">' + dirGrid + '</div>' : '')
            + charts + '</div>';

        var sec = document.createElement('section');
        sec.className = 'win na-td-section';
        sec.innerHTML = html;

        var anchor = null, wins = root.querySelectorAll('.win');
        for (var i = 0; i < wins.length; i++) {
            var b = wins[i].querySelector('.fname b');
            if (b && b.textContent.trim() === 'trade_breakdown') { anchor = wins[i]; break; }
        }
        if (anchor && anchor.parentNode) { anchor.parentNode.insertBefore(sec, anchor.nextSibling); }
        else { root.appendChild(sec); }

        var flag = root.querySelector('.flag');
        if (flag && flag.parentNode) { flag.parentNode.removeChild(flag); }

        render();
    }

    function render() {
        if (typeof ApexCharts === 'undefined') { return; }
        var baseChart = { fontFamily: MONO, foreColor: T.muted, toolbar: { show: false }, animations: { enabled: true, speed: 600 } };
        var gridOpts = { borderColor: T.grid, strokeDashArray: 4 };

        if (hasBuckets(DATA.h