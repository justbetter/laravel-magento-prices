# Upgrade Guide

## 1.x to 2.x

2.x introduces a complete refactor of the package structure.

A few highlights:
- Simplified implementation
- Support updating via Magento 2 bulk async requests
- Removed error logger, replaced with activity log
- Dropped support for Laravel 10

### Update your project

The price retriever and SKU retriever classes all have been merged into a single repository class.
Refer to the readme on how to implement this.

The configuration file has been stripped, most of the configuration is now done in the repository class.

A lot of classes have been renamed, be sure to update your scheduler and check all classes that you use.
The price model has been renamed from `MagentoPrice` to `Price`.
