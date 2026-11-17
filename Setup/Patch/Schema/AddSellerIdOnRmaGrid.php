<?php

declare(strict_types=1);

namespace Smile\RetailerAdmin\Setup\Patch\Schema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Add seller_id column and foreign key on 'magento_rma_grid' (Adobe Commerce only, hence not in db_schema.xml).
 */
class AddSellerIdOnRmaGrid implements SchemaPatchInterface
{
    public function __construct(private SchemaSetupInterface $schemaSetup)
    {
    }

    /**
     * @inheritdoc
     */
    public function apply(): self
    {
        $tableName = 'magento_rma_grid';
        $columnName = 'seller_id';
        $connection = $this->schemaSetup->getConnection();
        $table = $this->schemaSetup->getTable($tableName);

        if (!$connection->isTableExists($table) || $connection->tableColumnExists($table, $columnName)) {
            return $this;
        }

        $this->schemaSetup->startSetup();
        $connection->addColumn(
            $table,
            $columnName,
            [
                'type' => Table::TYPE_INTEGER,
                'unsigned' => true,
                'nullable' => true,
                'default' => null,
                'comment' => 'Seller ID',
            ]
        );
        $connection->addForeignKey(
            $this->schemaSetup->getFkName($tableName, $columnName, 'smile_seller_entity', 'entity_id'),
            $table,
            $columnName,
            $this->schemaSetup->getTable('smile_seller_entity'),
            'entity_id',
            Table::ACTION_SET_NULL
        );
        $this->schemaSetup->endSetup();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
