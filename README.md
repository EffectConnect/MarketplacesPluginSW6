# EffectConnect Marketplaces - Shopware 6 plugin

[![Latest Version on Packagist](https://img.shields.io/packagist/v/effectconnect/marketplaces-plugin-sw6.svg?style=flat-square)](https://packagist.org/packages/effectconnect/marketplaces-plugin-sw6)
[![Latest Stable Version](https://poser.pugx.org/effectconnect/marketplaces-plugin-sw6/v/stable?style=flat-square)](https://packagist.org/packages/effectconnect/marketplaces-plugin-sw6)
[![Total Downloads](https://img.shields.io/packagist/dt/effectconnect/marketplaces-plugin-sw6.svg?style=flat-square)](https://packagist.org/packages/effectconnect/marketplaces-plugin-sw6)
[![License](https://poser.pugx.org/effectconnect/marketplaces-plugin-sw6/license?style=flat-square?style=flat-square)](https://packagist.org/packages/effectconnect/marketplaces-plugin-sw6)
[![Monthly Downloads](https://poser.pugx.org/effectconnect/marketplaces-plugin-sw6/d/monthly?style=flat-square)](https://packagist.org/packages/effectconnect/marketplaces-plugin-sw6)
[![Daily Downloads](https://poser.pugx.org/effectconnect/marketplaces-plugin-sw6/d/daily?style=flat-square)](https://packagist.org/packages/effectconnect/marketplaces-plugin-sw6)

Use this plugin to connect your Shopware 6 webshop with EffectConnect Marketplaces. For more information about EffectConnect, go to the [EffectConnect website](https://www.effectconnect.com "EffectConnect Website").

> Warning: Only Shopware 6.6 is officially supported at this moment<br><br>
> For Shopware 6.2, 6.3, 6.4 and 6.5 support, use [releases](https://github.com/EffectConnect/MarketplacesPluginSW6/releases) prior to 1.8.0.

**Important: before installing this plugin please contact the EffectConnect sales department via +31(0)852088432 and/or sales@effectconnect.com so we can provide you with a new EffectConnect account.**

## Table of Contents
  * [Installation](#installation)
    1. [Shopware Store (recommended)](#1-shopware-store--recommended-)
    2. [ZIP upload](#2-zip-upload)
  * [Activation](#activate)
  * [Configuration](#configuration)
  * [Setup module](#setup-module)
    + [API Keys](#api-keys)
    + [Tasks](#tasks)

## Installation
You can install the EffectConnect Marketplaces Shopware 6 Plugin using the ZIP installation method.

##### ZIP upload
In the back-end of your Shopware 6 webshop, go to Extensions -> My Extensions and click the upload extension button. Then upload the ZIP-file which can be downloaded from the GitHub releases page of this project.

#### Activation
After installation the plugin can be activated in the Shopware 6 Extensions module.

#### Updating
In Shopware versions below 6.4.17 it's necessary to run the scheduled-task:register command after plug-in updates.

## Configuration
#### API Keys
First create an API keyset in your EffectConnect Marketplace environment. Then go the configuration page of the EffectConnect Marketplaces plugin in your Shopware 6 webshop and set the API keys for the appropriate sales channel.

#### Tasks
The automatic import, export, and cleanup can be enabled in two ways:
1. By configuring the Shopware 6 scheduled tasks. This should run every minute. For more information, see the Shopware 6 documentation.
2. By setting a couple of commands in the crontab (or another cronjobs module), see below an example of such a configuration (replace {APPLICATION_PATH} with the location of the Shopware 6 installation):

```bash
0 3 * * * php {APPLICATION_PATH}/bin/console ec:export-catalog
*/30 * * * * php {APPLICATION_PATH}/bin/console ec:export-offers
*/15 * * * * php {APPLICATION_PATH}/bin/console ec:import-orders
*/15 * * * * php {APPLICATION_PATH}/bin/console ec:export-shipments
0 * * * * php {APPLICATION_PATH}/bin/console ec:clean-exports
0 * * * * php {APPLICATION_PATH}/bin/console ec:clean-log
* * * * * php {APPLICATION_PATH}/bin/console ec:run-shipment-queue
* * * * * php {APPLICATION_PATH}/bin/console ec:run-offer-queue
```

The cron times above can be adjusted as desired. The times above are explained below:
- **Catalog export (ec:export-catalog)**: Every night at 3:00 AM.
- **Offers export (ec:export-offers)**: Every half hour (00th minute, 30th minute).
- **Import orders (ec:import-orders)**: Every 15 minutes (00th minute, 15th minute, 30th minute, 45th minute).
- **Export shipments (ec:export-shipments)**: Every 15 minutes (00th minute, 15th minute, 30th minute, 45th minute).
- **Clearing old exports (ec:clean-exports)**: Every hour on the hour.
- **Clearing old logs (ec:clean-log)**: Every hour on the hour.
- **Exports all queued shipments (ec:run-shipment-queue)**: Every minute.
- **Exports all queued offer changes (ec:run-offer-queue)**: Every minute.

*Note: In older versions of Shopware 6 the extensions module is located under Settings -> System -> Plugins.*