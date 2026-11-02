const fs=require('fs');
(async()=>{
const tabs=await(await fetch('http://127.0.0.1:9337/json')).json();
const tab=tabs.find(t=>t.url.includes('AEnhance-Presentation-FR.html'));
const ws=new WebSocket(tab.webSocketDebuggerUrl); await new Promise(r=>ws.addEventListener('open',r,{once:true}));
let id=0;const pending=new Map();ws.addEventListener('message',e=>{const m=JSON.parse(e.data);if(pending.has(m.id)){pending.get(m.id)(m);pending.delete(m.id);}});
const call=(method,params={})=>new Promise(r=>{pending.set(++id,r);ws.send(JSON.stringify({id,method,params}));});
await call('Emulation.setDeviceMetricsOverride',{width:1280,height:760,deviceScaleFactor:1,mobile:false});
const check=await call('Runtime.evaluate',{expression:`JSON.stringify([...document.querySelectorAll('.slide')].map((s,i)=>{const f=s.querySelector('.footer').getBoundingClientRect();const a=[...s.children].filter(x=>!x.classList.contains('footer'));return {slide:i+1,overlap:a.filter(x=>x.getBoundingClientRect().bottom>f.top-5).map(x=>x.className)}}))`,returnByValue:true});
console.log(check.result.result.value);
await call('Runtime.evaluate',{expression:"document.body.classList.add('present');window.scrollTo(0,0)"});
const shot=await call('Page.captureScreenshot',{format:'png',captureBeyondViewport:false});fs.writeFileSync(__dirname+'/presentation-preview.png',Buffer.from(shot.result.data,'base64'));
await call('Runtime.evaluate',{expression:"document.querySelectorAll('.slide')[8].scrollIntoView()"});
const shot2=await call('Page.captureScreenshot',{format:'png',captureBeyondViewport:false});fs.writeFileSync(__dirname+'/presentation-video-preview.png',Buffer.from(shot2.result.data,'base64'));
await call('Browser.close');ws.close();
})().catch(e=>{console.error(e);process.exit(1)});
