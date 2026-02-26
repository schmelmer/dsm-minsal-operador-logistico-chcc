const mix = require('laravel-mix');

mix.js('resources/assets/js/app.js', 'public/js')
  .webpackConfig({
    module: {
      rules: [
        {test: /\.js$/, loader: ['ng-annotate-loader']}
      ]
    }
  })
