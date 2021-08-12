<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Traits;

use EffectConnect\Marketplaces\Exception\CustomFieldsSetupException;
use EffectConnect\Marketplaces\Service\CustomFieldService;
use Shopware\Core\Framework\Context;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Trait CustomFieldsTrait
 * @package EffectConnect\Marketplaces\Traits
 */
trait CustomFieldsTrait
{
    /**
     * Add the EffectConnect custom fields.
     *
     * @param Context $context
     * @param ContainerInterface $container
     */
    protected function addCustomFields(Context $context, ContainerInterface $container)
    {
        $customFieldService = new CustomFieldService($container);

        try {
            $customFieldService->createCustomFields($context);
        } catch (CustomFieldsSetupException $e) {
            return;
        }
    }
}