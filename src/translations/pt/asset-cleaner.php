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
];
