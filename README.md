# DevAwesome Base for Magento 2

[![Latest Stable Version](http://poser.pugx.org/devawesome/module-base/v)](https://packagist.org/packages/devawesome/module-base)
[![Total Downloads](http://poser.pugx.org/devawesome/module-base/downloads)](https://packagist.org/packages/devawesome/module-base)
[![License](http://poser.pugx.org/devawesome/module-base/license)](https://packagist.org/packages/devawesome/module-base)

## How to install & upgrade DevAwesome_Base

### 1. Install via composer (recommend)

We recommend you to install DevAwesome_Base module via composer. It is easy to install, update and maintaince.

Run the following command in Magento 2 root folder.

#### 1.1 Install

```
composer require devawesome/module-base
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
```

#### 1.2 Upgrade

```
composer update devawesome/module-base
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
```

Run compile if your store in Product mode:

```
php bin/magento setup:di:compile
```

### 2. Copy and paste

If you don't want to install via composer, you can use this way.

- Download [the latest version here](https://github.com/rahulbarot/magento2-Base/archive/refs/heads/master.zip)
- Extract `master.zip` file to `app/code/DevAwesome/Base` ; You should create a folder path `app/code/DevAwesome/Base` if not exist.
- Go to Magento root folder and run upgrade command line to install `DevAwesome_Base`:

```
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
```
