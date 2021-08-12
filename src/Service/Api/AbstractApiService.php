<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Service\Api;

use EffectConnect\Marketplaces\Factory\LoggerFactory;

/**
 * Class AbstractApiService
 * @package EffectConnect\Marketplaces\Service\Api
 */
abstract class AbstractApiService
{
    /**
     * @var InteractionService
     */
    protected $_interactionService;

    /**
     * @var LoggerFactory
     */
    protected $_loggerFactory;

    /**
     * AbstractApiService constructor.
     *
     * @param InteractionService $interactionService
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(
        InteractionService $interactionService,
        LoggerFactory $loggerFactory
    ) {
        $this->_interactionService  = $interactionService;
        $this->_loggerFactory       = $loggerFactory;
    }

    /**
     * @return InteractionService
     */
    public function getInteractionService(): InteractionService
    {
        return $this->_interactionService;
    }
}