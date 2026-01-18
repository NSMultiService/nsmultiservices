// Données des articles blog
const blogPosts = [
  {
    id: 1,
    title: "Nouveau service : Caméras de surveillance 24/7",
    category: "service",
    date: "2026-01-18",
    author: "Équipe NSM",
    image: "assets/images/camera.jpg",
    excerpt: "Découvrez notre nouveau service de surveillance professionnelle avec installation complète.",
    content: "Nous sommes heureux d'annoncer le lancement de notre service de caméras de surveillance. Nos experts vous offrent une installation professionnelle avec suivi 24/7..."
  },
  {
    id: 2,
    title: "5 conseils pour accélérer vos démarches administratives",
    category: "conseil",
    date: "2026-01-15",
    author: "Admin",
    image: "assets/images/pasport.jpg",
    excerpt: "Apprenez les meilleures pratiques pour faciliter vos démarches.",
    content: "Les démarches administratives peuvent être longues. Voici 5 conseils pour les accélérer..."
  },
  {
    id: 3,
    title: "Augmentation tarifaire - Janvier 2026",
    category: "annonce",
    date: "2026-01-10",
    author: "Équipe NSM",
    image: "assets/images/mastercard.jpg",
    excerpt: "Notification importante concernant nos tarifs applicables à partir de janvier.",
    content: "À compter du 1er janvier 2026, certains de nos tarifs seront ajustés pour maintenir la qualité de nos services..."
  },
  {
    id: 4,
    title: "Comment bien préparer votre album de photos",
    category: "conseil",
    date: "2026-01-05",
    author: "Expert Admin",
    image: "assets/images/album.jpg",
    excerpt: "Guide complet pour préparer votre demande de passeport sans erreur.",
    content: "Obtenir un passeport nécessite une préparation minutieuse. Voici tous les documents et conseils..."
  },
  {
    id: 5,
    title: "Horaires spéciaux pendant le Carnaval",
    category: "annonce",
    date: "2025-12-28",
    author: "Équipe NSM",
    image: "assets/images/affihe2.jpg",
    excerpt: "Nos horaires modifiés lors de la période du Carnaval 2026.",
    content: "Durant la période carnavalesque, nos horaires seront différents. Consulter nos horaires spéciaux..."
  },
  {
    id: 6,
    title: "L'importance de la plastification pour vos documents",
    category: "conseil",
    date: "2025-12-20",
    author: "Service Impression",
    image: "assets/images/reliure.jpg",
    excerpt: "Pourquoi la plastification protège vos documents importants.",
    content: "La plastification est un service essentiel pour protéger vos documents des dégâts..."
  }
];

let currentFilter = 'all';
let currentPage = 1;
const postsPerPage = 6;

// Initialiser
document.addEventListener('DOMContentLoaded', () => {
  setupFilterButtons();
  renderBlogPosts();
});

// Configuration des boutons filtres
function setupFilterButtons() {
  document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
      e.target.classList.add('active');
      currentFilter = e.target.dataset.category;
      currentPage = 1;
      renderBlogPosts();
    });
  });
}

// Filtrer les articles
function getFilteredPosts() {
  if (currentFilter === 'all') {
    return blogPosts;
  }
  return blogPosts.filter(post => post.category === currentFilter);
}

// Rendre les articles
function renderBlogPosts() {
  const filtered = getFilteredPosts();
  const startIndex = (currentPage - 1) * postsPerPage;
  const endIndex = startIndex + postsPerPage;
  const posts = filtered.slice(startIndex, endIndex);
  
  const blogGrid = document.getElementById('blogGrid');
  blogGrid.innerHTML = '';
  
  if (posts.length === 0) {
    blogGrid.innerHTML = '<p style="text-align: center; grid-column: 1/-1;">Aucun article trouvé.</p>';
    return;
  }
  
  posts.forEach(post => {
    const article = document.createElement('article');
    article.className = 'blog-card';
    article.innerHTML = `
      <div class="blog-image">
        <img src="${post.image}" alt="${post.title}">
        <span class="blog-category">${getCategoryLabel(post.category)}</span>
      </div>
      <div class="blog-content">
        <h3>${post.title}</h3>
        <div class="blog-meta">
          <span class="blog-date">${formatDate(post.date)}</span>
          <span class="blog-author">Par ${post.author}</span>
        </div>
        <p>${post.excerpt}</p>
        <a href="#" class="blog-read-more" onclick="viewPost(${post.id}); return false;">Lire la suite →</a>
      </div>
    `;
    blogGrid.appendChild(article);
  });
  
  renderPagination(filtered.length);
}

// Rendu de la pagination
function renderPagination(totalPosts) {
  const totalPages = Math.ceil(totalPosts / postsPerPage);
  const pagination = document.getElementById('pagination');
  pagination.innerHTML = '';
  
  if (totalPages <= 1) return;
  
  if (currentPage > 1) {
    const prevBtn = document.createElement('button');
    prevBtn.textContent = '← Précédent';
    prevBtn.className = 'pagination-btn';
    prevBtn.onclick = () => {
      currentPage--;
      renderBlogPosts();
      window.scrollTo(0, 0);
    };
    pagination.appendChild(prevBtn);
  }
  
  for (let i = 1; i <= totalPages; i++) {
    const btn = document.createElement('button');
    btn.textContent = i;
    btn.className = `pagination-btn ${i === currentPage ? 'active' : ''}`;
    btn.onclick = () => {
      currentPage = i;
      renderBlogPosts();
      window.scrollTo(0, 0);
    };
    pagination.appendChild(btn);
  }
  
  if (currentPage < totalPages) {
    const nextBtn = document.createElement('button');
    nextBtn.textContent = 'Suivant →';
    nextBtn.className = 'pagination-btn';
    nextBtn.onclick = () => {
      currentPage++;
      renderBlogPosts();
      window.scrollTo(0, 0);
    };
    pagination.appendChild(nextBtn);
  }
}

// Utilitaires
function getCategoryLabel(category) {
  const labels = {
    annonce: '📢 Annonce',
    conseil: '💡 Conseil',
    service: '🎯 Service'
  };
  return labels[category] || category;
}

function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
}

function viewPost(id) {
  const post = blogPosts.find(p => p.id === id);
  if (post) {
    alert(`${post.title}\n\n${post.content}`);
  }
}