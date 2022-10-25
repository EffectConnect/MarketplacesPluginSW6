<?php

namespace EffectConnect\Marketplaces\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1653480004ExportQueue extends MigrationStep
{

    /**
     * @inheritDoc
     */
    public function getCreationTimestamp(): int
    {
        return 1653480004;
    }

    /**
     * @inheritDoc
     * @throws \Doctrine\DBAL\Exception
     */
    public function update(Connection $connection): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `ec_export_queue` (
    `id` BINARY(16) NOT NULL,
    `type` ENUM('offer', 'shipment') NOT NULL,
    `identifier` BINARY(16) NOT NULL,
    `sales_channel_id` BINARY(16) DEFAULT NULL,
    `data` TEXT COLLATE utf8mb4_unicode_ci NOT NULL,
    `status` ENUM('queued', 'started', 'completed') NOT NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3),
    PRIMARY KEY (`id`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;
SQL;
        $connection->executeUpdate($sql);
    }

    /**
     * @inheritDoc
     */
    public function updateDestructive(Connection $connection): void
    {
        $connection->executeUpdate("DROP TABLE `ec_export_queue`");
        $this->update($connection);
    }
}