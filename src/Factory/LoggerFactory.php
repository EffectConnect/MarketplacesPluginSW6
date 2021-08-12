<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Factory;

use DateTime;
use DateTimeZone;
use EffectConnect\Marketplaces\Helper\FileHelper;
use EffectConnect\Marketplaces\Interfaces\LoggerProcess;
use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Class LoggerFactory
 * @package EffectConnect\Marketplaces\Factory
 */
class LoggerFactory
{
    /**
     * The log channel for the Monolog logger.
     */
    protected const CHANNEL         = 'EffectConnect Marketplaces';

    /**
     * The directory where the logs need to be generated.
     */
    protected const DIRECTORY       = __DIR__ . '/../../data/logs/';

    /**
     * The filename for the generated log file (first string is the process, second string is the date).
     */
    protected const FILENAME_FORMAT = '%s-%s.log';

    /**
     * The time zone for the logs.
     */
    protected const TIME_ZONE       = 'Europe/Amsterdam';

    /**
     * The date format for the date part in the log's filename.
     */
    protected const DATE_FORMAT     = 'Y_m_d';

    /**
     * @var Logger[]
     */
    protected static $_loggers = [];

    /**
     * @param string $process
     * @return Logger
     */
    public static function createLogger(string $process = LoggerProcess::OTHER)
    {
        if (isset(static::$_loggers[$process])) {
            return static::$_loggers[$process];
        }

        try {
            static::$_loggers[$process] = new Logger(static::CHANNEL, [
                new StreamHandler(
                    FileHelper::guaranteeFileLocation(
                        static::DIRECTORY,
                        sprintf(
                            static::FILENAME_FORMAT,
                            $process,
                            (
                                new DateTime(
                                    'now',
                                    (new DateTimeZone(static::TIME_ZONE))
                                )
                            )->format(static::DATE_FORMAT)
                        )
                    )
                )
            ]);
        } catch (Exception $e) {
            static::$_loggers[$process] = new Logger(static::CHANNEL);
        }

        static::$_loggers[$process]->setTimezone(new DateTimeZone(static::TIME_ZONE));

        return static::$_loggers[$process];
    }
}