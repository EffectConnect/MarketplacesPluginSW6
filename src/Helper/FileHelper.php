<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Helper;

use DOMException;
use EffectConnect\Marketplaces\Exception\FileCreationFailedException;

/**
 * Class FileHelper
 * @package EffectConnect\Marketplaces\Helper
 */
class FileHelper
{
    /**
     * Export File Location Direction Type
     */
    public const DIRECTION_TYPE_EXPORT = 'export';

    /**
     * The directory where the XML needs to be generated (first string is the direction type and second string the content type).
     */
    protected const DIRECTORY_FORMAT        = __DIR__ . '/../../data/%s/%s/';

    /**
     * The filename for the generated XML (first string is the content type, second string is the sales channel ID and third string the current timestamp).
     */
    protected const FILENAME_FORMAT  = '%s_%s_%s.xml';

    /**
     * Check if the file exists, if not create one.
     *
     * @param string $directory
     * @param string $filename
     * @return string
     * @throws FileCreationFailedException
     */
    public static function guaranteeFileLocation(string $directory, string $filename): string
    {
        $fileLocation = $directory . $filename;

        if (!file_exists($directory)) {
            if (!@mkdir($directory, 0777, true)) {
                $error = error_get_last();
                throw new FileCreationFailedException($directory, $error['message'] ?? '-');
            }
        }

        if (!file_exists($fileLocation)) {
            if (!@touch($fileLocation)) {
                $error = error_get_last();
                throw new FileCreationFailedException($fileLocation, $error['message'] ?? '-');
            }
        }

        if (!is_writable($fileLocation)) {
            throw new FileCreationFailedException($fileLocation, 'File is not writable.');
        }

        return $fileLocation;
    }

    /**
     * Generate a file for a certain type and sales channel id.
     *
     * @param string $directionType
     * @param string $contentType
     * @param string $salesChannelId
     * @return string
     * @throws FileCreationFailedException
     */
    public static function generateFile(string $directionType, string $contentType, string $salesChannelId): string
    {
        $directory      = sprintf(static::DIRECTORY_FORMAT, $directionType, $contentType);
        $filename       = sprintf(static::FILENAME_FORMAT, $contentType, $salesChannelId, time());

        return static::guaranteeFileLocation($directory, $filename);
    }

    /**
     * Get an XmlHelper instance for a certain file.
     *
     * @param string $fileLocation
     * @param string $rootElement
     * @return XmlHelper
     * @throws FileCreationFailedException
     */
    public static function getXmlHelperInstance(string $fileLocation, string $rootElement): XmlHelper
    {
        try {
            return XmlHelper::startTransaction($fileLocation, $rootElement);
        } catch (DOMException $e) {
            throw new FileCreationFailedException($fileLocation, $e->getMessage());
        }
    }
}