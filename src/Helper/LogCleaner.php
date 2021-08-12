<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Helper;

use DateTime;
use DateTimeZone;
use Exception;

/**
 * Class LogCleaner
 * @package EffectConnect\Marketplaces\Helper
 */
class LogCleaner
{
    /**
     * Determines after how many days a log item is deleted.
     */
    public const LOG_EXPIRATION_DAYS = 7;

    /**
     * Regex used for checking if it is a valid log file.
     */
    protected const LOG_FILENAME_REGEX  = "/^([a-z_].*)-([0-9]{4})_([0-9]{2})_([0-9]{2}).log$/";

    /**
     * The date format for the date part in the log's filename.
     */
    protected const DATE_FORMAT         = 'Y_m_d';

    /**
     * The time zone for the logs.
     */
    protected const TIME_ZONE       = 'Europe/Amsterdam';

    /**
     * The directory where the logs are situated.
     */
    protected const DIRECTORY           = __DIR__ . '/../../data/logs/';

    /**
     * Clean the logs directory for expired logs.
     * @param int $logExpirationDays
     */
    public static function cleanLog(int $logExpirationDays = self::LOG_EXPIRATION_DAYS)
    {
        if (!file_exists(static::DIRECTORY) || !is_dir(static::DIRECTORY)) {
            return;
        }

        foreach (scandir(static::DIRECTORY) as $file) {
            $location   = static::DIRECTORY . $file;

            if (!boolval(preg_match(static::LOG_FILENAME_REGEX, $file))) {
                continue;
            }

            if (!file_exists($location) || !is_file($location)) {
                continue;
            }

            $location   = realpath($location);
            $datePart   = explode('.', explode('-', $file)[1])[0];
            $dateTime   = DateTime::createFromFormat(
                static::DATE_FORMAT,
                $datePart,
                (new DateTimeZone(static::TIME_ZONE))
            );

            try {
                $difference = $dateTime->diff(
                    new DateTime(
                        'now',
                        (new DateTimeZone(static::TIME_ZONE))
                    )
                );
            } catch (Exception $e) {
                $difference = 0;
            }

            $daysString = $difference->format('%r%a');

            if (!is_numeric($daysString)) {
                continue;
            }

            $days       = intval($daysString);

            if ($days < $logExpirationDays) {
                continue;
            }

            unlink($location);
        }
    }
}