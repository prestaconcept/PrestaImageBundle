# Webpack Encore

## How to

- Install npm dependencies

```bash
npm install --save cropperjs
npm install --save-dev @symfony/webpack-encore node-sass sass-loader webpack-notifier
```

- Suggested `webpack.config.js`

```javascript
const Encore = require('@symfony/webpack-encore')
const path = require('path')

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev')
}

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')

    .addEntry('js/app', './assets/scripts/app.js')
    .addStyleEntry('css/app', './assets/styles/app.scss')

    .splitEntryChunks()
    .enableSingleRuntimeChunk()

    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())

    .configureBabel(config => {
        config.plugins.push('@babel/plugin-proposal-class-properties')
    })
    .configureBabelPresetEnv(config => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })

    .addAliases({
        prestaimage: path.resolve(__dirname, 'public/bundles/prestaimage')
    })

    .enableSassLoader()
    .enableIntegrityHashes(Encore.isProduction())


module.exports = Encore.getWebpackConfig()
```

- Minimal `assets/styles/app.scss`

```scss
@import "~cropperjs/dist/cropper.min.css";
@import "../../public/bundles/prestaimage/css/cropper.css";
```

- Minimal `assets/js/app.js`

```javascript
import 'cropperjs'
import Cropper from 'prestaimage/js/cropper'

document.querySelectorAll('.presta-image').forEach(element => {
    new Cropper(element)
})
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
        {% block javascripts %}
            {{ encore_entry_script_tags('js/app') }}
        {% endblock %}****
    </head>
    <body>
        {% block body %}{% endblock %}
    </body>
</html>
```
