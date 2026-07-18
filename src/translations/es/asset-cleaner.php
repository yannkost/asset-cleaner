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
];
