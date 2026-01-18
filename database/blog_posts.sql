CREATE TABLE IF NOT EXISTS blog_posts (
  id INT PRIMARY KEY AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  category VARCHAR(50) NOT NULL,
  date DATETIME DEFAULT CURRENT_TIMESTAMP,
  author VARCHAR(100) NOT NULL,
  image VARCHAR(255),
  excerpt TEXT,
  content LONGTEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO blog_posts (title, category, date, author, image, excerpt, content) VALUES
('Nouveau service : Caméras de surveillance 24/7', 'service', '2026-01-18', 'Équipe NSM', 'assets/images/service3.jpg', 'Découvrez notre nouveau service de surveillance professionnelle.', 'Contenu complet...'),
('5 conseils pour accélérer vos démarches administratives', 'conseil', '2026-01-15', 'Admin', 'assets/images/pasport.jpg', 'Apprenez les meilleures pratiques.', 'Contenu complet...');