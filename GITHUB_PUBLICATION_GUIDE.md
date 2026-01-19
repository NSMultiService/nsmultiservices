# 🚀 Guide de Configuration pour GitHub

## ⚠️ Avant de Publier sur GitHub

Assurez-vous que vous avez fait ceci:

### 1. ✅ Fichier `.gitignore` Configuré
Le fichier `.gitignore` ignorera automatiquement:
- ❌ `config/db.php` (identifiants sensibles)
- ❌ `database/backups/` (données sensibles)
- ❌ Fichiers temporaires et caches

### 2. ✅ Exemple de Configuration
Un fichier `config/db.example.php` est fourni pour montrer la structure.

### 3. ✅ Pas de Secrets dans le Code
Vérifiez qu'il n'y a pas de:
- Mots de passe
- Clés API
- Identifiants de base de données
- Jetons d'authentification

---

## 📋 Checklist Avant Publication

- [ ] `.gitignore` est correct
- [ ] `config/db.php` NE sera PAS committé (voir .gitignore)
- [ ] Pas de fichiers sensibles dans `database/backups/`
- [ ] README.md mis à jour avec instructions d'installation
- [ ] Aucun fichier `.env` contenant des secrets

---

## 🔐 Bonnes Pratiques GitHub

### ✅ À Publier:
- Code source (*.php, *.js, *.css, *.html)
- Scripts de création de BD (database/reset_database_v2.php)
- Fichiers de configuration EXEMPLE (*.example.php)
- Documentation (*.md)
- Fichier .gitignore

### ❌ À NE PAS Publier:
- Fichiers de configuration réels (db.php)
- Sauvegardes de base de données (*.sql)
- Fichiers d'upload/données sensibles
- Fichiers temporaires

---

## 📝 Instructions d'Installation pour Autres Développeurs

Quand quelqu'un clone votre projet, il doit:

```bash
# 1. Cloner le projet
git clone https://github.com/NSMultiService/nsmultiservices.git
cd nsmultiservices

# 2. Copier le fichier de configuration
cp config/db.example.php config/db.php

# 3. Éditer le fichier avec ses paramètres
# Windows: copy config\db.example.php config\db.php
# Puis éditer config/db.php

# 4. Créer la base de données
# Ouvrir: http://localhost/nsm-website/database/reset_database_v2.php

# 5. Tester
# Ouvrir: http://localhost/nsm-website/
```

---

## 🔍 Vérifier Avant de Pousser

Avant de faire `git push`:

```bash
# Voir ce qui va être committé
git status

# Vérifier que config/db.php est ignoré
git check-ignore -v config/db.php
# Devrait afficher: config/db.php

# Vérifier qu'aucun fichier sensible ne sera poussé
git ls-files --others --exclude-standard

# Ne rien voir dans les fichiers sensibles!
```

---

## ✅ Vous Pouvez Publier Si:

✅ `git check-ignore -v config/db.php` retourne quelque chose  
✅ Aucun fichier `.sql` dans `database/backups/` ne sera committé  
✅ Pas de fichier `.env` dans le projet  
✅ Tous les secrets sont dans des fichiers `.example.php`  

---

## 🛡️ Sécurité Résumée

```
GitHub = PUBLIC (tout le monde peut voir)
↓
❌ Pas de mots de passe
❌ Pas d'identifiants
❌ Pas de clés API
❌ Pas de données sensibles
↓
✅ Seulement du code réutilisable
✅ Instructions d'installation
✅ Fichiers de configuration EXEMPLE
```

---

**Vous êtes maintenant prêt à publier sur GitHub en toute sécurité! 🎉**
