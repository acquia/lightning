/**
 * @file
 * A Backbone view for drag and drop file uploads.
 */

var UploadView = Backbone.View.extend({

  initialize: function (options) {
    this.model = new Backbone.Model();

    // The Dropzone's event handlers need access to the model.
    var model = this.model;
    model.urlRoot = options.url;
    model.on('destroy', model.clear);

    // The dict* messages are not displayed when the dropzone is created
    // programmatically unless the target element already has the dropzone
    // class. @see https://github.com/enyo/dropzone/issues/655
    this.$el.addClass('dropzone');

    this.dz = new Dropzone(this.el, {
      acceptedFiles: 'image/*',
      addRemoveLinks: true,
      dictDefaultMessage: Drupal.t('Click here, or drag and drop an image to upload it.'),
      dictFallbackMessage: Drupal.t('Click here to upload an image'),
      init: function () {
        // Set a unique identifier on the hidden file field, for testing.
        this.hiddenFileInput.id = '__dropzone_' + UploadView.count++;
      },
      maxFiles: 1,
      thumbnailHeight: null,
      thumbnailWidth: null,
      url: model.urlRoot
    });

    this.dz.on('success', function (file, response) {
      model.set(response).trigger('sync', model, response, {});
    });

    this.dz.on('removedfile', function () {
      if (model.destroy) {
        model.destroy();
      }
    });

    // Set a flag so that external test code can track active XHRs.
    this.dz.on('sending', function () {
      UploadView.xhr++;
    });

    this.dz.on('complete', function () {
      UploadView.xhr--;
    });
  },

  reset: function () {
    // Know thy Ghostbusters villains...
    var gozer = this.model.destroy;
    this.model.destroy = null;
    // Clear the dropzone, canceling any upload in progress.
    this.dz.removeAllFiles(true);
    this.model.destroy = gozer;
  }

}, { count: 0, xhr: 0 });
