const sw=document.getElementById('theme');
const apply=(light)=>{ document.documentElement.classList.toggle('light',light); localStorage.setItem('dimoto_theme', light?'light':'dark'); };
const saved=localStorage.getItem('dimoto_theme'); apply(saved==='light'); if(sw) sw.checked=(saved==='light');
if(sw) sw.addEventListener('change',()=>apply(sw.checked));
