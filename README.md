PrestaImageBundle
===================

## Overview

PrestaImageBundle is a Symfony bundle providing tools to resize local/remote images before uploading them through a classic form. 
It uses [Cropper][1] jQuery plugin.

## Installation

### Require the bundle as a Composer dependency

```bash
php composer.phar require presta/image-bundle
```

### Enable the bundle in the kernel

```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = [
        // ...
        new Presta\ImageBundle\ImageBundle(),
    ];
}
```

### Install assets

See Cropper [quick start section][2] to install Cropper assets. 

## Contributing

Pull requests are welcome.

Thanks to
[everyone who has contributed](https://github.com/prestaconcept/PrestaImageBundle/graphs/contributors) already.

---

*This project is supported by [PrestaConcept](http://www.prestaconcept.net)*

**Lead Developer** : [@J-Ben87](https://github.com/J-Ben87)

Released under the MIT License

[1]: https://fengyuanchen.github.io/cropper/
[2]: https://github.com/fengyuanchen/cropper#quick-start
