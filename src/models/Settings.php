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
}
