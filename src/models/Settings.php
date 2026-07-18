<?php

declare(strict_types=1);

namespace yann\assetcleaner\models;

use craft\base\Model;

/**
 * Asset Cleaner plugin settings.
 */
class Settings extends Model
{
    public const STORAGE_MODE_FILE = 'file';
    public const STORAGE_MODE_DATABASE = 'database';

    /**
     * @var string Storage backend for scan state.
     */
    public string $scanStorageMode = self::STORAGE_MODE_FILE;

    /**
     * @var string|null Optional custom workspace path for file-based storage.
     */
    public ?string $scanWorkspacePath = null;

    /**
     * @var bool Whether drafts should count as usage by default.
     */
    public bool $includeDraftsByDefault = false;

    /**
     * @var bool Whether revisions should count as usage by default.
     */
    public bool $includeRevisionsByDefault = false;

    /**
     * @var int Maximum number of assets loaded for relation scanning per queue
     * execution. Lower this on sites with heavy or deeply nested relations so a
     * single execution does less work and stays under the worker timeout.
     */
    public int $relationBatchSize = 2000;

    /**
     * @var int Wall-clock budget, in seconds, for the relation stage of one
     * queue execution. Once exceeded the batch stops and re-queues to continue,
     * keeping each execution safely under the queue's time-to-reserve (TTR).
     */
    public int $relationTimeBudgetSeconds = 120;

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['scanStorageMode'], 'required'],
            [['scanStorageMode'], 'in', 'range' => [
                self::STORAGE_MODE_FILE,
                self::STORAGE_MODE_DATABASE,
            ]],
            [['scanWorkspacePath'], 'string'],
            [['scanWorkspacePath'], 'default', 'value' => null],
            [['includeDraftsByDefault', 'includeRevisionsByDefault'], 'boolean'],
            [['includeDraftsByDefault', 'includeRevisionsByDefault'], 'default', 'value' => false],
            [['relationBatchSize'], 'default', 'value' => 2000],
            [['relationBatchSize'], 'integer', 'min' => 1],
            [['relationTimeBudgetSeconds'], 'default', 'value' => 120],
            [['relationTimeBudgetSeconds'], 'integer', 'min' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function fields(): array
    {
        return [
            'scanStorageMode',
            'scanWorkspacePath',
            'includeDraftsByDefault',
            'includeRevisionsByDefault',
            'relationBatchSize',
            'relationTimeBudgetSeconds',
        ];
    }

    /**
     * Whether file-backed scan storage is enabled.
     */
    public function isFileMode(): bool
    {
        return $this->scanStorageMode === self::STORAGE_MODE_FILE;
    }

    /**
     * Whether database-backed scan storage is enabled.
     */
    public function isDatabaseMode(): bool
    {
        return $this->scanStorageMode === self::STORAGE_MODE_DATABASE;
    }

    /**
     * Whether draft usage should be included by default.
     */
    public function shouldIncludeDraftsByDefault(): bool
    {
        return $this->includeDraftsByDefault;
    }

    /**
     * Whether revision usage should be included by default.
     */
    public function shouldIncludeRevisionsByDefault(): bool
    {
        return $this->includeRevisionsByDefault;
    }

    /**
     * Maximum assets loaded for relation scanning per queue execution.
     */
    public function getRelationBatchSize(): int
    {
        return max(1, $this->relationBatchSize);
    }

    /**
     * Wall-clock budget, in seconds, for the relation stage of one execution.
     */
    public function getRelationTimeBudgetSeconds(): int
    {
        return max(1, $this->relationTimeBudgetSeconds);
    }
}
