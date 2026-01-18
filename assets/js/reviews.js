document.addEventListener('DOMContentLoaded', function(){
  const STORAGE_KEY = 'nsm_reviews_v1';
  const REVIEWS_API = '/api/reviews.php';
  const EMAILJS_PUBLIC_KEY = 'AdzX2pPF5aUa4A9hQ';
  const EMAILJS_SERVICE_ID = 'nsm_rfuqyff';
  const EMAILJS_TEMPLATE_ID = 'template_5e538zh';
  const OWNER_EMAIL = 'nsmultiservice3@gmail.com';

  const form = document.getElementById('reviewForm');
  const list = document.getElementById('reviewsList');
  const status = document.getElementById('reviewStatus');

  // Charger les avis depuis l'API serveur (fallback localStorage si offline)
  async function fetchReviews(){
    try{
      const res = await fetch(REVIEWS_API);
      if(!res.ok) throw new Error('Fetch failed ' + res.status);
      const json = await res.json();
      return Array.isArray(json) ? json : [];
    }catch(err){
      console.warn('Récupération via API échouée, fallback localStorage:', err);
      try{ const stored = localStorage.getItem(STORAGE_KEY); return stored ? JSON.parse(stored) : []; }catch(e){ return []; }
    }
  }

  // Envoyer un avis vers l'API serveur (et fallback localStorage si échec)
  async function postReviewToServer(review){
    try{
      const res = await fetch(REVIEWS_API, {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify(review)
      });
      if(!res.ok) throw new Error('API error ' + res.status);
      return await res.json();
    }catch(err){
      console.warn('Envoi au serveur échoué, sauvegarde locale:', err);
      try{ const reviews = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]'); reviews.push(review); localStorage.setItem(STORAGE_KEY, JSON.stringify(reviews)); }catch(e){}
      return null;
    }
  }

  // Afficher tous les avis (les plus récents d'abord)
  async function render(){
    const reviews = (await fetchReviews()).sort((a,b)=> b.date - a.date);
    if(list){
      list.innerHTML = reviews.length ? reviews.map(r => `
        <article class="review-card card">
          <div class="review-head">
            <strong>${escapeHtml(r.name || 'Anonyme')}</strong>
            <span class="muted"> — ${r.service || 'Service général'}</span>
          </div>
          <div class="review-body">
            <div class="rating">${'★'.repeat(r.rating)}${'☆'.repeat(5-r.rating)}</div>
            <p>${escapeHtml(r.message)}</p>
          </div>
          <div class="review-meta muted">${new Date(r.date).toLocaleDateString('fr-FR', {year:'numeric',month:'long',day:'numeric',hour:'2-digit',minute:'2-digit'})}</div>
        </article>
      `).join('') : '<p class="muted">Aucun avis pour le moment. Soyez le premier à laisser un avis !</p>';
    }
  }

  // Échapper les caractères HTML pour éviter les injections
  function escapeHtml(s){ 
    return String(s||'').replace(/[&<>"']/g, c => ({
      '&':'&amp;',
      '<':'&lt;',
      '>':'&gt;',
      '"':'&quot;',
      "'":"&#39;"
    })[c]); 
  }

  // Envoyer une notification email au propriétaire
  async function sendNotification(params){
    const url = 'https://api.emailjs.com/api/v1.0/email/send';
    const body = {
      service_id: EMAILJS_SERVICE_ID,
      template_id: EMAILJS_TEMPLATE_ID,
      user_id: EMAILJS_PUBLIC_KEY,
      template_params: {
        reviewer_name: params.name,
        reviewer_service: params.service || 'Service général',
        reviewer_rating: '★'.repeat(params.rating),
        reviewer_message: params.message,
        to_email: OWNER_EMAIL
      }
    };
    const res = await fetch(url, { 
      method: 'POST', 
      headers:{'Content-Type':'application/json'}, 
      body: JSON.stringify(body) 
    });
    if(!res.ok) throw new Error('EmailJS API error ' + res.status);
    return res;
  }

  // Gérer la soumission du formulaire d'avis
  if(form){
    form.addEventListener('submit', async function(e){
      e.preventDefault();
      const name = form.name.value.trim();
      const service = form.service.value.trim();
      const rating = parseInt(form.rating.value || '0', 10);
      const message = form.message.value.trim();

      // Validation
      if(!name || !rating || !message){ 
        status.textContent = 'Veuillez remplir tous les champs requis.'; 
        status.style.color = '#ef4444'; 
        return; 
      }

      // Créer l'objet avis
      const review = { 
        name, 
        service, 
        rating, 
        message, 
        date: Date.now() 
      };

      status.textContent = 'Publication en cours...';
      status.style.color = 'var(--muted)';

      // Poster au serveur (fallback localStorage si échec)
      try{
        await postReviewToServer(review);
      }catch(e){
        console.warn('Erreur en postReviewToServer:', e);
      }

      // Envoyer une notification au propriétaire (email)
      try{
        await sendNotification({ name, service, rating, message });
      }catch(err){
        console.warn('Notification email échouée:', err);
      }

      status.textContent = '✓ Merci ! Votre avis est publié.';
      status.style.color = '#10b981';
      form.reset();
      render();
    });
  }

  // Afficher les avis au chargement de la page
  render();
});