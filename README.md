Local Magmi Core Compatibility Changes
======================================

Updated: 2026-08-23

The installed Magmi fork reports version `0.7.24-git` and includes Magento 2
adaptations. The following local changes have been made:

- Product create/update/touch timestamps and the remaining web status timestamps
  were migrated from removed PHP 8.5 `strftime()` calls to `date()` calls.
- Runtime `core_store` references discovered in optional plugins were changed
  to Magento 2's `store` table.
- A dead commented `core_website` query was removed.
- Obsolete Magento 1 product-flat cleanup was removed from the reindexing and
  product-deletion plugins.
- Obsolete Magento 1 product-flat and category-flat truncation was removed from
  the destructive catalog-clearing utilities.
- The removed `catalog_product_flat` and `catalog_category_flat` indexers were
  removed from the default reindexing plugin configuration.

Upstream Magmi 2 README
=======================

Magmi 2 for Magento 2 > 2.1.x
-----------------------------

This is fork from official magmi Github reposiotry (https://github.com/dweeves/magmi-git).
This fork use version 0.7.23 of magmi with changes for Magento 2 imported from repositories:
- tagesjump/magmi-m2 - https://github.com/tagesjump/magmi-m2
- pushnov-i/magmi-m2 - https://github.com/pushnov-i/magmi-m2
On top of that custom compatibility fixes were added.

We're accepting pull requests.
''''''''''''''''''''''''''''''

Magento CE 2 Support
====================

Current version is in **beta** and tested only for import simple and configurable products, categories, images and simple-configurable links.

**NOTICE: If you want to create URL rewrites please enable "On the fly indexer" plugin!**

**Known working plugins:**
- On the fly category creator/importer
- On the fly indexer
- Configurable Item processor
- Image attributes processor

### Authentication

Magmi now features shared Magento authentication out of the box.

One can simply use their Magento administrative (backend) credentials to login to Magmi.


#### .ini File Warning

While the authentication protects your Magmi web interface from unauthorised logins, it doesn't protect you from a poorly configured server.

Magmi uses .ini files to store it's configuration, and some servers will serve these files as plain text files if the are requested directly.

There is never a reason to serve .ini files to end users on a Magento platform, so ensure that your server is configured not to!




## Changelog form the base (Magento 1 version)

magmi-git 0.7.24
===

Security fix , remove magmi default authentication.
Force usage of magento admin login.

magmi-git 0.7.23
===

The [official Magmi Wiki](http://wiki.magmi.org/) is still hosted at SourceForge.
