const API = location.origin.replace(/\/public$/, '') + '/api';
export const auth = {
  async req(path, opts={}){
    const headers={'Content-Type':'application/json'};
    if (opts.csrf) headers['X-CSRF-Token']=opts.csrf;
    const res=await fetch(API+path,{...opts,headers,credentials:'include'});
    const data=await res.json().catch(()=>({}));
    if(!res.ok) throw new Error(data.error||'Erro');
    return data;
  },
  async check(){ return this.req('/me.php'); },
  async login(email,password){ return this.req('/login.php',{method:'POST',body:JSON.stringify({email,password})}); },
  async post(path,body){ return this.req(path,{method:'POST',body:JSON.stringify(body)}); }
};
window.auth = auth;
