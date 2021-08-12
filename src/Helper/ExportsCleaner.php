<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Helper;

use EffectConnect\Marketplaces\Service\Transformer\CatalogTransformerService;
use EffectConnect\Marketplaces\Service\Transformer\OfferTransformerService;

/**
 * Class ExportCleaner
 * @package EffectConnect\Marketplaces\Helper
 */
class ExportsCleaner
{
    /**
     * Determines after how many days a export item is deleted.
     */
    public const EXPORT_EXPIRATION_DAYS     = 7;

    /**
     * Regex used for checking if it is a valid export file.
     */
    protected const EXPORT_FILENAME_REGEX   = "/^([a-z]*)_([a-z0-9]{32})_([0-9]{10,11}).xml$/";

    /**
     * Regex used for checking if it is a valid export file.
     */
    protected const EXPORT_DIRECTORIES      = [
        OfferTransformerService::CONTENT_TYPE,
        CatalogTransformerService::CONTENT_TYPE
    ];

    /**
     * The directory where the exports are situated.
     */
    protected const DIRECTORY               = __DIR__ . '/../../data/export/';

    /**
     * Clean the export directory for expired exports.
     *
     * @param int $exportExpirationDays
     */
    public static function cleanExports(int $exportExpirationDays = self::EXPORT_EXPIRATION_DAYS)
    {
        if (!file_exists(static::DIRECTORY) || !is_dir(static::DIRECTORY)) {
            return;
        }

        $timestampDifference    = $exportExpirationDays * 24 * 60 * 60;

        foreach (scandir(static::DIRECTORY) as $directory) {
            $directoryLocation  = static::DIRECTORY . $directory;

            if (!file_exists($directoryLocation) || !is_dir($directoryLocation) || !in_array($directory, static::EXPORT_DIRECTORIES)) {
                continue;
            }

            foreach (scandir($directoryLocation) as $file) {
                $fileLocation   = $directoryLocation . DIRECTORY_SEPARATOR . $file;

                if (!boolval(preg_match(static::EXPORT_FILENAME_REGEX, $file))) {
                    continue;
                }

                if (!file_exists($fileLocation) || !is_file($fileLocation)) {
                    continue;
                }

                $fileLocation   = realpath($fileLocation);
                $timestampPart  = explode('.', explode('_', $file)[2])[0];

                if (!is_numeric($timestampPart)) {
                    continue;
                }

                $expired        = intval($timestampPart) < (time() - $timestampDifference);

                if ($expired) {
                    unlink($fileLocation);
                }
            }
        }
    }
}