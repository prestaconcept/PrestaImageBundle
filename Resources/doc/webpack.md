# Webpack Encore

## How to

- Install npm dependencies

```bash
npm install --save bootstrap cropper jquery popper.js
npm install --save-dev @symfony/webpack-encore node-sass sass-loader webpack-notifier
```

- Suggested `webpack.config.js`

```javascript
const Encore = require('@symfony/webpack-encore');
const path = require('path');

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')

    .addEntry('js/app', './assets/js/app.js')
    .addStyleEntry('css/app', './assets/css/app.scss')

    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())

    .addAliases({
        prestaimage: path.resolve(__dirname, 'public/bundles/prestaimage')
    })

    .enableSassLoader()
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
import 'bootstrap';
import 'cropper/dist/cropper.min'
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
            <link rel="stylesheet" href="{{ asset('build/css/app.css') }}">
        {% endblock %}
    </head>
    <body>
        {% block body %}{% endblock %}
        {% block javascripts %}
            <script src="{{ asset('build/js/app.js') }}"></script>
        {% endblock %}
    </body>
</html>
```
