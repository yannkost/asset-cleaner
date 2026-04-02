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
}
