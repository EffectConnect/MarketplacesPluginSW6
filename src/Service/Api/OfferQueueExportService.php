<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Service\Api;

use EffectConnect\Marketplaces\Exception\ApiCallFailedException;
use EffectConnect\Marketplaces\Exception\CreateCurlFileException;
use EffectConnect\Marketplaces\Exception\OfferExportFailedException;
use EffectConnect\Marketplaces\Factory\LoggerFactory;
use EffectConnect\Marketplaces\Interfaces\LoggerProcess;
use EffectConnect\Marketplaces\Service\Transformer\OfferTransformerService;
use EffectConnect\PHPSdk\Core;
use Exception;
use Monolog\Logger;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

/**
 * Class OfferQueueExportService
 * @package EffectConnect\Marketplaces\Service\Api
 */
class OfferQueueExportService extends OfferExportService
{
    /**
     * The logger process for this service.
     */
    protected const LOGGER_PROCESS      = LoggerProcess::OFFER_CHANGE_TASK;
}