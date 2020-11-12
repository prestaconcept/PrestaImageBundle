# Webpack Encore

## How to

- Install npm dependencies

```bash
npm install --save bootstrap cropperjs jquery jquery-cropper popper.js
npm install --save-dev @symfony/webpack-encore node-sass sass-loader webpack-notifier
```

- Suggested `webpack.config.js`

```javascript
const Encore = require('@symfony/webpack-encore');
const path = require('path');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')

    .addEntry('js/app', './assets/js/app.js')
    .addStyleEntry('css/app', './assets/css/app.scss')

    .splitEntryChunks()
    .enableSingleRuntimeChunk()

    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())

    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })

    .addAliases({
        prestaimage: path.resolve(__dirname, 'public/bundles/prestaimage')
    })

    .enableSassLoader()
    .enableIntegrityHashes(Encore.isProduction())
    .autoProvidejQuery()
;

module.exports = Encore.getWebpackConfig();
```

- Minimal `assets/css/app.scss`

```scss
@import "~bootstrap/scss/bootstrap";
@import "~cropper/dist/cropper.min.css";
@import "../../public/bundles/prestaimage/css/cropper.css";
```

- Minimal `assets/js/app.js`

```javascript
import $ from 'jquery';
import 'bootstrap';
import 'cropperjs'
import * as Cropper from 'prestaimage/js/cropper';

$(function() {
    $('.cropper').each(function() {
        new Cropper($(this));
    });
});
```

- Minimal `templates/base.html.twig`

```twig
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>{% block title %}Welcome!{% endblock %}</title>
        {% block stylesheets %}
            {{ encore_entry_link_tags('css/app') }}
        {% endblock %}
    </head>
    <body>
        {% block body %}{% endblock %}
        {% block javascripts %}
            {{ encore_entry_script_tags('js/app') }}
        {% endblock %}
    </body>
</html>
```
