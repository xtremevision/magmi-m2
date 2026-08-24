<?php

/**
 * Synchronizes Magmi's legacy stock updates with Magento Multi-Source Inventory.
 *
 * Magmi writes cataloginventory_stock_item directly, bypassing Magento's API
 * compatibility layer that normally mirrors Default Stock into MSI. This
 * processor runs after Magmi has finalized the legacy stock row and copies its
 * resulting quantity and status to a configurable MSI source.
 */
class Magmi_MsiInventoryItemProcessor extends Magmi_ItemProcessor
{
    private $sourceCode = 'default';
    private $stockId = 1;
    private $enabled = true;

    public function getPluginInfo()
    {
        return array(
            'name' => 'Magento MSI Inventory Synchronizer',
            'author' => 'Magmi Community',
            'version' => '1.0.0',
        );
    }

    public function initialize($params)
    {
        $this->sourceCode = trim((string)$this->getParam('MSI:source_code', 'default'));
        $this->stockId = (int)$this->getParam(
            'MSI:legacy_stock_id',
            $this->getParam('MSI:stock_id', 1)
        );

        if ($this->sourceCode === '') {
            throw new InvalidArgumentException('MSI:source_code cannot be empty.');
        }
        if ($this->stockId < 1) {
            throw new InvalidArgumentException('MSI:legacy_stock_id must be a positive integer.');
        }

        $sourceItemTable = $this->tablename('inventory_source_item');
        $sourceTable = $this->tablename('inventory_source');

        // Remain usable on Magento installations where MSI is not installed.
        if (!$this->tableExists($sourceItemTable) || !$this->tableExists($sourceTable)) {
            $this->enabled = false;
            $this->log('MSI tables are not present; synchronization is disabled.', 'warning');
            return;
        }

        $source = $this->selectOne(
            "SELECT source_code FROM `$sourceTable` WHERE source_code=? AND enabled=1",
            array($this->sourceCode),
            'source_code'
        );
        if ($source === false || $source === null) {
            throw new RuntimeException(
                "MSI source '{$this->sourceCode}' does not exist or is disabled."
            );
        }
    }

    public function getPluginParams($params)
    {
        $pluginParams = array();
        foreach ($params as $key => $value) {
            if (strpos($key, 'MSI:') === 0) {
                $pluginParams[$key] = $value;
            }
        }
        return $pluginParams;
    }

    /**
     * Mirror the final legacy quantity/status after Magmi's updateStock().
     *
     * Reading the saved stock row preserves Magmi's relative quantity handling
     * and any is_in_stock value it calculated during the import.
     */
    public function processItemAfterImport(&$item, $params = null)
    {
        if (!$this->enabled || !$this->containsInventoryData($item)) {
            return true;
        }

        $productId = isset($params['product_id']) ? (int)$params['product_id'] : 0;
        $sku = isset($item['sku']) ? trim((string)$item['sku']) : '';
        if ($productId < 1 || $sku === '') {
            throw new RuntimeException('MSI synchronization requires a product ID and SKU.');
        }

        $stockItemTable = $this->tablename('cataloginventory_stock_item');
        $sourceItemTable = $this->tablename('inventory_source_item');
        $stockStatement = $this->select(
            "SELECT qty, is_in_stock FROM `$stockItemTable` WHERE product_id=? AND stock_id=?",
            array($productId, $this->stockId)
        );
        $stock = $stockStatement->fetch(PDO::FETCH_ASSOC);
        $stockStatement->closeCursor();
        if (!is_array($stock)) {
            throw new RuntimeException(
                "Legacy stock row {$this->stockId} was not found for SKU '$sku'."
            );
        }

        $sql = "INSERT INTO `$sourceItemTable` (source_code, sku, quantity, status)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    quantity=VALUES(quantity),
                    status=VALUES(status)";

        $this->insert(
            $sql,
            array($this->sourceCode, $sku, $stock['qty'], $stock['is_in_stock'])
        );

        return true;
    }

    private function containsInventoryData($item)
    {
        return array_key_exists('qty', $item)
            || array_key_exists('is_in_stock', $item)
            || array_key_exists('stock_status', $item);
    }

    public static function getCategory()
    {
        return 'Inventory';
    }
}
