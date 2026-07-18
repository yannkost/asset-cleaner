<?php

return [
    // General
    'Asset Cleaner' => 'Asset Cleaner',
    'An error occurred.' => 'Se produjo un error.',
    'Loading...' => 'Cargando...',
    
    // View Usage
    'View Usage' => 'Ver uso',
    'Used by Entries' => 'Usado por entradas',
    'Used in Content Fields' => 'Usado en campos de contenido',
    'This asset is not used anywhere.' => 'Este asset no se usa en ningún lugar.',
    
    // Utility Page
    'Scan Now' => 'Escanear ahora',
    'Select Volumes' => 'Seleccionar volúmenes',
    'Select All' => 'Seleccionar todo',
    'Results' => 'Resultados',
    'Used Assets' => 'Assets usados',
    'Unused Assets' => 'Assets no usados',
    'Scanning...' => 'Escaneando...',
    
    // Bulk Actions
    'Bulk Actions' => 'Acciones masivas',
    'Bulk Actions (All Volumes)' => 'Acciones masivas (Todos los volúmenes)',
    'Download CSV' => 'Descargar CSV',
    'Download ZIP' => 'Descargar ZIP',
    'Put into Trash' => 'Mover a la papelera',
    'Delete Permanently' => 'Eliminar permanentemente',
    
    // Table Headers
    'Title' => 'Título',
    'Filename' => 'Nombre de archivo',
    'Volume' => 'Volumen',
    'Size' => 'Tamaño',
    'Path' => 'Ruta',
    'Date Created' => 'Fecha de creación',
    
    // Messages
    'No assets selected.' => 'No hay assets seleccionados.',
    'No assets found.' => 'No se encontraron assets.',
    'Could not create ZIP file.' => 'No se pudo crear el archivo ZIP.',
    'No volumes selected.' => 'No hay volúmenes seleccionados.',
    
    // ZIP Download Dialog
    'ZIP Download Options' => 'Opciones de descarga ZIP',
    'How would you like to organize the files in the ZIP?' => '¿Cómo desea organizar los archivos en el ZIP?',
    'Flat (all files in root)' => 'Plano (todos los archivos en la raíz)',
    'Preserve folder structure' => 'Preservar estructura de carpetas',
    'Cancel' => 'Cancelar',
    'ZIP download initiated. Large files may take several minutes to prepare.' => 'Descarga ZIP iniciada. Los archivos grandes pueden tardar varios minutos.',
    'Preparing ZIP file... This may take several minutes for large files. Please wait.' => 'Preparando archivo ZIP... Esto puede tardar varios minutos para archivos grandes. Por favor espere.',
    
    // Trash/Delete Messages
    'Are you sure you want to move {count} assets to trash?' => '¿Está seguro de que desea mover {count} assets a la papelera?',
    'Moved {count} assets to trash.' => '{count} assets movidos a la papelera.',
    'Permanently deleted {count} assets.' => '{count} assets eliminados permanentemente.',
    'WARNING: You are about to permanently delete assets.' => 'ADVERTENCIA: Está a punto de eliminar assets permanentemente.',
    'This action CANNOT be undone!' => '¡Esta acción NO se puede deshacer!',
    'We strongly recommend downloading the unused assets as a backup before proceeding.' => 'Recomendamos encarecidamente descargar los assets no usados como respaldo antes de continuar.',
    'Are you absolutely sure you want to permanently delete these assets?' => '¿Está absolutamente seguro de que desea eliminar permanentemente estos assets?',
    'Final confirmation: Permanently delete assets? This CANNOT be undone!' => 'Confirmación final: ¿Eliminar assets permanentemente? ¡Esto NO se puede deshacer!',
    
    // Volume Section
    'unused assets' => 'assets no usados',
    'No assets selected in this volume.' => 'No hay assets seleccionados en este volumen.',
    
    // Errors
    'Failed to scan volumes.' => 'Error al escanear volúmenes.',
    'Failed to export CSV.' => 'Error al exportar CSV.',
    'Failed to create ZIP file.' => 'Error al crear archivo ZIP.',
    'Failed to move assets to trash.' => 'Error al mover assets a la papelera.',
    'Failed to delete assets.' => 'Error al eliminar assets.',
    'Failed to get asset usage.' => 'Error al obtener uso del asset.',

    // Queue Scan
    'Scan queued...' => 'Escaneo en cola...',
    'Scan failed.' => 'El escaneo falló.',
    'Scanning assets for usage' => 'Escaneando assets en uso',
    'The queue does not appear to be running. Make sure a queue worker is active (e.g. php craft queue/listen).' => 'La cola no parece estar en ejecución. Asegúrese de que un worker de cola esté activo (ej: php craft queue/listen).',

    // Scan Time
    'Scanned on {date}' => 'Escaneado el {date}',
    'Restoring last scan...' => 'Restaurando último escaneo...',

    // Usage Dialog / Scan Options
    'Check Asset Usage' => 'Comprobar uso del recurso',
    'Choose how usage should be evaluated for this asset.' => 'Elige cómo debe evaluarse el uso de este recurso.',
    'Choose the usage options you want to check, then confirm.' => 'Elige las opciones de uso que deseas comprobar y luego confirma.',
    'Include drafts' => 'Incluir borradores',
    'Include revisions' => 'Incluir revisiones',
    'Count all relational references as usage' => 'Contar todas las referencias relacionales como uso',
    'Recommended for projects with plugin-defined or unknown element types that may store asset relations outside normal entry content.' => 'Recomendado para proyectos con tipos de elementos definidos por plugins o desconocidos que puedan almacenar relaciones de recursos fuera del contenido normal de entradas.',
    'Check Usage' => 'Comprobar uso',
    'Used by Relational Elements' => 'Usado por elementos relacionales',
    'Other Relational Elements' => 'Otros elementos relacionales',
    'Relational element #{id}' => 'Elemento relacional #{id}',
    'Relational element' => 'Elemento relacional',
    'Include drafts in this scan' => 'Incluir borradores en este escaneo',
    'When enabled, assets referenced only in drafts may be treated as used.' => 'Cuando está activado, los recursos referenciados solo en borradores pueden tratarse como usados.',
    'Include revisions in this scan' => 'Incluir revisiones en este escaneo',
    'When enabled, assets referenced only in revisions may be treated as used.' => 'Cuando está activado, los recursos referenciados solo en revisiones pueden tratarse como usados.',
    'When enabled, any row in Craft’s relations table will cause an asset to be treated as used, including references created by plugin-defined or unknown element types. Disable this for a stricter scan.' => 'Cuando está activado, cualquier fila en la tabla de relaciones de Craft hará que un recurso se trate como usado, incluidas las referencias creadas por tipos de elementos definidos por plugins o desconocidos. Desactívalo para un escaneo más estricto.',

    // Settings - Scan performance
    'Scan performance' => 'Rendimiento del escaneo',
    'Relation batch size' => 'Tamaño del lote de relaciones',
    'Maximum number of assets loaded for relation scanning per queue execution. Lower this (e.g. to 500) on sites with heavy or deeply nested relations if scan jobs time out. You can also override this with `relationBatchSize` in `config/asset-cleaner.php` or the `ASSET_CLEANER_RELATION_BATCH_SIZE` environment variable.' => 'Número máximo de assets cargados para el escaneo de relaciones por cada ejecución de la cola. Reduce este valor (p. ej. a 500) en sitios con relaciones numerosas o profundamente anidadas si las tareas de escaneo agotan el tiempo de espera. También puedes sobrescribirlo con `relationBatchSize` en `config/asset-cleaner.php` o la variable de entorno `ASSET_CLEANER_RELATION_BATCH_SIZE`.',
    'Relation time budget (seconds)' => 'Presupuesto de tiempo de relaciones (segundos)',
    'Wall-clock budget for the relation stage of a single queue execution. Once exceeded, the job stops and re-queues to continue, keeping each execution safely under the queue’s time-to-reserve (TTR, 300s by default). Keep this comfortably below your TTR. You can also override this with `relationTimeBudgetSeconds` in `config/asset-cleaner.php` or the `ASSET_CLEANER_RELATION_TIME_BUDGET` environment variable.' => 'Presupuesto de tiempo real para la fase de relaciones de una sola ejecución de la cola. Una vez superado, la tarea se detiene y vuelve a encolarse para continuar, manteniendo cada ejecución con seguridad por debajo del time-to-reserve (TTR, 300 s por defecto) de la cola. Mantén este valor claramente por debajo de tu TTR. También puedes sobrescribirlo con `relationTimeBudgetSeconds` en `config/asset-cleaner.php` o la variable de entorno `ASSET_CLEANER_RELATION_TIME_BUDGET`.',

    // Settings page, queue job descriptions, scan stage labels, usage labels
    'Scan storage mode' => 'Modo de almacenamiento de escaneos',
    'File-based' => 'Basado en archivos',
    'Database-based' => 'Basado en base de datos',
    'Choose how Asset Cleaner stores transient scan state. File-based storage is the default and works well when web and queue workers share a filesystem. Database-based storage is better suited for containerized or cloud-style environments where shared filesystem access is not guaranteed.' => 'Determina cómo Asset Cleaner almacena el estado transitorio de los escaneos. El almacenamiento basado en archivos es el predeterminado y funciona bien cuando los workers web y de cola comparten un sistema de archivos. El almacenamiento en base de datos es más adecuado para entornos en contenedores o en la nube donde el acceso a un sistema de archivos compartido no está garantizado.',
    'File-based scan workspace path' => 'Ruta del espacio de trabajo de escaneo basado en archivos',
    'Optional. Only used when scan storage mode is set to File-based. Defaults to `@storage/asset-cleaner`. You can also override this with `scanWorkspacePath` in `config/asset-cleaner.php` or the `ASSET_CLEANER_SCAN_PATH` environment variable.' => 'Opcional. Solo se usa cuando el modo de almacenamiento es "Basado en archivos". Por defecto es `@storage/asset-cleaner`. También puedes sobrescribirlo con `scanWorkspacePath` en `config/asset-cleaner.php` o la variable de entorno `ASSET_CLEANER_SCAN_PATH`.',
    'Include drafts by default' => 'Incluir borradores por defecto',
    'When enabled, assets referenced only in drafts may be treated as used during scans. This value acts as the default for new scans and can be overridden per scan from the utility page. You can also override this with `includeDraftsByDefault` in `config/asset-cleaner.php` or the `ASSET_CLEANER_INCLUDE_DRAFTS` environment variable.' => 'Cuando está activado, los assets referenciados solo en borradores pueden considerarse usados durante los escaneos. Este valor actúa como predeterminado para nuevos escaneos y puede sobrescribirse por escaneo desde la página de utilidades. También puedes sobrescribirlo con `includeDraftsByDefault` en `config/asset-cleaner.php` o la variable de entorno `ASSET_CLEANER_INCLUDE_DRAFTS`.',
    'Include revisions by default' => 'Incluir revisiones por defecto',
    'When enabled, assets referenced only in revisions may be treated as used during scans. This value acts as the default for new scans and can be overridden per scan from the utility page. You can also override this with `includeRevisionsByDefault` in `config/asset-cleaner.php` or the `ASSET_CLEANER_INCLUDE_REVISIONS` environment variable.' => 'Cuando está activado, los assets referenciados solo en revisiones pueden considerarse usados durante los escaneos. Este valor actúa como predeterminado para nuevos escaneos y puede sobrescribirse por escaneo desde la página de utilidades. También puedes sobrescribirlo con `includeRevisionsByDefault` en `config/asset-cleaner.php` o la variable de entorno `ASSET_CLEANER_INCLUDE_REVISIONS`.',
    'Notes:' => 'Notas:',
    'Only the latest scan is retained for restore/export workflows.' => 'Solo se conserva el último escaneo para los flujos de restauración/exportación.',
    'When using File-based storage in multi-container setups, make sure the configured workspace path is shared between web and queue workers.' => 'Si usas almacenamiento basado en archivos en configuraciones multi-contenedor, asegúrate de que la ruta del espacio de trabajo configurada sea compartida entre los workers web y de cola.',
    'Config file values override these control panel settings.' => 'Los valores del archivo de configuración tienen prioridad sobre estos ajustes del panel de control.',
    'Draft and revision handling can be configured globally here and overridden per scan from the utility page.' => 'El tratamiento de borradores y revisiones puede configurarse globalmente aquí y sobrescribirse por escaneo desde la página de utilidades.',
    'Preparing asset scan' => 'Preparando el escaneo de assets',
    'Scanning asset relations' => 'Escaneando relaciones de assets',
    'Scanning content for asset references' => 'Escaneando contenido en busca de referencias a assets',
    'Finalizing asset scan results' => 'Finalizando los resultados del escaneo de assets',
    'Preparing asset snapshot...' => 'Preparando instantánea de assets...',
    'Scanning relations...' => 'Escaneando relaciones...',
    'Scanning content...' => 'Escaneando contenido...',
    'Finalizing results...' => 'Finalizando resultados...',
    'User profile picture' => 'Foto de perfil de usuario',
    'User #{id}' => 'Usuario #{id}',
    'Relational source #{id}' => 'Fuente relacional #{id}',
    'Used by relational element #{id}' => 'Usado por el elemento relacional #{id}',

    // Bulk delete confirmations and scan status messages
    'Are you sure you want to permanently delete {count} assets? This action CANNOT be undone! Download a backup (CSV or ZIP) before proceeding.' => '¿Seguro que quieres eliminar permanentemente {count} assets? ¡Esta acción NO se puede deshacer! Descarga una copia de seguridad (CSV o ZIP) antes de continuar.',
    'Before permanently deleting' => 'Antes de eliminar permanentemente',
    'Bulk Actions - All Selected Volumes' => 'Acciones masivas - Todos los volúmenes seleccionados',
    'Final confirmation: Permanently delete {count} assets? This CANNOT be undone!' => 'Confirmación final: ¿eliminar permanentemente {count} assets? ¡Esto NO se puede deshacer!',
    'Lost contact while polling scan progress. The scan may still be running.' => 'Se perdió la conexión al consultar el progreso del escaneo. Es posible que el escaneo siga en curso.',
    'No unused assets found.' => 'No se encontraron assets sin usar.',
    'Scan older than 24h — results may be outdated' => 'Escaneo de hace más de 24 h — los resultados pueden estar desactualizados',
    'We recommend downloading a ZIP backup of the assets you plan to remove first, or using "Put into Trash" as a safer alternative. Permanent deletions cannot be undone.' => 'Recomendamos descargar primero una copia ZIP de los assets que planeas eliminar, o usar "Mover a la papelera" como alternativa más segura. Las eliminaciones permanentes no se pueden deshacer.',
    '{count} unused assets — {size}' => '{count} assets sin usar — {size}',
];
