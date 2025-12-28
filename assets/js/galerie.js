document.addEventListener('DOMContentLoaded', function(){
  const STORAGE_KEY = 'nsm_gallery_v1';

  // Données par défaut (exemples — remplacer par vos images réelles)
  const DEFAULT_ITEMS = [
    { id: 1, title: 'Impression couleur', category: 'impression', image: 'assets/images/livre.jpg', description: 'un livre professionnelle en couleur haute qualité' },
    { id: 2, title: 'Passeport traité', category: 'administratif', image: 'assets/images/pasport.jpg', description: 'Service passeport rapide et fiable' },
    { id: 3, title: 'Photo Pro', category: 'informatique', image: 'assets/images/photopro.jpg', description: 'Design et impression photo professionnel' },
    { id: 4, title: 'Mastercard ', category: 'administratif', image: 'assets/images/mastercard.jpg', description: 'MasterCard virtuel (FYATU)' },
    { id: 5, title: 'Affiche Pro', category: 'informatique', image: 'assets/images/affiche1.jpg', description: 'Affiche professionnel' },
    { id: 6, title: 'Documents légalisés', category: 'administratif', image: 'assets/images/diplome.png', description: ' Demande et Légalisation de documents pour l\'étranger et local' },
    { id: 7, title: 'Création et Impression', category: 'impression', image: 'assets/images/album.jpg', description: 'Création et impression Album professionnelle en couleur haute qualité'},
    { id: 8, title: 'Archives', category: 'administratif', image: 'assets/images/achivre.jpg', description: 'Service Archives rapide et fiable' },
    { id: 9, title: 'Affiche Professionnel', category: 'informatique', image: 'assets/images/affiche.jpg', description: 'Affiche simple et professionnel' },
    { id: 10, title: 'Reliure professionnelle', category: 'impression', image: 'assets/images/reliure.jpg', description: 'Reliure en spirale et thermique' },
    { id: 11, title: 'Création de logo', category: 'informatique', image: 'assets/images/logo1.jpg', description: 'Création logo simple et professionnel' },
    { id: 12, title: 'Permis de conduire', category: 'administratif', image: 'assets/images/permis de conduire.jpg', description: 'Permis de conduire type AC, AB' }
  ];

  const grid = document.getElementById('galleryGrid');
  const modal = document.getElementById('imageModal');
  const modalImg = document.getElementById('modalImg');
  const modalCaption = document.getElementById('modalCaption');
  const filterBtns = document.querySelectorAll('.filter-btn');

  let currentFilter = 'all';
  let currentImageIndex = 0;
  let currentImages = [];

  // Charger les images depuis localStorage ou utiliser les données par défaut
  function loadGallery(){
    try{
      const stored = localStorage.getItem(STORAGE_KEY);
      return stored ? JSON.parse(stored) : DEFAULT_ITEMS;
    }catch(e){
      console.error('Erreur lecture galerie:', e);
      return DEFAULT_ITEMS;
    }
  }

  // Sauvegarder la galerie dans localStorage
  function saveGallery(items){
    try{
      localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
    }catch(e){
      console.error('Erreur écriture galerie:', e);
    }
  }

  // Rendre la galerie
  function render(filter = 'all'){
    const items = loadGallery();
    const filtered = filter === 'all' ? items : items.filter(i => i.category === filter);
    currentImages = filtered;
    currentFilter = filter;

    grid.innerHTML = filtered.map((item, idx) => `
      <div class="gallery-item" data-id="${item.id}">
        <div class="gallery-image-wrapper">
          <img src="${item.image}" alt="${item.title}" class="gallery-image" onclick="openImage(${idx})">
          <div class="gallery-overlay">
            <h3>${escapeHtml(item.title)}</h3>
            <p>${escapeHtml(item.description)}</p>
          </div>
        </div>
      </div>
    `).join('');
  }

  // Ouvrir l'image en modal
  window.openImage = function(idx){
    currentImageIndex = idx;
    const item = currentImages[idx];
    modalImg.src = item.image;
    modalCaption.textContent = `${item.title} — ${item.description}`;
    modal.style.display = 'flex';
  };

  // Changer d'image dans le modal
  window.changeImage = function(n){
    currentImageIndex = (currentImageIndex + n + currentImages.length) % currentImages.length;
    const item = currentImages[currentImageIndex];
    modalImg.src = item.image;
    modalCaption.textContent = `${item.title} — ${item.description}`;
  };

  // Fermer le modal
  const closeBtn = document.querySelector('.modal-close');
  if(closeBtn){
    closeBtn.onclick = function(){ modal.style.display = 'none'; };
  }
  window.onclick = function(e){
    if(e.target === modal) modal.style.display = 'none';
  };

  // Filtres
  filterBtns.forEach(btn => {
    btn.addEventListener('click', function(){
      filterBtns.forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      render(this.dataset.filter);
    });
  });

  // Échapper HTML
  function escapeHtml(s){
    return String(s||'').replace(/[&<>"']/g, c => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    })[c]);
  }

  // Rendu initial
  render();
});