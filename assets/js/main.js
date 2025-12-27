document.addEventListener('DOMContentLoaded', function(){
  const btn = document.querySelector('.nav-toggle');
  const nav = document.querySelector('.nav');
  if(btn && nav){
    btn.addEventListener('click', ()=> nav.classList.toggle('open'));
    nav.querySelectorAll('a').forEach(link => link.addEventListener('click', () => nav.classList.remove('open')));
  }

  // EmailJS configuration — remplacer par vos valeurs
  const EMAILJS_PUBLIC_KEY = 'AdzX2pPF5aUa4A9hQ';
  const EMAILJS_SERVICE_ID = 'nsm_rfuqyff';  
  const EMAILJS_TEMPLATE_ID = 'template_5e538zh'; 
  // Liste des URLs CDN à tenter
  const EMAILJS_CDNS = [
    'https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/build/index.min.js',
    'https://unpkg.com/@emailjs/browser@4/dist/build/index.min.js'
  ];

  function loadScript(url){
    return new Promise((resolve, reject) => {
      const s = document.createElement('script');
      s.src = url;
      s.async = true;
      s.onload = () => resolve(url);
      s.onerror = (e) => reject(new Error('load error ' + url));
      document.head.appendChild(s);
    });
  }

  // Essaie plusieurs CDN avec retry simple
  async function loadEmailJsWithFallback(retries = 2, delayMs = 700){
    for(const cdn of EMAILJS_CDNS){
      for(let attempt=0; attempt<=retries; attempt++){
        try{
          await loadScript(cdn);
          if(window.emailjs && window.emailjs.send){
            try{ emailjs.init(EMAILJS_PUBLIC_KEY); }catch(e){ console.warn('emailjs.init failed', e); }
            return window.emailjs;
          }
        }catch(err){
          console.warn(`CDN ${cdn} attempt ${attempt} failed:`, err);
          if(attempt < retries) await new Promise(r => setTimeout(r, delayMs));
        }
      }
    }
    throw new Error('Tous les CDN EmailJS ont échoué.');
  }

  // --- ADDED: REST API fallback pour envoyer directement via EmailJS si CDN bloqué
  async function sendViaEmailJsApi(service_id, template_id, user_id, template_params){
    const url = 'https://api.emailjs.com/api/v1.0/email/send';
    const body = { service_id, template_id, user_id, template_params };
    const res = await fetch(url, {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify(body)
    });
    if(!res.ok){
      const txt = await res.text().catch(()=>res.statusText);
      throw new Error(`API error ${res.status}: ${txt}`);
    }
    return res;
  }

  const form = document.getElementById('contactForm');
  const sendBtn = document.getElementById('sendBtn');

  function sendViaWhatsApp(name, phone, service, message){
    const text = encodeURIComponent(`Bonjour NSM,\n\nNom: ${name}\nTéléphone: ${phone}\nService: ${service}\n\nMessage:\n${message}`);
    window.open(`https://wa.me/50940317399?text=${text}`, '_blank');
  }

  if(form && sendBtn){
    sendBtn.addEventListener('click', async function(){
        
      const status = document.getElementById('formStatus'); 
      const name = (form.name.value || '').trim();
      const phone = (form.phone.value || '').trim();
      const service = (form.service.value || '').trim();
      const message = (form.message.value || '').trim();
      if(!name || !phone || !service || !message){
        status.textContent = 'Veuillez remplir tous les champs.';
        status.style.color = '#ef4444';
        return;
      }

      status.textContent = 'Envoi en cours...';
      status.style.color = 'var(--muted)';

      try{
        await loadEmailJsWithFallback(2, 800);
      }catch(err){
        console.warn('EmailJS CDN load failed — will attempt REST API fallback:', err);
        // ne pas retourner ici — on continue et essayera l'API REST ci‑dessous
      }

      // préparation des params (mettre votre email correct)
      const templateParams = {
        client_name: name,
        client_phone: phone,
        service: service,
        message: message,
        to_email: 'nsmultiservice3@gmail.com'
      };

      // Si la librairie est chargée, utiliser emailjs.send
      if(window.emailjs && emailjs.send){
        emailjs.send(EMAILJS_SERVICE_ID, EMAILJS_TEMPLATE_ID, templateParams)
          .then(function(){
            status.textContent = '✓ Merci ! Votre message a été reçu. Nous vous contacterons sous peu.';
            status.style.color = '#10b981';
            form.reset();
          }).catch(async function(error){
            console.warn('emailjs.send failed, trying REST API:', error);
            // essai via REST API
            try{
              await sendViaEmailJsApi(EMAILJS_SERVICE_ID, EMAILJS_TEMPLATE_ID, EMAILJS_PUBLIC_KEY, templateParams);
              status.textContent = '✓ Merci ! Votre message a été reçu (via API).';
              status.style.color = '#10b981';
              form.reset();
            }catch(apiErr){
              console.error('EmailJS API send failed:', apiErr);
              status.textContent = '✗ Envoi échoué. Voulez-vous envoyer via WhatsApp ?';
              status.style.color = '#ef4444';
              if(confirm('L\'envoi par email a échoué. Envoyer via WhatsApp maintenant ?')) sendViaWhatsApp(name, phone, service, message);
            }
          });
      }else{
        // librairie non disponible → tenter directement l'API REST
        try{
          await sendViaEmailJsApi(EMAILJS_SERVICE_ID, EMAILJS_TEMPLATE_ID, EMAILJS_PUBLIC_KEY, templateParams);
          status.textContent = '✓ Merci ! Votre message a été reçu (via API).';
          status.style.color = '#10b981';
          form.reset();
        }catch(apiErr){
          console.error('EmailJS API send failed:', apiErr);
          status.textContent = '✗ Envoi échoué. Voulez-vous envoyer via WhatsApp ?';
          status.style.color = '#ef4444';
          if(confirm('L\'envoi par email a échoué. Envoyer via WhatsApp maintenant ?')) sendViaWhatsApp(name, phone, service, message);
        }
      }
    });
  }
});