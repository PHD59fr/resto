# Critiques Gastronomiques de PH

## 📚 Présentation

Ce site est un **guide gastronomique personnel**, répertoriant des critiques honnêtes et sans filtre sur différents restaurants de la région.  
Chaque critique est soigneusement détaillée avec :
- une description générale,
- une note sur la cuisine, le service et l’ambiance,
- des photos de plats et d’ambiance,
- ainsi qu’une date de visite.

Le site propose deux modes de navigation :
- **Liste** : vue d’ensemble paginée de tous les restaurants évalués.
- **Fiche individuelle** : détail complet pour chaque restaurant sélectionné.

---

## ❓ Pourquoi ce site ?

Sur Google, il est **trop facile pour un restaurant de faire supprimer un commentaire négatif** :  
**un simple email à Google** peut suffire pour faire disparaître une critique, même si elle est honnête et justifiée.

Avec **Critiques Gastronomiques de PH**, l’objectif est de :
- **Publier un avis qui ne peuvent pas être censurés** par des plateformes extérieures,
- **Garantir la liberté d’expression** pour partager de vraies expériences,
- **Informer les futurs clients** de manière authentique, sans influence ni pression.

De plus, **en rendant ce projet open source sur GitHub**, chacun peut :
- **Créer facilement son propre site de critiques**,
- **Partager ses avis sans risque de censure**,
- **Contribuer à une information libre et indépendante**.

Ici, **les critiques restent** – qu’elles soient positives ou négatives.

---

## ⚙️ Fonctionnement technique

- Les données de chaque restaurant sont stockées dans un dossier `/restaurant/`, sous forme de fichier `info.json`.
- Le site est entièrement **statique côté client** : aucun CMS ou base de données côté serveur.
- PHP est utilisé pour :
  - scanner les restaurants disponibles
  - gérer la pagination
  - afficher dynamiquement les informations selon l'URL
  - afficher les images locales (cover et autres images)

---

## 🏗️ Installation locale

1. Cloner ce dépôt :
   ```bash
   git clone https://github.com/phd59fr/resto.git
   ```
2. Placer le projet sur un serveur local supportant PHP (ex : Apache, Nginx, ou simplement `php -S localhost:8000`).
3. Visiter `http://localhost:8000` dans votre navigateur.
4. Ajouter vos critiques en créant des sous-dossiers dans `/restaurant/` avec un fichier `info.json` et des images.

---

## 📂 Structure d'un restaurant

Exemple d'un fichier `restaurant/nom-du-restaurant/info.json` :

```json
{
  "slug": "nom-du-restaurant",
  "name": "Nom du Restaurant",
  "category": "Cuisine Française",
  "description": "Un lieu cosy offrant des plats traditionnels revisités.\netc, etc, etc...",
  "visitDate": "2025-04-01",
  "ratings": {
    "cuisine": 5,
    "service": 5,
    "ambiance": 5
  },
  "externalImageTitle": "https://exemple.com/image-de-couverture.jpg",
  "imageTitle": "cover.png",
  "externalImages": [
    "https://exemple.com/photo1.jpg",
    "https://exemple.com/photo2.jpg"
  ]
}
```

---

## 🧑‍🍳 Auteur

- **PH** — Passionné de gastronomie

---
