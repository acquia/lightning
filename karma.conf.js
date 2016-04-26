// Karma configuration
// Generated on Tue Mar 29 2016 13:32:10 GMT-0400 (EDT)

module.exports = function(config) {
  function base (script) {
    return './docroot/profiles/lightning/' + script;
  }

  function media (script) {
    return base('modules/lightning_features/lightning_media/' + script);
  }

  config.set({

    // base path that will be used to resolve all patterns (eg. files, exclude)
    basePath: '',


    // frameworks to use
    // available frameworks: https://npmjs.org/browse/keyword/karma-adapter
    frameworks: ['jasmine'],


    // list of files / patterns to load in the browser
    files: [
      base('libraries/dropzone/dist/min/dropzone.min.js'),
      'docroot/core/assets/vendor/jquery/jquery.min.js',
      'docroot/core/assets/vendor/jquery.ui/ui/core-min.js',
      'docroot/core/assets/vendor/jquery.ui/ui/widget-min.js',
      'docroot/core/assets/vendor/jquery.ui/ui/tabs-min.js',
      'docroot/core/assets/vendor/underscore/underscore-min.js',
      'docroot/core/assets/vendor/backbone/backbone-min.js',
      base('libraries/backbone.collectionView/dist/backbone.collectionView.min.js'),
      'node_modules/jasmine-ajax/lib/mock-ajax.js',
      'node_modules/sinon/pkg/sinon.js',
      media('js/LibraryConnector.js'),
      media('js/models/*.js'),
      media('js/views/*.js'),
      media('js/tests/*.js')
    ],


    // list of files to exclude
    exclude: [
    ],


    // preprocess matching files before serving them to the browser
    // available preprocessors: https://npmjs.org/browse/keyword/karma-preprocessor
    preprocessors: {
    },


    // test results reporter to use
    // possible values: 'dots', 'progress'
    // available reporters: https://npmjs.org/browse/keyword/karma-reporter
    reporters: ['progress'],


    // web server port
    port: 9876,


    // enable / disable colors in the output (reporters and logs)
    colors: true,


    // level of logging
    // possible values: config.LOG_DISABLE || config.LOG_ERROR || config.LOG_WARN || config.LOG_INFO || config.LOG_DEBUG
    logLevel: config.LOG_INFO,


    // enable / disable watching file and executing tests whenever any file changes
    autoWatch: false,


    // start these browsers
    // available browser launchers: https://npmjs.org/browse/keyword/karma-launcher
    browsers: ['PhantomJS'],


    // Continuous Integration mode
    // if true, Karma captures browsers, runs the tests and exits
    singleRun: false,

    // Concurrency level
    // how many browser should be started simultaneous
    concurrency: Infinity
  })
}
