# ✅ CHECKLIST PUBLICATION GITHUB

## 🔐 Sécurité (CRITIQUE)

- [ ] **config/db.php** n'existe PAS dans le projet (ou le .gitignore l'exclut)
  ```bash
  git check-ignore -v config/db.php
  # Doit retourner: config/db.php  nsm-website/.gitignore:6
  ```

- [ ] **database/backups/** est vide ou ignoré
  
- [ ] Pas de fichiers `.env` non ignorés

- [ ] Pas de mots de passe dans le code source

- [ ] Pas de clés API dans le code source

## 📋 Fichiers Requis

- [ ] `config/db.example.php` - Exemple de configuration
- [ ] `.gitignore` - Fichiers à ignorer
- [ ] `README.md` - Documentation principale
- [ ] `GITHUB_PUBLICATION_GUIDE.md` - Ce guide

## 📝 Documentation

- [ ] README.md contient les instructions d'installation
- [ ] README.md contient les prérequis (PHP, MySQL, etc.)
- [ ] Fichier d'exemple de configuration (db.example.php)
- [ ] Guide d'installation clair

## 🧪 Tests Avant Push

Exécutez ces commandes:

```bash
# 1. Vérifier les fichiers qui seront committes
git status

# 2. Vérifier que les fichiers sensibles sont ignorés
git check-ignore -v config/db.php
git check-ignore -v database/backups/

# 3. Voir tous les fichiers qui seront poussés
git ls-files --others --exclude-standard
# Ne devrait rien retourner pour les fichiers sensibles

# 4. Vérifier le .gitignore
cat .gitignore
```

## 📤 Commandes Git Finales

```bash
# 1. Ajouter tous les fichiers (sauf les ignorés)
git add .

# 2. Vérifier avant de committer
git status

# 3. Créer le commit
git commit -m "Mise à jour: système de réinitialisation BD et configuration"

# 4. Pousser vers GitHub
git push origin main
```

## 🎯 Points de Vérification

- [ ] Les fichiers ignorés ne sont pas dans le git
- [ ] Les exemples de configuration sont présents
- [ ] La documentation est à jour
- [ ] Les instructions d'installation sont claires
- [ ] Pas de données sensibles ne sont exposées

## 💡 Si Vous Avez Accidentellement Poussé des Secrets

⚠️ **C'est grave, agissez immédiatement:**

```bash
# 1. Modifier le .gitignore
# 2. Exécuter (attention: cela réécrit l'historique)
git rm -r --cached config/db.php
git commit -am "Remove sensitive files from git history"

# 3. Ou utiliser git-filter-branch (plus complet)
# Consultez: https://git-scm.com/book/en/v2/Git-Tools-Rewriting-History

# 4. Changer les mots de passe immédiatement
# Les secrets sont exposés publiquement!
```

---

## ✨ Vous Êtes Prêt Si:

✅ `.gitignore` est bien configuré  
✅ Pas de secrets dans le code  
✅ Exemple de configuration fourni  
✅ Documentation complète  
✅ `git status` ne montre pas `config/db.php`  

---

**Publiez en toute confiance! 🚀**
