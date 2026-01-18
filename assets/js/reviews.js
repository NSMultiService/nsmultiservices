document.addEventListener('DOMContentLoaded', function(){
  const STORAGE_KEY = 'nsm_reviews_v1';
  const EMAILJS_PUBLIC_KEY = 'AdzX2pPF5aUa4A9hQ';
  const EMAILJS_SERVICE_ID = 'nsm_rfuqyff';
  const EMAILJS_TEMPLATE_ID = 'template_5e538zh';
  const OWNER_EMAIL = 'nsmultiservice3@gmail.com';

  const form = document.getElementById('reviewForm');
  const list = document.getElementById('reviewsList');
  const status = document.getElementById('reviewStatus');

  // Charger les avis depuis localStorage (persiste même après fermeture du navigateur)
  function loadReviews(){ 
    try{ 
      const stored = localStorage.getItem(STORAGE_KEY);
      return stored ? JSON.parse(stored) : [];
    }catch(e){ 
      console.error('Erreur lecture localStorage:', e);
      return []; 
    }
  }

  // Sauvegarder les avis dans localStorage
  function saveReviews(arr){ 
    try{
      localStorage.setItem(STORAGE_KEY, JSON.stringify(arr));
    }catch(e){
      console.error('Erreur écriture localStorage:', e);
    }
  }

  // Afficher tous les avis (les plus récents d'abord)
  function render(){
    const reviews = loadReviews().sort((a,b)=> b.date - a.date);
    if(list){
      list.innerHTML = reviews.length ? reviews.map(r => `
        <article class="review-card card">
          <div class="review-head">
            <strong>${escapeHtml(r.name)}</strong>
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

      // Ajouter l'avis à la liste et sauvegarder
      const reviews = loadReviews();
      reviews.push(review);
      saveReviews(reviews);
      
      // Afficher immédiatement le nouvel avis
      render();
      
      status.textContent = 'Publication en cours...'; 
      status.style.color = 'var(--muted)';

      // Envoyer une notification au propriétaire
      try{
        await sendNotification({ name, service, rating, message });
        status.textContent = '✓ Merci ! Votre avis est publié. Nous en avons été informés.';
        status.style.color = '#10b981';
      }catch(err){
        console.warn('Notification email échouée:', err);
        status.textContent = '✓ Avis publié avec succès. (Alerte email échouée, mais votre avis est visible)';
        status.style.color = '#10b981';
      }
      
      form.reset();
    });
  }

  // Afficher les avis au chargement de la page
  render();
});