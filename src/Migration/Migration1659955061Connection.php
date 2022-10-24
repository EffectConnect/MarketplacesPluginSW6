<?php
namespace EffectConnect\Marketplaces\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1659955061Connection extends MigrationStep
{
    /**
     * @inheritDoc
     */
    public function getCreationTimestamp(): int
    {
        return 1659955061;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function update(Connection $connection): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `ec_connection` (
    `id` BINARY(16) NOT NULL,
    `sales_channel_id` BINARY(16) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `public_key` VARCHAR(255) DEFAULT NULL,
    `secret_key` VARCHAR(255) DEFAULT NULL,
    `catalog_export_schedule` INT DEFAULT NULL,
    `add_leading_zero_to_ean` TINYINT(1) DEFAULT NULL,
    `use_special_price` TINYINT(1) DEFAULT NULL,
    `use_fallback_translations` TINYINT(1) DEFAULT NULL,
    `use_sales_channel_default_language_as_first_fallback_language` TINYINT(1) DEFAULT NULL,
    `use_system_languages` TINYINT(1) DEFAULT NULL,
    `offer_export_schedule` INT DEFAULT NULL,
    `stock_type` VARCHAR(255) DEFAULT NULL,
    `order_import_schedule` INT DEFAULT NULL,
    `payment_status` VARCHAR(255) DEFAULT NULL,
    `order_status` VARCHAR(255) DEFAULT NULL,
    `payment_method` VARCHAR(255) DEFAULT NULL,
    `shipping_method` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3),
    PRIMARY KEY (`id`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;
SQL;
        // use executeUpdate for shopware 6.3 compatibility.
        $connection->executeUpdate($sql);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function updateDestructive(Connection $connection): void
    {
        $connection->executeUpdate("DROP TABLE `ec_connection`");
        $this->update($connection);
    }
}