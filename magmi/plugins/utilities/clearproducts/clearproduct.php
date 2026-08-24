<?php

class ClearProductUtility extends Magmi_UtilityPlugin
{
    public function getPluginInfo()
    {
        return array("name" => "Clear Catalog","author" => "Dweeves","version" => "1.1.0");
    }

    public function runUtility()
    {
        $tables = array("catalog_product_bundle_option",
            "catalog_product_bundle_option_value",
            "catalog_product_bundle_selection",
            "catalog_product_bundle_selection_price",
            "catalog_product_bundle_price_index",
            "catalog_product_bundle_stock_index",
            "catalog_product_entity_datetime",
            "catalog_product_entity_decimal",
            "catalog_product_entity_int",
            "catalog_product_entity_gallery",
            "catalog_product_entity_media_gallery",
            "catalog_product_entity_media_gallery_value",
            "catalog_product_entity_media_gallery_value_to_entity",
            "catalog_product_entity_media_gallery_value_video",
            "catalog_product_entity_text",
            "catalog_product_entity_tier_price",
            "catalog_product_entity_varchar",
            "catalog_product_entity",
            "catalog_product_option",
            "catalog_product_option_price",
            "catalog_product_option_title",
            "catalog_product_option_type_price",
            "catalog_product_option_type_title",
            "catalog_product_option_type_value",
            "catalog_product_super_attribute_label",
            "catalog_product_super_attribute",
            "catalog_product_super_link",
            "catalog_product_link",
            "catalog_product_link_attribute_decimal",
            "catalog_product_link_attribute_varchar",
            "catalog_product_link_attribute_int",
            "catalog_product_relation",
            "catalog_product_website",
            "catalog_product_frontend_action",
            "catalog_compare_item",
            "catalog_url_rewrite_product_category",
            "catalog_category_product_index",
            "catalog_category_product",
            "cataloginventory_stock_item",
            "cataloginventory_stock_status",

            // Magento 2 catalog index data; indexer configuration is preserved.
            "catalog_product_index_eav",
            "catalog_product_index_eav_idx",
            "catalog_product_index_eav_tmp",
            "catalog_product_index_eav_replica",
            "catalog_product_index_eav_decimal",
            "catalog_product_index_eav_decimal_idx",
            "catalog_product_index_eav_decimal_tmp",
            "catalog_product_index_eav_decimal_replica",
            "catalog_product_index_price",
            "catalog_product_index_price_idx",
            "catalog_product_index_price_tmp",
            "catalog_product_index_price_replica",
            "catalog_product_index_tier_price",
            "catalog_product_index_website",
            "catalog_product_attribute_cl",
            "catalog_product_category_cl",
            "catalog_product_price_cl",
            "catalog_category_product_cl",
            "catalogrule_affected_product",
            "catalogrule_product",
            "catalogrule_product_price",
            "catalogsearch_fulltext",

            // Optional Magento product modules.
            "downloadable_link",
            "downloadable_link_price",
            "downloadable_link_title",
            "downloadable_sample",
            "downloadable_sample_title",
            "product_alert_price",
            "product_alert_stock",
            "wishlist_item_option",
            "wishlist_item",
            "report_compared_product_index",
            "report_viewed_product_index",
            "report_viewed_product_aggregated_daily",
            "report_viewed_product_aggregated_monthly",
            "report_viewed_product_aggregated_yearly",
            "weee_tax",

            // MSI data is SKU-based and has no FK to catalog_product_entity.
            "inventory_source_item",
            "inventory_reservation",
            "inventory_low_stock_notification_configuration",
            "inventory_cl");

        $this->exec_stmt("SET FOREIGN_KEY_CHECKS = 0");
        try {
            foreach ($tables as $table) {
                // Keep the utility portable across Magento editions and optional modules.
                if ($this->tableExists($table)) {
                    $this->exec_stmt("TRUNCATE TABLE `" . $this->tablename($table) . "`");
                }
            }

            // Remove product rewrites only; preserve category and CMS rewrites.
            if ($this->tableExists("url_rewrite")) {
                $this->delete(
                    "DELETE FROM `" . $this->tablename("url_rewrite") . "` WHERE entity_type=?",
                    array("product")
                );
            }
        } finally {
            // Never leave the connection with referential-integrity checks disabled.
            $this->exec_stmt("SET FOREIGN_KEY_CHECKS = 1");
        }

        echo "Catalog cleared";
    }

    public function getWarning()
    {
        return "Are you sure?, it will destroy all existing items in catalog!!!";
    }

    public function getShortDescription()
    {
        return "This Utility clears the catalog";
    }
}
