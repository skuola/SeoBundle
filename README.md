# SeoBundle

##Installation

Install the bundle:

    composer require skuola/seo-bundle

Register the bundle in `app/AppKernel.php`:

``` php
<?php
// app/AppKernel.php
public function registerBundles()
{
    return array(
        // ...
        new Skuola\SeoBundle\SkuolaSeoBundle()
    );
}
```
