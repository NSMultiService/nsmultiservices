document.addEventListener('DOMContentLoaded', function(){
  const STORAGE_KEY = 'nsm_reviews_v1';
  const EMAILJS_PUBLIC_KEY = 'AdzX2pPF5aUa4A9hQ';
  const EMAILJS_SERVICE_ID = 'nsm_rfuqyff';
  const EMAILJS_TEMPLATE_ID = 'template_5e538zh';
  const OWNER_EMAIL = 'nsmultiservice3@gmail.com';

  const form = document.getElementById('reviewForm');
  const list = document.getElementById('reviewsList');
  const status = document.getElementById('reviewStatus');

  function loadReviews(){ 
    try{ return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]'); }catch{ return []; }
  }
  function saveReviews(arr){ localStorage.setItem(STORAGE_KEY, JSON.stringify(arr)); }

  function render(){
    const reviews = loadReviews().sort((a,b)=> b.date - a.date);
    list.innerHTML = reviews.length ? reviews.map(r => `
      <article class="review-card card">
        <div class="review-head"><strong>${escapeHtml(r.name)}</strong><span class="muted"> — ${r.service || 'Général'}</span></div>
        <div class="review-body"><div class="rating">` + '★'.repeat(r.rating) + '☆'.repeat(5-r.rating) + `</div><p>${escapeHtml(r.message)}</p></div>
        <div class="review-meta muted">${new Date(r.date).toLocaleString('fr-FR')}</div>
      </article>`).join('') : '<p class="muted">Aucun avis pour le moment.</p>';
  }

  function escapeHtml(s){ return String(s||'').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c]); }

  async function sendNotification(params){
    // envoi via API REST EmailJS (ne dépend pas du CDN)
    const url = 'https://api.emailjs.com/api/v1.0/email/send';
    const body = {
      service_id: EMAILJS_SERVICE_ID,
      template_id: EMAILJS_TEMPLATE_ID,
      user_id: EMAILJS_PUBLIC_KEY,
      template_params: {
        reviewer_name: params.name,
        reviewer_phone: params.phone || '',
        reviewer_service: params.service || '',
        reviewer_rating: params.rating,
        reviewer_message: params.message,
        to_email: OWNER_EMAIL
      }
    };
    const res = await fetch(url, { method: 'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(body) });
    if(!res.ok) throw new Error('EmailJS API error ' + res.status);
    return res;
  }

  form && form.addEventListener('submit', async function(e){
    e.preventDefault();
    const name = form.name.value.trim();
    const service = form.service.value.trim();
    const rating = parseInt(form.rating.value || '0', 10);
    const message = form.message.value.trim();
    if(!name || !rating || !message){ status.textContent = 'Veuillez remplir tous les champs requis.'; status.style.color = '#ef4444'; return; }
    const review = { name, service, rating, message, date: Date.now() };
    const reviews = loadReviews(); reviews.push(review); saveReviews(reviews); render();
    status.textContent = 'Publication en cours...'; status.style.color = 'var(--muted)';

    try{
      await sendNotification({ name, phone:'', service, rating, message });
      status.textContent = 'Merci — votre avis est publié et nous en avons été informés.';
      status.style.color = '#10b981';
    }catch(err){
      console.warn('Notification failed:', err);
      status.textContent = 'Avis publié localement, mais l\'alerte email a échoué. Vous pouvez envoyer via WhatsApp.';
      status.style.color = '#ef4444';
    }
    form.reset();
  });

  render();
});