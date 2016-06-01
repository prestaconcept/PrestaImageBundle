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

You must add the bundle into `app/AppKernel.php`:

```php
<?php

public function registerBundles()
{
    $bundles = [
        // ...
        new Presta\ImageBundle\ImageBundle(),
    ];
}
```

### Configure the bundle

You must use the `image_widget.html.twig` form theme into `app/config.yml`.

```yml
twig:
    form_themes:
        - "PrestaImageBundle:form:image_widget.html.twig"
```

### Install assets

See Cropper [quick start section][2] to install Cropper assets.

Note that [jQuery][3] and [Bootstrap][4] are required.

Don't forget to include the following assets in your page:

- `@PrestaImageBundle/Resources/public/css/cropper.css`
- `@PrestaImageBundle/Resources/public/js/cropper.js`

## Usage

### Initialize cropper

```javascript
(function(w, $) {

    'use strict';

    $(function() {
        $('.cropper').each(function() {
            new Cropper($(this));
        });
    });

})(window, jQuery);
```

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
[3]: https://jquery.com/download/
[4]: http://getbootstrap.com/getting-started/#download
