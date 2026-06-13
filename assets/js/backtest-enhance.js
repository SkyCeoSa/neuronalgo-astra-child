/**
 * NeuronAlgo — Single Backtest enhancements (FE-3.6 + FE-3.7), additive & non-destructive.
 * 1) Splits the shared equity_curve card into two .win cards (Equity + Drawdown).
 * 2) Ports the terminal-desk hero (badges, animated KPI strip, equity sparkline).
 * 3) Uniform motion system: every .win card scroll-reveals (fade-up) and, on first
 *    reveal, its numbers count-up and its gauges/donuts/bars fill from zero — so
 *    at-a-glance, trade_breakdown, trade_distribution and full_statistics all feel
 *    as alive as the hero. Honors prefers-reduced-motion. Every step is wrapped in
 *    try/catch and restores exact original text/markup at rest, so any failure
 *    leaves server-rendered content intact.
 */
(function(){
  'use strict';

  function ready(fn){if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',fn);}else{fn();}}
  function each(list,fn){if(list){Array.prototype.forEach.call(list,fn);}}
  function num(n){return (typeof n==='number'&&isFinite(n))?n:null;}
  function fmtNum(v,dec){return v.toLocaleString('en-US',{minimumFractionDigits:dec,maximumFractionDigits:dec});}

  /* ---------- 1) split equity / drawdown into two cards ---------- */
  function splitCharts(){
    try{
      var dd=document.querySelector('[id^="na-chart-"][id$="-drawdown"]');
      if(!dd){return;}
      var ddBox=dd.closest('.chart-box');
      if(!ddBox){return;}
      var equityCard=ddBox.closest('.win');
      if(!equityCard||equityCard.getAttribute('data-na-split')){return;}
      var note=ddBox.previousElementSibling;
      var cmd=note?note.previousElementSibling:null;
      var cap=ddBox.nextElementSibling;
      var data=cap?cap.nextElementSibling:null;
      var card=document.createElement('section');
      card.className='win';
      card.innerHTML='<div class="winbar"><span class="dots"><i></i><i></i><i></i></span>'+
        '<span class="fname"><b>drawdown</b> --render</span><span class="wstat">ok</span></div>'+
        '<div class="winbody"></div>';
      var nb=card.querySelector('.winbody');
      [cmd,note,ddBox,cap,data].forEach(function(n){if(n){nb.appendChild(n);}});
      if(cmd){cmd.removeAttribute('style');}
      equityCard.parentNode.insertBefore(card,equityCard.nextSibling);
      equityCard.setAttribute('data-na-split','1');
    }catch(e){}
  }

  /* ---------- 2) hero ---------- */
  function countUp(el){
    var to=parseFloat(el.getAttribute('data-to'));
    if(isNaN(to)){return;}
    var dec=parseInt(el.getAttribute('data-dec')||'0',10);
    var pre=el.getAttribute('data-pre')||'';
    var suf=el.getAttribute('data-suf')||'';
    var t0=null,dur=950;
    function tick(ts){
      if(!t0){t0=ts;}
      var p=Math.min(1,(ts-t0)/dur);
      var e=1-Math.pow(1-p,3);
      el.textContent=pre+fmtNum(to*e,dec)+suf;
      if(p<1){requestAnimationFrame(tick);}else{el.textContent=pre+fmtNum(to,dec)+suf;}
    }
    requestAnimationFrame(tick);
  }

  function kpiTile(label,to,opt){
    opt=opt||{};
    var pre=opt.pre||'',suf=opt.suf||'',dec=opt.dec||0;
    var k=document.createElement('div');
    k.className='na-kpi';
    if(to==null){
      k.innerHTML='<span class="na-kpi-l">'+label+'</span><span class="na-kpi-v">\u2014</span>'+
        '<span class="na-kpi-s">'+(opt.sub||'')+'</span>';
    }else{
      k.innerHTML='<span class="na-kpi-l">'+label+'</span>'+
        '<span class="na-kpi-v" data-to="'+to+'" data-dec="'+dec+'" data-pre="'+pre+'" data-suf="'+suf+'">'+pre+'0'+suf+'</span>'+
        '<span class="na-kpi-s">'+(opt.sub||'')+'</span>';
      k.__v=k.querySelector('.na-kpi-v');
    }
    return k;
  }

  function readEquitySeries(){
    try{
      var s=document.querySelector('[id$="-equity-data"]');
      if(!s){return null;}
      var p=JSON.parse(s.textContent||s.innerText);
      return (p&&p.series&&p.series.length)?p.series:null;
    }catch(e){return null;}
  }

  function sparkSvg(series){
    if(!series||series.length<2){return null;}
    var n=series.length,min=Infinity,max=-Infinity,i,v;
    for(i=0;i<n;i++){v=series[i].v;if(typeof v==='number'){if(v<min){min=v;}if(v>max){max=v;}}}
    if(!(max>min)){return null;}
    var W=1000,Hh=200,stride=Math.max(1,Math.floor(n/220)),d='';
    for(i=0;i<n;i+=stride){
      var x=(i/(n-1))*W;
      var y=Hh-((series[i].v-min)/(max-min))*Hh;
      d+=(d?'L':'M')+x.toFixed(1)+' '+y.toFixed(1)+' ';
    }
    return '<svg class="na-hero-spark" viewBox="0 0 '+W+' '+Hh+'" preserveAspectRatio="none" aria-hidden="true">'+
      '<defs><linearGradient id="naHeroSparkG" x1="0" y1="0" x2="0" y2="1">'+
      '<stop offset="0%" stop-color="#3d7dff" stop-opacity="0.55"/>'+
      '<stop offset="100%" stop-color="#3d7dff" stop-opacity="0"/></linearGradient></defs>'+
      '<path d="'+d+'L 1000 200 L 0 200 Z" fill="url(#naHeroSparkG)" stroke="none"/>'+
      '<path d="'+d+'" fill="none" stroke="#38bdf8" stroke-width="2" vector-effect="non-scaling-stroke"/></svg>';
  }

  function buildHero(){
    try{
      var hero=document.querySelector('.na-backtest-single .hero');
      if(!hero||hero.getAttribute('data-na-hero')){return;}
      var body=hero.querySelector('.winbody');
      if(!body){return;}
      var H=window.NA_HERO||{};
      var net=num(H.net),win=num(H.winRate),pf=num(H.pf),trades=num(H.trades),wins=num(H.wins),losses=num(H.losses);
      var longOnly=!!H.longOnly;
      if(window.NA_TD&&window.NA_TD.longShort){
        var ls=window.NA_TD.longShort;
        if(ls&&ls.short&&typeof ls.short.trades==='number'&&ls.long&&typeof ls.long.trades==='number'){
          longOnly=(ls.short.trades===0&&ls.long.trades>0);
        }
      }

      var badges=document.createElement('div');
      badges.className='na-hero-badges';
      function badge(txt,cls){var b=document.createElement('span');b.className='na-badge'+(cls?(' '+cls):'');b.textContent=txt;badges.appendChild(b);}
      badge('LIVE','live');
      if(H.instrument){badge(String(H.instrument).replace(/_/g,' \u00B7 '));}
      if(H.tf){badge(H.tf);}
      if(H.pStart&&H.pEnd){badge(H.pStart+' \u2192 '+H.pEnd);}
      if(longOnly){badge('Long-only','lo');}

      var strip=document.createElement('div');
      strip.className='na-kpi-strip';
      var tiles=[
        kpiTile('Net profit', net==null?null:Math.abs(net), {pre:(net!=null&&net<0)?'-$':'$',dec:0,sub:'total net'}),
        kpiTile('Win rate', win, {suf:'%',dec:2,sub:(wins!=null&&losses!=null)?(fmtNum(wins,0)+'W / '+fmtNum(losses,0)+'L'):'wins / losses'}),
        kpiTile('Profit factor', pf, {dec:2,sub:'gross profit / loss'}),
        kpiTile('Trades', trades, {dec:0,sub:longOnly?'long-only':'long + short'})
      ];
      tiles.forEach(function(t){strip.appendChild(t);});

      var sparkHtml=sparkSvg(readEquitySeries());

      /* ---- mutate now that everything is built ---- */
      var subline=body.querySelector('.subline');
      var statusline=body.querySelector('.statusline');
      if(subline){subline.parentNode.replaceChild(badges,subline);}
      else{var h1=body.querySelector('h1');if(h1){h1.parentNode.insertBefore(badges,h1.nextSibling);}else{body.appendChild(badges);}}
      if(statusline){statusline.parentNode.replaceChild(strip,statusline);}
      else{badges.parentNode.insertBefore(strip,badges.nextSibling);}

      if(sparkHtml){
        var wrap=document.createElement('div');
        wrap.innerHTML=sparkHtml;
        var svg=wrap.firstChild;
        if(svg){body.insertBefore(svg,body.firstChild);}
      }

      try{
        var cta=body.querySelector('.hero-cta');
        var allLink=body.querySelector('.crumb a');
        if(cta&&allLink&&!cta.querySelector('.na-cta2')){
          var a=document.createElement('a');
          a.className='btn na-cta2';
          a.href=allLink.getAttribute('href');
          a.textContent='all backtests';
          cta.appendChild(a);
        }
      }catch(e){}

      hero.classList.add('na-hero-on');
      hero.setAttribute('data-na-hero','1');
      tiles.forEach(function(t){if(t.__v){countUp(t.__v);}});
    }catch(e){}
  }

  /* ---------- 3) uniform motion: reveal + count-up + fills ---------- */
  var VALUE_SEL='.nval, .nrow .v, .gv, .dc b, .legend .row .v, .cmp .num, .mini .v, .metric .v';

  function countUpText(el){
    try{
      if(el.__naCounted||el.children.length!==0){return;}
      var orig=(el.textContent||'').trim();
      var m=orig.match(/^([+-]?\$?)(\d[\d,]*(?:\.\d+)?)(%|x|\u00D7)?$/);
      if(!m){return;}
      el.__naCounted=1;
      var pre=m[1],rawNum=m[2],suf=m[3]||'';
      var grouped=rawNum.indexOf(',')>=0;
      var dot=rawNum.indexOf('.');
      var dec=dot>=0?(rawNum.length-dot-1):0;
      var target=parseFloat(rawNum.replace(/,/g,''));
      if(!isFinite(target)){return;}
      function fmt(v){var s=grouped?v.toLocaleString('en-US',{minimumFractionDigits:dec,maximumFractionDigits:dec}):v.toFixed(dec);return pre+s+suf;}
      var t0=null,dur=900;
      function tick(ts){if(!t0){t0=ts;}var p=Math.min(1,(ts-t0)/dur);var e=1-Math.pow(1-p,3);el.textContent=fmt(target*e);if(p<1){requestAnimationFrame(tick);}else{el.textContent=orig;}}
      el.textContent=fmt(0);
      requestAnimationFrame(tick);
    }catch(e){}
  }

  function animateBar(el){
    try{
      var w=el.style.width;
      if(!w||w==='0%'||w==='0px'){return;}
      el.style.width='0%';
      requestAnimationFrame(function(){requestAnimationFrame(function(){el.style.width=w;});});
    }catch(e){}
  }

  function animateConic(el){
    try{
      var s=el.getAttribute('style')||'';
      var m=s.match(/([\d.]+)deg/);
      if(!m){return;}
      var target=parseFloat(m[1]);
      if(!(target>0)){return;}
      var idx=m.index,pre=s.slice(0,idx),post=s.slice(idx+m[0].length);
      var t0=null,dur=900;
      function tick(ts){if(!t0){t0=ts;}var p=Math.min(1,(ts-t0)/dur);var e=1-Math.pow(1-p,3);el.setAttribute('style',pre+(target*e).toFixed(1)+'deg'+post);if(p<1){requestAnimationFrame(tick);}else{el.setAttribute('style',s);}}
      el.setAttribute('style',pre+'0deg'+post);
      requestAnimationFrame(tick);
    }catch(e){}
  }

  function revealCard(card){
    try{
      if(card.__naRevealed){return;}
      card.__naRevealed=1;
      card.classList.add('na-reveal-in');
      each(card.querySelectorAll(VALUE_SEL),countUpText);
      each(card.querySelectorAll('.cmp .bar i'),animateBar);
      each(card.querySelectorAll('.gauge, .donut'),animateConic);
    }catch(e){}
  }

  function setupMotion(){
    try{
      var root=document.getElementById('na-backtest-single');
      if(!root){return;}
      var reduce=window.matchMedia&&window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      if(reduce){return;}
      if(!('IntersectionObserver' in window)){return;}
      root.classList.add('na-motion');
      var io=new IntersectionObserver(function(entries){
        each(entries,function(en){if(en.isIntersecting){revealCard(en.target);io.unobserve(en.target);}});
      },{threshold:0.12,rootMargin:'0px 0px -8% 0px'});
      function observe(card){if(card.__naMo){return;}card.__naMo=1;io.observe(card);}
      each(root.querySelectorAll('.win'),observe);
      var mo=new MutationObserver(function(muts){
        each(muts,function(mu){
          each(mu.addedNodes,function(node){
            if(!node||node.nodeType!==1){return;}
            if(node.classList&&node.classList.contains('win')){observe(node);}
            if(node.querySelectorAll){each(node.querySelectorAll('.win'),observe);}
          });
        });
      });
      mo.observe(root,{childList:true,subtree:true});
    }catch(e){}
  }

  ready(function(){
    splitCharts();
    buildHero();
    setupMotion();
  });
})();
