/**
 * @file
 * Backbone model for media entities created from an embed code.
 */

var Embed = Backbone.Model.extend({

  initialize: function () {
    this.on('change:embed_code', function (model, value) {
      if (value) {
        if (model.isNew()) {
          // It's the server's job to return a preview, which may include commands for
          // Drupal's AJAX framework. We need to perform this request with the AJAX
          // framework in order for those commands to be handled properly.
          var ajax = Drupal.ajax({
            url: model.url(),
            submit: {
              embed_code: value
            }
          });

          // In Drupal 8.0.x, this requires patch #2637194.
          var xhr = ajax.execute().then(function (response) {
            // Any AJAX framework commands in the response have already been executed by
            // this point, so don't pollute the model with them.
            delete response.commands;
            model.set(response).trigger('sync', model, response, {});
          });

          model.trigger('request', model, xhr, {});
        }
        else {
          model.destroy().then(function () {
            model.set('embed_code', value);
          });
        }
      }
      else {
        model.destroy();
      }
    });

    this.on('destroy', function (model) {
      model.clear({ silent: true });
    });
  }

});
