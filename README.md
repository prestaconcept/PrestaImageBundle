PrestaImageBundle
=================

![tests](https://github.com/prestaconcept/PrestaImagebundle/actions/workflows/tests.yml/badge.svg)
![quality](https://github.com/prestaconcept/PrestaImagebundle/actions/workflows/quality.yml/badge.svg)
[![codecov](https://codecov.io/gh/prestaconcept/PrestaImagebundle/branch/4.x/graph/badge.svg?token=ls4VjT51Pi)](https://codecov.io/gh/prestaconcept/PrestaImagebundle)
[![Latest Stable Version](https://poser.pugx.org/presta/image-bundle/v/stable.png)](https://packagist.org/packages/presta/image-bundle)
[![Total Downloads](https://poser.pugx.org/presta/image-bundle/downloads.png)](https://packagist.org/packages/presta/image-bundle)

Overview
--------

PrestaImageBundle is a Symfony bundle providing tools to resize uploaded and remote images before sending them through a classic form.
It uses the [Cropper.js][1] library.

Installation
------------

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Applications that use Symfony Flex

Open a command console, enter your project directory and execute:

```console
$ composer require presta/image-bundle
```

### Applications that don't use Symfony Flex

#### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require presta/image-bundle
```

#### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Presta\ImageBundle\PrestaImageBundle::class => ['all' => true],
];
```

Configuration
-------------

You must configure the `form_layout.html.twig` form theme into `config/packages/twig.yaml`.

```yaml
twig:
    form_themes:
        - "@PrestaImage/form/form_layout.html.twig"
```

> Note: you can also create your own form theme instead.

You must include the routing into `config/routes.yaml`:

```yaml
presta_image:
    resource: "@PrestaImageBundle/config/routing.yaml"
```

See VichUploader [documentation][2] to configure the bundle.

Assets
------

See Cropper.js [documentation][3] to install assets.

Don't forget to include the following assets in your page:

- `@PrestaImageBundle/public/css/cropper.css`
- `@PrestaImageBundle/public/js/cropper.js`

### How to: implementation examples

- [Webpack Encore][4]

Usage
-----

### Initialize cropper

```javascript
document.querySelectorAll('.presta-image').forEach(element => {
    new Cropper(element)
})
```

### Use the form type

```php
<?php

use Presta\ImageBundle\Form\Type\ImageType;

public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $builder
        ->add('image', ImageType::class)
    ;
}
```

Available options for the `ImageType`:

- `aspect_ratios` (`array`): a list of aspect ratio to apply when resizing an image
- `cropper_options` (`array`): a list of options supported by cropper (default: `['autoCropArea' => 1]`)
- `max_width` (`int`): the max width of the cropped image send to server (default: `320`)
- `max_height` (`int`): the max height of the cropped image send to server (default: `180`)
- `preview_width` (`string`): the max width to use when displaying the image preview - can be in px, % or other css value (default: `'320px'`)
- `preview_height` (`string`): the max height to use when displaying the image preview - can be in px, % or other css value (default: `'180px'`)
- `upload_button_class` (`string`): CSS class of the "upload" button (default: `''`)
- `cancel_button_class` (`string`): CSS class of the "cancel" button (default: `''`)
- `save_button_class` (`string`): CSS class of the "save" button (default: `''`)
- `download_uri` (`string`): the path where the image is located (default: `null`, automatically set)
- `show_image` (`bool`): whether the image should be rendered in the form or not (default: `null`, will default to `true` in next major)
- ~~`download_link` (`bool`): whether the image should be rendered in the form or not (default: `true`)~~ **Deprecated, will be removed (replaced by `show_image`) in next major**
- `file_upload_enabled` (`bool`): whether to enable the file upload widget or not (default: `true`)
- `remote_url_enabled` (`bool`): whether to enable the remote url widget or not (default: `true`)
- `rotation_enabled` (`bool`): whether to enable the rotation or not (default: `false`)
- `upload_mimetype` (`string`): format of the image to be uploaded (default: `image/png`)  
  (Note: If the chosen mimetype is not supported by the browser, it will silently fall back to `image/png`)
- `upload_quality` (`float`): quality (0..1) of uploaded image for lossy imageformats (eg. `image/jpeg`) (default: `0.92`) 
  
#### Notes

You can find Cropper.js options [here][5].

The `max_width` and `max_height` options are used to define maximum size the cropped uploaded image will be.
Bigger images (after cropping) are scaled down.

**Security Note:** NEVER rely on this size constraints and the format settings as 
they can easily be manipulated client side. ALWAYS validate the `image-data`, `image-size,` `image-format` server side! 

Contributing
------------

Pull requests are welcome.

Thanks to
[everyone who has contributed](https://github.com/prestaconcept/PrestaImageBundle/graphs/contributors) already.

---

*This project is supported by [PrestaConcept](http://www.prestaconcept.net)*

**Lead Developer** : [@J-Ben87](https://github.com/J-Ben87)

Released under the MIT License

[1]: https://github.com/fengyuanchen/cropperjs
[2]: https://github.com/dustin10/VichUploaderBundle/blob/master/docs/usage.md
[3]: https://github.com/fengyuanchen/cropperjs#getting-started
[4]: https://github.com/prestaconcept/PrestaImageBundle/blob/master/docs/webpack.md
[5]: https://github.com/fengyuanchen/cropperjs#options
