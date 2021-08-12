<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Helper;

use Shopware\Core\Kernel;

/**
 * Class SystemHelper
 * @package EffectConnect\Marketplaces\Helper
 */
class SystemHelper
{
    /**
     * Compare a version with the installed Shopware version.
     *
     * @param string $version
     * @param string $operator
     * @return bool|int
     */
    public static function compareVersion(string $version, string $operator = '=')
    {
        return version_compare(Kernel::SHOPWARE_FALLBACK_VERSION, $version, $operator);
    }
}