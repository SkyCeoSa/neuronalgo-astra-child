/**
 * NeuronAlgo — Window Controls + Chart Zoom Reset (global, additive)
 * - .win cards get macOS-style minimize (yellow) + expand (green).
 *   Red is intentionally inert (no close on content pages).
 * - Equity/drawdown ApexCharts get a "reset scale" button. The charts are
 *   drag-zoomable but ship with the toolbar hidden, so there is otherwise no
 *   way back from a zoom. Reads the live instance via element.apexcharts and
 *   does NOT touch backtest-charts.js.
 */
(function(){
  'use strict';

  var SCOPES=['na-backtest-single','na-single-strategy','na-backtest-archive','na-strategy-library'];

  function ready(fn){
    if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',fn);}else{fn();}
  }

  var ov=null,scopeWrap=null,maxed=null,ph=null;

  function getOverlay(){
    if(!ov){
      ov=document.createElement('div');
      ov.className='na-winov';
      scopeWrap=document.createElement('div');
      ov.appendChild(scopeWrap);
      document.body.appendChild(ov);
      ov.addEventListener('click',function(e){if(e.target===ov){restore();}});
    }
    return ov;
  }

  function scopeClassOf(win){
    for(var i=0;i<SCOPES.length;i++){if(win.closest('.'+SCOPES[i])){return SCOPES[i];}}
    var main=win.closest('main');
    return (main&&main.className)?main.className:'';
  }

  function restore(){
    if(!maxed){return;}
    maxed.classList.remove('na-max');
    if(ph&&ph.parentNode){ph.parentNode.insertBefore(maxed,ph);ph.parentNode.removeChild(ph);}
    if(ov){ov.classList.remove('na-show');}
    document.body.classList.remove('na-win-locked');
    maxed=null;ph=null;
    setTimeout(function(){window.dispatchEvent(new Event('resize'));},60);
  }

  function maximize(win){
    if(maxed===win){restore();return;}
    if(maxed){restore();}
    getOverlay();
    scopeWrap.className='na-winscope '+scopeClassOf(win);
    ph=document.createElement('div');ph.className='na-winph';
    win.parentNode.insertBefore(ph,win);
    scopeWrap.appendChild(win);
    win.classList.add('na-max');
    ov.classList.add('na-show');
    document.body.classList.add('na-win-locked');
    maxed=win;
    setTimeout(function(){window.dispatchEvent(new Event('resize'));},80);
  }

  function setupDot(dot,label,onAct){
    dot.classList.add('na-ctl');
    dot.setAttribute('role','button');
    dot.setAttribute('tabindex','0');
    dot.setAttribute('title',label);
    dot.setAttribute('aria-label',label);
    dot.addEventListener('click',function(e){e.stopPropagation();onAct();});
    dot.addEventListener('keydown',function(e){if(e.key==='Enter'||e.key===' '){e.preventDefault();e.stopPropagation();onAct();}});
  }

  function wireControls(win){
    var dots=win.querySelectorAll('.winbar .dots i');
    if(dots.length<3){return;}
    setupDot(dots[1],'Minimize',function(){win.classList.toggle('na-min');});
    setupDot(dots[2],'Expand',function(){maximize(win);});
  }

  function resetZoom(container){
    var inst=container.apexcharts;
    if(!inst){return;}
    try{
      var g=inst.w&&inst.w.globals;
      if(g&&g.initialMinX!=null&&g.initialMaxX!=null&&isFinite(g.initialMinX)&&isFinite(g.initialMaxX)){
        inst.zoomX(g.initialMinX,g.initialMaxX);
      }else if(typeof inst.resetSeries==='function'){
        inst.resetSeries(true,true);
      }
    }catch(err){}
  }

  function addResetBtn(container){
    if(container.querySelector('.na-zoom-reset')){return;}
    var btn=document.createElement('button');
    btn.type='button';
    btn.className='na-zoom-reset';
    btn.setAttribute('title','Reset zoom');
    btn.setAttribute('aria-label','Reset chart zoom');
    btn.innerHTML='<span class="na-zr-ic">\u27F2</span> reset';
    btn.addEventListener('click',function(e){e.stopPropagation();resetZoom(container);});
    container.appendChild(btn);
  }

  function observeChart(container){
    function tryAdd(){if(container.classList.contains('na-chart-state-rendered')){addResetBtn(container);}}
    tryAdd();
    try{
      var mo=new MutationObserver(tryAdd);
      mo.observe(container,{attributes:true,attributeFilter:['class']});
    }catch(e){}
  }

  ready(function(){
    var wins=document.querySelectorAll('.win'),i;
    for(i=0;i<wins.length;i++){wireControls(wins[i]);}
    var charts=document.querySelectorAll('[id^="na-chart-"][id$="-equity"],[id^="na-chart-"][id$="-drawdown"]');
    for(i=0;i<charts.length;i++){observeChart(charts[i]);}
    document.addEventListener('keydown',function(e){if(e.key==='Escape'){restore();}});
  });
})();
