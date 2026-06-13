/**
 * NeuronAlgo — Window Controls + Chart Zoom Reset (global, additive)
 * - .win cards get macOS-style minimize (yellow) + expand (green).
 *   Red is intentionally inert (no close on content pages).
 * - Equity/drawdown ApexCharts get a "reset scale" button. The chart loader
 *   (backtest-charts.js, untouchable) builds each chart WITHOUT a chart.id and
 *   never stores the instance, so the instance is otherwise unreachable. We
 *   wrap ApexCharts.prototype.render once at load time so every chart records
 *   itself on its mount element (el.__naChart). This script evaluates in the
 *   footer before DOMContentLoaded, i.e. before the loader renders, so the
 *   patch is always in place. Reset then zoomX-es back to the full data range.
 */
(function(){
  'use strict';

  var SCOPES=['na-backtest-single','na-single-strategy','na-backtest-archive','na-strategy-library'];

  function ready(fn){
    if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',fn);}else{fn();}
  }

  // --- capture every ApexCharts instance on its mount element ---
  function patchApex(){
    if(typeof window.ApexCharts==='undefined'){return false;}
    if(ApexCharts.__naPatched){return true;}
    try{
      ApexCharts.__naPatched=true;
      var proto=ApexCharts.prototype;
      var _render=proto.render;
      proto.render=function(){
        try{if(this.el){this.el.__naChart=this;}}catch(e){}
        return _render.apply(this,arguments);
      };
    }catch(e){}
    return true;
  }
  if(!patchApex()){
    var _tries=0;
    var _t=setInterval(function(){_tries++;if(patchApex()||_tries>100){clearInterval(_t);}},15);
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

  // --- resolve the live ApexCharts instance for a chart container ---
  function findInstance(container){
    if(container.__naChart){return container.__naChart;}
    if(container.apexcharts){return container.apexcharts;}
    try{
      var canvas=container.querySelector('.apexcharts-canvas');
      if(canvas&&canvas.id&&window.ApexCharts&&typeof ApexCharts.getChartByID==='function'){
        var byId=ApexCharts.getChartByID(canvas.id.replace(/^apexcharts/,''));
        if(byId){return byId;}
      }
    }catch(e){}
    try{
      var reg=window.Apex&&window.Apex._chartInstances;
      if(reg&&reg.length){
        for(var i=0;i<reg.length;i++){
          var c=reg[i]&&reg[i].chart;
          if(c&&c.el&&(c.el===container||container.contains(c.el))){return c;}
        }
      }
    }catch(e){}
    return null;
  }

  function fullXRange(inst){
    try{
      var s=inst.w&&inst.w.config&&inst.w.config.series;
      if(!s){return null;}
      var min=Infinity,max=-Infinity,i,j,d,x;
      for(i=0;i<s.length;i++){
        d=s[i].data||[];
        for(j=0;j<d.length;j++){
          x=Array.isArray(d[j])?d[j][0]:(d[j]&&d[j].x);
          if(typeof x==='number'&&isFinite(x)){if(x<min){min=x;}if(x>max){max=x;}}
        }
      }
      if(isFinite(min)&&isFinite(max)&&max>min){return [min,max];}
    }catch(e){}
    return null;
  }

  function resetZoom(container){
    var inst=findInstance(container);
    if(!inst){return;}
    var r=fullXRange(inst);
    try{
      if(r&&typeof inst.zoomX==='function'){inst.zoomX(r[0],r[1]);return;}
    }catch(e){}
    try{
      if(typeof inst.resetSeries==='function'){inst.resetSeries(true,true);}
    }catch(e){}
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
