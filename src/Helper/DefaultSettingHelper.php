<?php

namespace EffectConnect\Marketplaces\Helper;

use EffectConnect\Marketplaces\Setting\SettingStruct;

class DefaultSettingHelper
{
    public static function getDefaults(): array
    {
        $settings = (new SettingStruct());
        return [
            'catalogExportSchedule' => $settings->getCatalogExportSchedule(),
            'offerExportSchedule' => $settings->getOfferExportSchedule(),
            'orderImportSchedule' => $settings->getOrderImportSchedule(),
            'addLeadingZeroToEan' => $settings->isAddLeadingZeroToEan(),
            'useSpecialPrice' => $settings->isUseSpecialPrice(),
            'useFallbackTranslations' => $settings->isUseFallbackTranslations(),
            'useSalesChannelDefaultLanguageAsFirstFallbackLanguage' => $settings->isUseSalesChannelDefaultLanguageAsFirstFallbackLanguage(),
            'useSystemLanguages' => $settings->isUseSystemLanguages(),
            'stockType' => $settings->getStockType(),
            'paymentStatus' => $settings->getPaymentStatus(),
            'orderStatus' => $settings->getOrderStatus(),
            'createCustomer' => $settings->isCreateCustomer(),
            'importExternallyFulfilledOrders' => $settings->isImportExternallyFulfilledOrders(),
            'customerSourceType' => $settings->getCustomerSourceType(),
        ];
    }
}