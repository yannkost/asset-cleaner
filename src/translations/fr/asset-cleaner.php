<?php

return [
    // General
    'Asset Cleaner' => 'Asset Cleaner',
    'An error occurred.' => 'Une erreur s\'est produite.',
    'Loading...' => 'Chargement...',
    
    // View Usage
    'View Usage' => 'Voir l\'utilisation',
    'Used by Entries' => 'Utilisé par les entrées',
    'Used in Content Fields' => 'Utilisé dans les champs de contenu',
    'This asset is not used anywhere.' => 'Cet asset n\'est utilisé nulle part.',
    
    // Utility Page
    'Scan Now' => 'Scanner maintenant',
    'Select Volumes' => 'Sélectionner les volumes',
    'Select All' => 'Tout sélectionner',
    'Results' => 'Résultats',
    'Used Assets' => 'Assets utilisés',
    'Unused Assets' => 'Assets inutilisés',
    'Scanning...' => 'Analyse en cours...',
    
    // Bulk Actions
    'Bulk Actions' => 'Actions groupées',
    'Bulk Actions (All Volumes)' => 'Actions groupées (Tous les volumes)',
    'Download CSV' => 'Télécharger CSV',
    'Download ZIP' => 'Télécharger ZIP',
    'Put into Trash' => 'Mettre à la corbeille',
    'Delete Permanently' => 'Supprimer définitivement',
    
    // Table Headers
    'Title' => 'Titre',
    'Filename' => 'Nom du fichier',
    'Volume' => 'Volume',
    'Size' => 'Taille',
    'Path' => 'Chemin',
    'Date Created' => 'Date de création',
    
    // Messages
    'No assets selected.' => 'Aucun asset sélectionné.',
    'No assets found.' => 'Aucun asset trouvé.',
    'Could not create ZIP file.' => 'Impossible de créer le fichier ZIP.',
    'No volumes selected.' => 'Aucun volume sélectionné.',
    
    // ZIP Download Dialog
    'ZIP Download Options' => 'Options de téléchargement ZIP',
    'How would you like to organize the files in the ZIP?' => 'Comment souhaitez-vous organiser les fichiers dans le ZIP ?',
    'Flat (all files in root)' => 'Plat (tous les fichiers à la racine)',
    'Preserve folder structure' => 'Conserver la structure des dossiers',
    'Cancel' => 'Annuler',
    'ZIP download initiated. Large files may take several minutes to prepare.' => 'Téléchargement ZIP lancé. Les fichiers volumineux peuvent prendre plusieurs minutes.',
    'Preparing ZIP file... This may take several minutes for large files. Please wait.' => 'Préparation du fichier ZIP... Cela peut prendre plusieurs minutes pour les fichiers volumineux. Veuillez patienter.',
    
    // Trash/Delete Messages
    'Are you sure you want to move {count} assets to trash?' => 'Êtes-vous sûr de vouloir déplacer {count} assets vers la corbeille ?',
    'Moved {count} assets to trash.' => '{count} assets déplacés vers la corbeille.',
    'Permanently deleted {count} assets.' => '{count} assets supprimés définitivement.',
    'WARNING: You are about to permanently delete assets.' => 'ATTENTION : Vous êtes sur le point de supprimer définitivement des assets.',
    'This action CANNOT be undone!' => 'Cette action est IRRÉVERSIBLE !',
    'We strongly recommend downloading the unused assets as a backup before proceeding.' => 'Nous vous recommandons fortement de télécharger les assets inutilisés comme sauvegarde avant de continuer.',
    'Are you absolutely sure you want to permanently delete these assets?' => 'Êtes-vous absolument sûr de vouloir supprimer définitivement ces assets ?',
    'Final confirmation: Permanently delete assets? This CANNOT be undone!' => 'Confirmation finale : Supprimer définitivement les assets ? Cette action est IRRÉVERSIBLE !',
    
    // Volume Section
    'unused assets' => 'assets inutilisés',
    'No assets selected in this volume.' => 'Aucun asset sélectionné dans ce volume.',
    
    // Errors
    'Failed to scan volumes.' => 'Échec de l\'analyse des volumes.',
    'Failed to export CSV.' => 'Échec de l\'export CSV.',
    'Failed to create ZIP file.' => 'Échec de la création du fichier ZIP.',
    'Failed to move assets to trash.' => 'Échec du déplacement des assets vers la corbeille.',
    'Failed to delete assets.' => 'Échec de la suppression des assets.',
    'Failed to get asset usage.' => 'Échec de la récupération de l\'utilisation de l\'asset.',

    // Queue Scan
    'Scan queued...' => 'Scan en file d\'attente...',
    'Scan failed.' => 'Le scan a échoué.',
    'Scanning assets for usage' => 'Analyse de l\'utilisation des assets',
    'The queue does not appear to be running. Make sure a queue worker is active (e.g. php craft queue/listen).' => 'La file d\'attente ne semble pas être en cours d\'exécution. Assurez-vous qu\'un worker est actif (ex : php craft queue/listen).',

    // Scan Time
    'Scanned on {date}' => 'Scanné le {date}',
    'Restoring last scan...' => 'Restauration du dernier scan...',

    // Usage Dialog / Scan Options
    'Check Asset Usage' => 'Vérifier l’utilisation de la ressource',
    'Choose how usage should be evaluated for this asset.' => 'Choisissez comment l’utilisation doit être évaluée pour cette ressource.',
    'Choose the usage options you want to check, then confirm.' => 'Choisissez les options d’utilisation à vérifier, puis confirmez.',
    'Include drafts' => 'Inclure les brouillons',
    'Include revisions' => 'Inclure les révisions',
    'Count all relational references as usage' => 'Compter toutes les références relationnelles comme une utilisation',
    'Recommended for projects with plugin-defined or unknown element types that may store asset relations outside normal entry content.' => 'Recommandé pour les projets avec des types d’éléments définis par des plugins ou inconnus pouvant stocker des relations de ressources en dehors du contenu normal des entrées.',
    'Check Usage' => 'Vérifier l’utilisation',
    'Used by Relational Elements' => 'Utilisé par des éléments relationnels',
    'Other Relational Elements' => 'Autres éléments relationnels',
    'Relational element #{id}' => 'Élément relationnel #{id}',
    'Relational element' => 'Élément relationnel',
    'Include drafts in this scan' => 'Inclure les brouillons dans ce scan',
    'When enabled, assets referenced only in drafts may be treated as used.' => 'Lorsqu’elle est activée, les ressources référencées uniquement dans des brouillons peuvent être considérées comme utilisées.',
    'Include revisions in this scan' => 'Inclure les révisions dans ce scan',
    'When enabled, assets referenced only in revisions may be treated as used.' => 'Lorsqu’elle est activée, les ressources référencées uniquement dans des révisions peuvent être considérées comme utilisées.',
    'When enabled, any row in Craft’s relations table will cause an asset to be treated as used, including references created by plugin-defined or unknown element types. Disable this for a stricter scan.' => 'Lorsqu’elle est activée, toute ligne dans la table des relations de Craft entraînera qu’une ressource soit considérée comme utilisée, y compris les références créées par des types d’éléments définis par des plugins ou inconnus. Désactivez cette option pour un scan plus strict.',
];
