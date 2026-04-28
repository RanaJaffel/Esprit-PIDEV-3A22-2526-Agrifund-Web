const Encore = require('@symfony/webpack-encore');

// Configure runtime environment
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // Output path
    .setOutputPath('public/build/')
    .setPublicPath('/build')

    /*
     * ENTRY CONFIG (JS)
     */
    .addEntry('app', './assets/app.js')
    .addEntry('messagerie', './assets/js/messagerie.js')
    .addEntry('sidebar', './assets/js/sidebar.js')

    /*
     * STYLE ENTRIES (CSS)
     */
    .addStyleEntry('variables', './assets/css/variables.css')
    .addStyleEntry('admin', './assets/css/admin.css')
    .addStyleEntry('agriculteur', './assets/css/agriculteur.css')
    .addStyleEntry('banque', './assets/css/banque.css')
    .addStyleEntry('messagerie_style', './assets/css/messagerie.css')
    .addStyleEntry('sidebar_style', './assets/css/sidebar.css')

    // Optimization
    .splitEntryChunks()
    .enableSingleRuntimeChunk()

    /*
     * FEATURES
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())

    // Babel config
    .configureBabel((config) => {
        config.plugins.push('@babel/plugin-proposal-class-properties');
    })

    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })

    // Loaders
    .enableSassLoader()
    .enablePostCssLoader()
    .enableStimulusBridge('./assets/controllers.json')

    // jQuery support
    .autoProvidejQuery()
;

module.exports = Encore.getWebpackConfig();
