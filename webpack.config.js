const Encore = require('@symfony/webpack-encore');
const path = require('path');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .addEntry('app', './vendor/phplist/web-frontend/assets/app.js')
    .addStyleEntry('styles', './vendor/phplist/web-frontend/assets/styles/app.css')
    .enableVueLoader(() => {}, { version: 3 })
    .enableSingleRuntimeChunk()
    .enablePostCssLoader()
    .copyFiles({
        from: './vendor/phplist/web-frontend/assets/images',
        to: 'images/[path][name].[hash:8].[ext]',
    })
    .addAliases({
        '@': path.resolve(__dirname, 'vendor/phplist/web-frontend/assets'),
        '@images': path.resolve(__dirname, 'vendor/phplist/web-frontend/assets/images'),
    })
;

module.exports = Encore.getWebpackConfig();
