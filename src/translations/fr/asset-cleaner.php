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

    // Settings - Scan performance
    'Scan performance' => 'Performance du scan',
    'Relation batch size' => 'Taille des lots de relations',
    'Maximum number of assets loaded for relation scanning per queue execution. Lower this (e.g. to 500) on sites with heavy or deeply nested relations if scan jobs time out. You can also override this with `relationBatchSize` in `config/asset-cleaner.php` or the `ASSET_CLEANER_RELATION_BATCH_SIZE` environment variable.' => 'Nombre maximal d\'assets chargés pour l\'analyse des relations par exécution de file d\'attente. Réduisez cette valeur (p. ex. à 500) sur les sites avec des relations nombreuses ou profondément imbriquées si les tâches de scan expirent. Vous pouvez aussi la remplacer avec `relationBatchSize` dans `config/asset-cleaner.php` ou la variable d\'environnement `ASSET_CLEANER_RELATION_BATCH_SIZE`.',
    'Relation time budget (seconds)' => 'Budget de temps des relations (secondes)',
    'Wall-clock budget for the relation stage of a single queue execution. Once exceeded, the job stops and re-queues to continue, keeping each execution safely under the queue’s time-to-reserve (TTR, 300s by default). Keep this comfortably below your TTR. You can also override this with `relationTimeBudgetSeconds` in `config/asset-cleaner.php` or the `ASSET_CLEANER_RELATION_TIME_BUDGET` environment variable.' => 'Budget de temps réel pour l\'étape des relations d\'une seule exécution de file d\'attente. Une fois dépassé, la tâche s\'arrête et se remet en file pour continuer, ce qui maintient chaque exécution sous le time-to-reserve (TTR, 300 s par défaut) de la file d\'attente. Gardez cette valeur nettement inférieure à votre TTR. Vous pouvez aussi la remplacer avec `relationTimeBudgetSeconds` dans `config/asset-cleaner.php` ou la variable d\'environnement `ASSET_CLEANER_RELATION_TIME_BUDGET`.',

    // Settings page, queue job descriptions, scan stage labels, usage labels
    'Scan storage mode' => 'Mode de stockage des scans',
    'File-based' => 'Basé sur fichiers',
    'Database-based' => 'Basé sur base de données',
    'Choose how Asset Cleaner stores transient scan state. File-based storage is the default and works well when web and queue workers share a filesystem. Database-based storage is better suited for containerized or cloud-style environments where shared filesystem access is not guaranteed.' => 'Détermine comment Asset Cleaner stocke l\'état transitoire des scans. Le stockage basé sur fichiers est le mode par défaut et convient bien lorsque les workers web et de file d\'attente partagent un système de fichiers. Le stockage en base de données est mieux adapté aux environnements conteneurisés ou cloud où l\'accès à un système de fichiers partagé n\'est pas garanti.',
    'File-based scan workspace path' => 'Chemin de travail des scans basés sur fichiers',
    'Optional. Only used when scan storage mode is set to File-based. Defaults to `@storage/asset-cleaner`. You can also override this with `scanWorkspacePath` in `config/asset-cleaner.php` or the `ASSET_CLEANER_SCAN_PATH` environment variable.' => 'Optionnel. Utilisé uniquement lorsque le mode de stockage est « Basé sur fichiers ». Par défaut : `@storage/asset-cleaner`. Vous pouvez aussi le remplacer avec `scanWorkspacePath` dans `config/asset-cleaner.php` ou la variable d\'environnement `ASSET_CLEANER_SCAN_PATH`.',
    'Include drafts by default' => 'Inclure les brouillons par défaut',
    'When enabled, assets referenced only in drafts may be treated as used during scans. This value acts as the default for new scans and can be overridden per scan from the utility page. You can also override this with `includeDraftsByDefault` in `config/asset-cleaner.php` or the `ASSET_CLEANER_INCLUDE_DRAFTS` environment variable.' => 'Lorsque cette option est activée, les assets référencés uniquement dans des brouillons peuvent être considérés comme utilisés lors des scans. Cette valeur sert de défaut pour les nouveaux scans et peut être remplacée pour chaque scan depuis la page utilitaire. Vous pouvez aussi la remplacer avec `includeDraftsByDefault` dans `config/asset-cleaner.php` ou la variable d\'environnement `ASSET_CLEANER_INCLUDE_DRAFTS`.',
    'Include revisions by default' => 'Inclure les révisions par défaut',
    'When enabled, assets referenced only in revisions may be treated as used during scans. This value acts as the default for new scans and can be overridden per scan from the utility page. You can also override this with `includeRevisionsByDefault` in `config/asset-cleaner.php` or the `ASSET_CLEANER_INCLUDE_REVISIONS` environment variable.' => 'Lorsque cette option est activée, les assets référencés uniquement dans des révisions peuvent être considérés comme utilisés lors des scans. Cette valeur sert de défaut pour les nouveaux scans et peut être remplacée pour chaque scan depuis la page utilitaire. Vous pouvez aussi la remplacer avec `includeRevisionsByDefault` dans `config/asset-cleaner.php` ou la variable d\'environnement `ASSET_CLEANER_INCLUDE_REVISIONS`.',
    'Notes:' => 'Remarques :',
    'Only the latest scan is retained for restore/export workflows.' => 'Seul le dernier scan est conservé pour les workflows de restauration/export.',
    'When using File-based storage in multi-container setups, make sure the configured workspace path is shared between web and queue workers.' => 'En cas de stockage basé sur fichiers dans des configurations multi-conteneurs, assurez-vous que le chemin de travail configuré est partagé entre les workers web et de file d\'attente.',
    'Config file values override these control panel settings.' => 'Les valeurs du fichier de configuration remplacent ces réglages du panneau de contrôle.',
    'Draft and revision handling can be configured globally here and overridden per scan from the utility page.' => 'La gestion des brouillons et des révisions peut être configurée globalement ici et remplacée pour chaque scan depuis la page utilitaire.',
    'Preparing asset scan' => 'Préparation du scan des assets',
    'Scanning asset relations' => 'Analyse des relations des assets',
    'Scanning content for asset references' => 'Analyse du contenu à la recherche de références d\'assets',
    'Finalizing asset scan results' => 'Finalisation des résultats du scan des assets',
    'Preparing asset snapshot...' => 'Préparation de l\'instantané des assets...',
    'Scanning relations...' => 'Analyse des relations...',
    'Scanning content...' => 'Analyse du contenu...',
    'Finalizing results...' => 'Finalisation des résultats...',
    'User profile picture' => 'Photo de profil utilisateur',
    'User #{id}' => 'Utilisateur #{id}',
    'Relational source #{id}' => 'Source relationnelle #{id}',
    'Used by relational element #{id}' => 'Utilisé par l\'élément relationnel #{id}',

    // Bulk delete confirmations and scan status messages
    'Are you sure you want to permanently delete {count} assets? This action CANNOT be undone! Download a backup (CSV or ZIP) before proceeding.' => 'Voulez-vous vraiment supprimer définitivement {count} assets ? Cette action est IRRÉVERSIBLE ! Téléchargez une sauvegarde (CSV ou ZIP) avant de continuer.',
    'Before permanently deleting' => 'Avant la suppression définitive',
    'Bulk Actions - All Selected Volumes' => 'Actions groupées - Tous les volumes sélectionnés',
    'Final confirmation: Permanently delete {count} assets? This CANNOT be undone!' => 'Confirmation finale : supprimer définitivement {count} assets ? Cette action est IRRÉVERSIBLE !',
    'Lost contact while polling scan progress. The scan may still be running.' => 'Connexion perdue pendant le suivi de la progression du scan. Le scan est peut-être toujours en cours.',
    'No unused assets found.' => 'Aucun asset inutilisé trouvé.',
    'Scan older than 24h — results may be outdated' => 'Scan datant de plus de 24 h — les résultats peuvent être obsolètes',
    'We recommend downloading a ZIP backup of the assets you plan to remove first, or using "Put into Trash" as a safer alternative. Permanent deletions cannot be undone.' => 'Nous vous recommandons de d\'abord télécharger une sauvegarde ZIP des assets que vous prévoyez de supprimer, ou d\'utiliser « Mettre à la corbeille » comme alternative plus sûre. Les suppressions définitives sont irréversibles.',
    '{count} unused assets — {size}' => '{count} assets inutilisés — {size}',
];
