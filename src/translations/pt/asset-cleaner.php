<?php

return [
    // General
    'Asset Cleaner' => 'Asset Cleaner',
    'An error occurred.' => 'Ocorreu um erro.',
    'Loading...' => 'Carregando...',
    
    // View Usage
    'View Usage' => 'Ver uso',
    'Used by Entries' => 'Usado por entradas',
    'Used in Content Fields' => 'Usado em campos de conteúdo',
    'This asset is not used anywhere.' => 'Este asset não é usado em nenhum lugar.',
    
    // Utility Page
    'Scan Now' => 'Escanear agora',
    'Select Volumes' => 'Selecionar volumes',
    'Select All' => 'Selecionar tudo',
    'Results' => 'Resultados',
    'Used Assets' => 'Assets usados',
    'Unused Assets' => 'Assets não usados',
    'Scanning...' => 'Escaneando...',
    
    // Bulk Actions
    'Bulk Actions' => 'Ações em massa',
    'Bulk Actions (All Volumes)' => 'Ações em massa (Todos os volumes)',
    'Download CSV' => 'Baixar CSV',
    'Download ZIP' => 'Baixar ZIP',
    'Put into Trash' => 'Mover para lixeira',
    'Delete Permanently' => 'Excluir permanentemente',
    
    // Table Headers
    'Title' => 'Título',
    'Filename' => 'Nome do arquivo',
    'Volume' => 'Volume',
    'Size' => 'Tamanho',
    'Path' => 'Caminho',
    'Date Created' => 'Data de criação',
    
    // Messages
    'No assets selected.' => 'Nenhum asset selecionado.',
    'No assets found.' => 'Nenhum asset encontrado.',
    'Could not create ZIP file.' => 'Não foi possível criar o arquivo ZIP.',
    'No volumes selected.' => 'Nenhum volume selecionado.',
    
    // ZIP Download Dialog
    'ZIP Download Options' => 'Opções de download ZIP',
    'How would you like to organize the files in the ZIP?' => 'Como você gostaria de organizar os arquivos no ZIP?',
    'Flat (all files in root)' => 'Plano (todos os arquivos na raiz)',
    'Preserve folder structure' => 'Preservar estrutura de pastas',
    'Cancel' => 'Cancelar',
    'ZIP download initiated. Large files may take several minutes to prepare.' => 'Download ZIP iniciado. Arquivos grandes podem levar vários minutos.',
    'Preparing ZIP file... This may take several minutes for large files. Please wait.' => 'Preparando arquivo ZIP... Isso pode levar vários minutos para arquivos grandes. Por favor aguarde.',
    
    // Trash/Delete Messages
    'Are you sure you want to move {count} assets to trash?' => 'Tem certeza de que deseja mover {count} assets para a lixeira?',
    'Moved {count} assets to trash.' => '{count} assets movidos para a lixeira.',
    'Permanently deleted {count} assets.' => '{count} assets excluídos permanentemente.',
    'WARNING: You are about to permanently delete assets.' => 'AVISO: Você está prestes a excluir assets permanentemente.',
    'This action CANNOT be undone!' => 'Esta ação NÃO pode ser desfeita!',
    'We strongly recommend downloading the unused assets as a backup before proceeding.' => 'Recomendamos fortemente baixar os assets não usados como backup antes de prosseguir.',
    'Are you absolutely sure you want to permanently delete these assets?' => 'Você tem certeza absoluta de que deseja excluir permanentemente estes assets?',
    'Final confirmation: Permanently delete assets? This CANNOT be undone!' => 'Confirmação final: Excluir assets permanentemente? Isso NÃO pode ser desfeito!',
    
    // Volume Section
    'unused assets' => 'assets não usados',
    'No assets selected in this volume.' => 'Nenhum asset selecionado neste volume.',
    
    // Errors
    'Failed to scan volumes.' => 'Falha ao escanear volumes.',
    'Failed to export CSV.' => 'Falha ao exportar CSV.',
    'Failed to create ZIP file.' => 'Falha ao criar arquivo ZIP.',
    'Failed to move assets to trash.' => 'Falha ao mover assets para a lixeira.',
    'Failed to delete assets.' => 'Falha ao excluir assets.',
    'Failed to get asset usage.' => 'Falha ao obter uso do asset.',

    // Queue Scan
    'Scan queued...' => 'Verificação em fila...',
    'Scan failed.' => 'A verificação falhou.',
    'Scanning assets for usage' => 'Verificando uso dos assets',
    'The queue does not appear to be running. Make sure a queue worker is active (e.g. php craft queue/listen).' => 'A fila não parece estar em execução. Certifique-se de que um worker de fila esteja ativo (ex: php craft queue/listen).',

    // Scan Time
    'Scanned on {date}' => 'Escaneado em {date}',
    'Restoring last scan...' => 'Restaurando último scan...',

    // Usage Dialog / Scan Options
    'Check Asset Usage' => 'Verificar uso do recurso',
    'Choose how usage should be evaluated for this asset.' => 'Escolha como o uso deve ser avaliado para este recurso.',
    'Choose the usage options you want to check, then confirm.' => 'Escolha as opções de uso que deseja verificar e depois confirme.',
    'Include drafts' => 'Incluir rascunhos',
    'Include revisions' => 'Incluir revisões',
    'Count all relational references as usage' => 'Contar todas as referências relacionais como uso',
    'Recommended for projects with plugin-defined or unknown element types that may store asset relations outside normal entry content.' => 'Recomendado para projetos com tipos de elementos definidos por plugins ou desconhecidos que possam armazenar relações de recursos fora do conteúdo normal das entradas.',
    'Check Usage' => 'Verificar uso',
    'Used by Relational Elements' => 'Usado por elementos relacionais',
    'Other Relational Elements' => 'Outros elementos relacionais',
    'Relational element #{id}' => 'Elemento relacional #{id}',
    'Relational element' => 'Elemento relacional',
    'Include drafts in this scan' => 'Incluir rascunhos nesta verificação',
    'When enabled, assets referenced only in drafts may be treated as used.' => 'Quando ativado, recursos referenciados apenas em rascunhos podem ser tratados como usados.',
    'Include revisions in this scan' => 'Incluir revisões nesta verificação',
    'When enabled, assets referenced only in revisions may be treated as used.' => 'Quando ativado, recursos referenciados apenas em revisões podem ser tratados como usados.',
    'When enabled, any row in Craft’s relations table will cause an asset to be treated as used, including references created by plugin-defined or unknown element types. Disable this for a stricter scan.' => 'Quando ativado, qualquer linha na tabela de relações do Craft fará com que um recurso seja tratado como usado, incluindo referências criadas por tipos de elementos definidos por plugins ou desconhecidos. Desative isso para uma verificação mais rigorosa.',

    // Settings - Scan performance
    'Scan performance' => 'Desempenho do escaneamento',
    'Relation batch size' => 'Tamanho do lote de relações',
    'Maximum number of assets loaded for relation scanning per queue execution. Lower this (e.g. to 500) on sites with heavy or deeply nested relations if scan jobs time out. You can also override this with `relationBatchSize` in `config/asset-cleaner.php` or the `ASSET_CLEANER_RELATION_BATCH_SIZE` environment variable.' => 'Número máximo de assets carregados para o escaneamento de relações por execução da fila. Reduza este valor (por ex. para 500) em sites com relações numerosas ou profundamente aninhadas se as tarefas de escaneamento excederem o tempo limite. Também é possível sobrescrevê-lo com `relationBatchSize` em `config/asset-cleaner.php` ou com a variável de ambiente `ASSET_CLEANER_RELATION_BATCH_SIZE`.',
    'Relation time budget (seconds)' => 'Orçamento de tempo das relações (segundos)',
    'Wall-clock budget for the relation stage of a single queue execution. Once exceeded, the job stops and re-queues to continue, keeping each execution safely under the queue’s time-to-reserve (TTR, 300s by default). Keep this comfortably below your TTR. You can also override this with `relationTimeBudgetSeconds` in `config/asset-cleaner.php` or the `ASSET_CLEANER_RELATION_TIME_BUDGET` environment variable.' => 'Orçamento de tempo real para a fase de relações de uma única execução da fila. Ao ser excedido, a tarefa para e volta para a fila para continuar, mantendo cada execução com segurança abaixo do time-to-reserve (TTR, 300 s por padrão) da fila. Mantenha este valor bem abaixo do seu TTR. Também é possível sobrescrevê-lo com `relationTimeBudgetSeconds` em `config/asset-cleaner.php` ou com a variável de ambiente `ASSET_CLEANER_RELATION_TIME_BUDGET`.',

    // Settings page, queue job descriptions, scan stage labels, usage labels
    'Scan storage mode' => 'Modo de armazenamento dos escaneamentos',
    'File-based' => 'Baseado em arquivos',
    'Database-based' => 'Baseado em banco de dados',
    'Choose how Asset Cleaner stores transient scan state. File-based storage is the default and works well when web and queue workers share a filesystem. Database-based storage is better suited for containerized or cloud-style environments where shared filesystem access is not guaranteed.' => 'Determina como o Asset Cleaner armazena o estado transitório dos escaneamentos. O armazenamento baseado em arquivos é o padrão e funciona bem quando os workers web e de fila compartilham um sistema de arquivos. O armazenamento em banco de dados é mais adequado para ambientes em contêineres ou em nuvem onde o acesso a um sistema de arquivos compartilhado não é garantido.',
    'File-based scan workspace path' => 'Caminho do espaço de trabalho de escaneamento baseado em arquivos',
    'Optional. Only used when scan storage mode is set to File-based. Defaults to `@storage/asset-cleaner`. You can also override this with `scanWorkspacePath` in `config/asset-cleaner.php` or the `ASSET_CLEANER_SCAN_PATH` environment variable.' => 'Opcional. Usado apenas quando o modo de armazenamento está definido como "Baseado em arquivos". O padrão é `@storage/asset-cleaner`. Também é possível sobrescrevê-lo com `scanWorkspacePath` em `config/asset-cleaner.php` ou com a variável de ambiente `ASSET_CLEANER_SCAN_PATH`.',
    'Include drafts by default' => 'Incluir rascunhos por padrão',
    'When enabled, assets referenced only in drafts may be treated as used during scans. This value acts as the default for new scans and can be overridden per scan from the utility page. You can also override this with `includeDraftsByDefault` in `config/asset-cleaner.php` or the `ASSET_CLEANER_INCLUDE_DRAFTS` environment variable.' => 'Quando ativado, assets referenciados apenas em rascunhos podem ser considerados usados durante os escaneamentos. Este valor atua como padrão para novos escaneamentos e pode ser sobrescrito por escaneamento na página de utilitários. Também é possível sobrescrevê-lo com `includeDraftsByDefault` em `config/asset-cleaner.php` ou com a variável de ambiente `ASSET_CLEANER_INCLUDE_DRAFTS`.',
    'Include revisions by default' => 'Incluir revisões por padrão',
    'When enabled, assets referenced only in revisions may be treated as used during scans. This value acts as the default for new scans and can be overridden per scan from the utility page. You can also override this with `includeRevisionsByDefault` in `config/asset-cleaner.php` or the `ASSET_CLEANER_INCLUDE_REVISIONS` environment variable.' => 'Quando ativado, assets referenciados apenas em revisões podem ser considerados usados durante os escaneamentos. Este valor atua como padrão para novos escaneamentos e pode ser sobrescrito por escaneamento na página de utilitários. Também é possível sobrescrevê-lo com `includeRevisionsByDefault` em `config/asset-cleaner.php` ou com a variável de ambiente `ASSET_CLEANER_INCLUDE_REVISIONS`.',
    'Notes:' => 'Observações:',
    'Only the latest scan is retained for restore/export workflows.' => 'Apenas o escaneamento mais recente é mantido para fluxos de restauração/exportação.',
    'When using File-based storage in multi-container setups, make sure the configured workspace path is shared between web and queue workers.' => 'Ao usar armazenamento baseado em arquivos em configurações multi-contêiner, garanta que o caminho do espaço de trabalho configurado seja compartilhado entre os workers web e de fila.',
    'Config file values override these control panel settings.' => 'Os valores do arquivo de configuração têm prioridade sobre estas configurações do painel de controle.',
    'Draft and revision handling can be configured globally here and overridden per scan from the utility page.' => 'O tratamento de rascunhos e revisões pode ser configurado globalmente aqui e sobrescrito por escaneamento na página de utilitários.',
    'Preparing asset scan' => 'Preparando o escaneamento de assets',
    'Scanning asset relations' => 'Escaneando relações de assets',
    'Scanning content for asset references' => 'Escaneando conteúdo em busca de referências a assets',
    'Finalizing asset scan results' => 'Finalizando os resultados do escaneamento de assets',
    'Preparing asset snapshot...' => 'Preparando snapshot de assets...',
    'Scanning relations...' => 'Escaneando relações...',
    'Scanning content...' => 'Escaneando conteúdo...',
    'Finalizing results...' => 'Finalizando resultados...',
    'User profile picture' => 'Foto de perfil do usuário',
    'User #{id}' => 'Usuário #{id}',
    'Relational source #{id}' => 'Fonte relacional #{id}',
    'Used by relational element #{id}' => 'Usado pelo elemento relacional #{id}',

    // Bulk delete confirmations and scan status messages
    'Are you sure you want to permanently delete {count} assets? This action CANNOT be undone! Download a backup (CSV or ZIP) before proceeding.' => 'Tem certeza de que deseja excluir permanentemente {count} assets? Esta ação NÃO pode ser desfeita! Baixe um backup (CSV ou ZIP) antes de continuar.',
    'Before permanently deleting' => 'Antes de excluir permanentemente',
    'Bulk Actions - All Selected Volumes' => 'Ações em massa - Todos os volumes selecionados',
    'Final confirmation: Permanently delete {count} assets? This CANNOT be undone!' => 'Confirmação final: excluir permanentemente {count} assets? Isto NÃO pode ser desfeito!',
    'Lost contact while polling scan progress. The scan may still be running.' => 'Conexão perdida ao consultar o progresso do escaneamento. O escaneamento ainda pode estar em execução.',
    'No unused assets found.' => 'Nenhum asset sem uso encontrado.',
    'Scan older than 24h — results may be outdated' => 'Escaneamento com mais de 24 h — os resultados podem estar desatualizados',
    'We recommend downloading a ZIP backup of the assets you plan to remove first, or using "Put into Trash" as a safer alternative. Permanent deletions cannot be undone.' => 'Recomendamos baixar primeiro um backup ZIP dos assets que você planeja remover, ou usar "Mover para a lixeira" como alternativa mais segura. Exclusões permanentes não podem ser desfeitas.',
    '{count} unused assets — {size}' => '{count} assets sem uso — {size}',
];
