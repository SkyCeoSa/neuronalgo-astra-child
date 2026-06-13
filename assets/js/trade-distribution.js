/* NeuronAlgo — Backtest Trade Distribution (FE-3.3b). Smart long/short card + hourly/weekday/duration charts. Data via window.NA_TD. Needs global ApexCharts (na-apexcharts). */
(function(){
'use strict';
var DATA=window.NA_TD;if(!DATA||typeof DATA!=='object')return;
var T={accent:'#3d7dff',cyan:'#38bdf8',violet:'#a855f7',green:'#22c55e',red:'#ef4444',amber:'#f5b94a',muted:'#637094',grid:'#1a2638',heading:'#f5f7fb',border:'#233048'};
var MONO="'JetBrains Mono',ui-monospace,Menlo,Consolas,monospace";
function num(v){return typeof v==='number'&&isFinite(v)?v:0;}
function money(v){v=num(v);return(v<0?'-$':'$')+Math.abs(v).toLocaleString('en-US',{maximumFractionDigits:0});}
function esc(s){return String(s).replace(/[&<>"]/g,function(c){return{'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c];});}
function tip(b,label){var tr=num(b.trades),wr=tr>0?num(b.wins)/tr*100:0,p=num(b.net_pnl);return'<div class="na-tt"><div class="na-tt-h">'+esc(label)+'</div><div class="na-tt-r"><span>trades</span><b>'+tr+'</b></div><div class="na-tt-r"><span>win rate</span><b>'+wr.toFixed(1)+'%</b></div><div class="na-tt-r"><span>net p&amp;l</span><b class="'+(p>=0?'pp':'nn')+'">'+money(p)+'</b></div></div>';}
function hasB(o){return o&&o.buckets&&o.buckets.length;}
function dirHtml(){
var ls=DATA.longShort;if(!ls||typeof ls!=='object')return'';
var L=ls.long||{},S=ls.short||{},lOn=num(L.trades)>0,sOn=num(S.trades)>0;if(!lOn&&!sOn)return'';
var badge,donut,legend,note='';
if(lOn&&sOn){var lt=num(L.trades),st=num(S.trades),tot=lt+st,sh=tot>0?lt/tot:0;badge='LONG + SHORT';
donut='<div class="donut" style="background:conic-gradient('+T.accent+' '+(sh*360).toFixed(1)+'deg,'+T.violet+' 0)"><div class="dc"><b>'+tot+'</b><span>trades</span></div></div>';
legend='<div class="legend"><div class="row"><span class="dot" style="background:'+T.accent+'"></span>Long<span class="v">'+lt+'</span></div><div class="row"><span class="dot" style="background:'+T.violet+'"></span>Short<span class="v">'+st+'</span></div></div>';
}else{var d=lOn?L:S,w=num(d.wins),l=num(d.losses),wl=w+l,ws=wl>0?w/wl:0;badge=lOn?'LONG-ONLY':'SHORT-ONLY';
donut='<div class="donut" style="background:conic-gradient('+T.green+' '+(ws*360).toFixed(1)+'deg,'+T.red+' 0)"><div class="dc"><b>'+(ws*100).toFixed(1)+'%</b><span>win</span></div></div>';
legend='<div class="legend"><div class="row"><span class="dot" style="background:'+T.green+'"></span>Wins<span class="v">'+w+'</span></div><div class="row"><span class="dot" style="background:'+T.red+'"></span>Losses<span class="v">'+l+'</span></div><div class="row"><span class="dot" style="background:'+T.border+'"></span>Total<span class="v">'+num(d.trades)+'</span></div></div>';
note='<div class="na-td-note">// '+(lOn?'short':'long')+' trades: 0 — single-direction system</div>';}
return'<div class="tlet na-td-tile"><div class="glet-bar"><span>direction_split</span><span class="s"></span></div><div class="tlet-in"><span class="na-td-badge">'+badge+'</span><div class="donut-wrap">'+donut+legend+'</div>'+note+'</div></div>';
}
function perfHtml(){
var ls=DATA.longShort;if(!ls||typeof ls!=='object')return'';
var L=ls.long||{},S=ls.short||{},sides=[];
if(num(L.trades)>0)sides.push(['LONG',L]);if(num(S.trades)>0)sides.push(['SHORT',S]);if(!sides.length)return'';
var mx=1;sides.forEach(function(s){mx=Math.max(mx,Math.abs(num(s[1].net_pnl)));});
var rows=sides.map(function(s){var k=s[0],d=s[1],wr=num(d.win_rate)*100,p=num(d.net_pnl),pw=Math.round(Math.abs(p)/mx*100);
return'<div class="na-td-perf-grp"><div class="na-td-perf-h">'+k+' · '+num(d.trades)+' trades</div><div class="line"><div class="top"><span>Win rate</span><span class="num">'+wr.toFixed(2)+'%</span></div><div class="bar"><i style="width:'+Math.min(100,wr).toFixed(1)+'%;background:'+T.cyan+'"></i></div></div><div class="line"><div class="top"><span>Net P&amp;L</span><span class="num '+(p>=0?'pos':'neg')+'">'+money(p)+'</span></div><div class="bar"><i style="width:'+pw+'%;background:'+(p>=0?T.green:T.red)+'"></i></div></div></div>';}).join('');
return'<div class="tlet na-td-tile"><div class="glet-bar"><span>direction_performance</span><span class="s"></span></div><div class="tlet-in"><div class="cmp">'+rows+'</div></div></div>';
}
function cw(id,arg,note){return'<div class="na-td-chartwrap"><p class="cmd"><span class="p">$</span> plot <span class="arg">'+arg+'</span><span class="caret"></span></p><p class="note">'+note+'</p><div class="chart-box"><div class="na-td-chart" id="'+id+'"></div></div></div>';}
function init(){
var root=document.getElementById('na-backtest-single');if(!root)return;
var dg=dirHtml()+perfHtml(),charts='';
if(hasB(DATA.hourly))charts+=cw('na-td-hourly','--by-hour','trade count by entry hour (UTC) · hover for win-rate &amp; P&amp;L');
if(hasB(DATA.weekday))charts+=cw('na-td-weekday','--by-weekday','trade count by weekday · hover for win-rate &amp; P&amp;L');
if(hasB(DATA.duration))charts+=cw('na-td-duration','--by-holding-time','trade count by holding time · hover for win-rate &amp; P&amp;L');
if(!dg&&!charts)return;
var html='<div class="winbar"><span class="dots"><i></i><i></i><i></i></span><span class="fname"><b>trade_distribution</b> --analyze</span><span class="wstat">ok</span></div><div class="winbody"><p class="cmd"><span class="p">$</span> trades <span class="arg">--distribution</span><span class="caret"></span></p><p class="note">direction split + time-of-day, weekday &amp; holding-time distributions</p>'+(dg?'<div class="tb-grid na-td-grid">'+dg+'</div>':'')+charts+'</div>';
var sec=document.createElement('section');sec.className='win na-td-section';sec.innerHTML=html;
var anchor=null,wins=root.querySelectorAll('.win');
for(var i=0;i<wins.length;i++){var b=wins[i].querySelector('.fname b');if(b&&b.textContent.trim()==='trade_breakdown'){anchor=wins[i];break;}}
if(anchor&&anchor.parentNode)anchor.parentNode.insertBefore(sec,anchor.nextSibling);else root.appendChild(sec);
var flag=root.querySelector('.flag');if(flag&&flag.parentNode)flag.parentNode.removeChild(flag);
render();
}
function render(){
if(typeof ApexCharts==='undefined')return;
var base={fontFamily:MONO,foreColor:T.muted,toolbar:{show:false},animations:{enabled:true,speed:600}},g={borderColor:T.grid,strokeDashArray:4};
if(hasB(DATA.hourly)){var H=DATA.hourly.buckets;new ApexCharts(document.getElementById('na-td-hourly'),{chart:Object.assign({type:'bar',height:300},base),series:[{name:'Trades',data:H.map(function(b){return num(b.trades);})}],colors:[T.accent],fill:{type:'gradient',gradient:{shade:'dark',type:'vertical',gradientToColors:[T.cyan],stops:[0,100],opacityFrom:.95,opacityTo:.65}},plotOptions:{bar:{columnWidth:'66%',borderRadius:3}},dataLabels:{enabled:false},grid:g,xaxis:{categories:H.map(function(b){return('0'+num(b.hour)).slice(-2);}),axisBorder:{color:T.grid},axisTicks:{color:T.grid},labels:{style:{colors:T.muted,fontSize:'10px',fontFamily:MONO}}},yaxis:{labels:{style:{colors:T.muted,fontFamily:MONO}}},tooltip:{custom:function(o){return tip(H[o.dataPointIndex],('0'+num(H[o.dataPointIndex].hour)).slice(-2)+':00');}}}).render();}
if(hasB(DATA.weekday)){var W=DATA.weekday.buckets;new ApexCharts(document.getElementById('na-td-weekday'),{chart:Object.assign({type:'bar',height:280},base),series:[{name:'Trades',data:W.map(function(b){return num(b.trades);})}],colors:[T.cyan],plotOptions:{bar:{columnWidth:'52%',borderRadius:4}},dataLabels:{enabled:true,offsetY:-16,style:{fontSize:'10px',fontFamily:MONO,colors:[T.heading]}},grid:g,xaxis:{categories:W.map(function(b){return b.label||('d'+num(b.weekday));}),axisBorder:{color:T.grid},axisTicks:{color:T.grid},labels:{style:{colors:T.muted,fontFamily:MONO}}},yaxis:{labels:{style:{colors:T.muted,fontFamily:MONO}}},tooltip:{custom:function(o){return tip(W[o.dataPointIndex],W[o.dataPointIndex].label||('day '+num(W[o.dataPointIndex].weekday)));}}}).render();}
if(hasB(DATA.duration)){var D=DATA.duration.buckets;new ApexCharts(document.getElementById('na-td-duration'),{chart:Object.assign({type:'bar',height:280},base),series:[{name:'Trades',data:D.map(function(b){return num(b.trades);})}],colors:[T.amber],plotOptions:{bar:{horizontal:true,barHeight:'58%',borderRadius:4}},dataLabels:{enabled:true,offsetX:2,style:{fontSize:'10px',fontFamily:MONO,colors:['#070b14']}},grid:g,xaxis:{categories:D.map(function(b){return b.bucket;}),axisBorder:{color:T.grid},axisTicks:{color:T.grid},labels:{style:{colors:T.muted,fontFamily:MONO}}},yaxis:{labels:{style:{colors:T.muted,fontFamily:MONO,fontSize:'11px'}}},tooltip:{custom:function(o){return tip(D[o.dataPointIndex],D[o.dataPointIndex].bucket);}}}).render();}
}
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
})();
