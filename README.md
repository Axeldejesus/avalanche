# Bibliothèque de Jeux - Symfony API

## Description

### Français
Ce projet est une API développée avec Symfony pour gérer une bibliothèque de jeux vidéo. Il permet aux utilisateurs de rechercher des jeux, de les noter, de les commenter, et de les organiser dans des dossiers tels que "Jeux terminés" ou "Jeux à faire" etc... Ce projet est actuellement en cours de développement (Work in Progress).

### English
This project is an API developed with Symfony to manage a video game library. It allows users to search for games, rate them, comment on them, and organize them into folders such as "Completed Games" or "Games to Play" etc... This project is currently a work in progress.

## Fonctionnalités / Features

- **Recherche de jeux / Game Search**: Recherchez des jeux vidéo par nom ou explorez les jeux à venir.
- **Détails des jeux / Game Details**: Affichez des informations détaillées sur chaque jeu, y compris des résumés et des illustrations.
- **Organisation des jeux / Game Organization**: Classez les jeux dans des dossiers personnalisés.
- **Notations et commentaires / Ratings and Comments**: Notez et commentez les jeux pour partager votre avis.

## Installation

### Prérequis / Prerequisites

- PHP 8.1 ou supérieur / PHP 8.1 or higher
- Composer
- Symfony CLI
- Un compte IGDB pour l'API / An IGDB account for the API

### Étapes d'installation / Installation Steps

1. **Clonez le dépôt / Clone the repository**:
   ```bash
   git clone
   cd avalanche
   ```

2. **Installez les dépendances / Install dependencies**:
   ```bash
   composer install
   ```

3. **Configurez les variables d'environnement / Configure environment variables**:
   - Créez un fichier `.env.local` et ajoutez vos identifiants IGDB.
   ```dotenv
   IGDB_CLIENT_ID=your_client_id
   IGDB_SECRET_KEY=your_secret_key
   ```

4. **Lancez le serveur Symfony / Start the Symfony server**:
   ```bash
   symfony server:start
   ```

5. **Accédez à l'application / Access the application**:
   - Ouvrez votre navigateur et allez à `http://localhost:8000`.

## Utilisation / Usage

- **Rechercher des jeux / Search for games**: Utilisez l'interface pour rechercher des jeux par nom.
- **Voir les détails / View details**: Cliquez sur un jeu pour voir ses détails.
- **Organiser les jeux / Organize games**: Ajoutez des jeux à vos dossiers personnalisés.
- **Noter et commenter / Rate and comment**: Partagez vos avis sur les jeux.

## Statut du projet / Project Status

### Français
Ce projet est en cours de développement. De nouvelles fonctionnalités et améliorations sont prévues.

### English
This project is a work in progress. New features and improvements are planned.
