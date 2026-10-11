const menu=document.querySelector('.menu');
const nav=document.querySelector('.site-header nav');
menu?.addEventListener('click',()=>nav.classList.toggle('open'));
document.querySelectorAll('nav a').forEach(a=>a.addEventListener('click',()=>nav.classList.remove('open')));
document.getElementById('year').textContent=new Date().getFullYear();

const quoteForm=document.getElementById('quoteForm');
quoteForm?.addEventListener('submit',async e=>{
  e.preventDefault();
  const button=quoteForm.querySelector('button[type="submit"]');
  const note=quoteForm.querySelector('.form-note');
  const originalText=button.textContent;
  button.disabled=true;
  button.textContent='Sending…';
  note.textContent='Sending your enquiry…';

  try{
    const response=await fetch('send-enquiry.php',{
      method:'POST',
      body:new FormData(quoteForm),
      headers:{'Accept':'application/json'}
    });
    const result=await response.json().catch(()=>({}));
    if(!response.ok||!result.success) throw new Error(result.message||'Unable to send enquiry.');
    quoteForm.reset();
    note.textContent='Thanks — your enquiry has been sent. Our team will be in touch.';
  }catch(error){
    note.textContent='Sorry, your enquiry could not be sent. Please call 0412 302 674 or try again.';
  }finally{
    button.disabled=false;
    button.textContent=originalText;
  }
});