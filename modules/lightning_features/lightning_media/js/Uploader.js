(function ($, Drupal, Backbone, Dropzone) {
  "use strict";

  window.Uploader = Backbone.View.extend({

    attributes: {
      class: 'upload'
    },

    /**
     * The uploaded file entity, as returned from the server.
     */
    upload: null,

    /**
     * Event triggered when a file is successfully uploaded to the server.
     */
    onUploadSuccess: function (file, response) {
      this.upload = response;
      $(this.footer).fadeIn();
    },

    /**
     * Event triggered when a file is removed from the dropzone.
     */
    onUploadRemove: function () {
      if (this.upload) {
        $.ajax(this.dropzone.options.url + '/' + this.upload.id, {
          method: 'DELETE',
          success: this.onUploadDelete.bind(this)
        });
      }
    },

    /**
     * Callback function after an uploaded file has been deleted from the
     * server.
     */
    onUploadDelete: function () {
      this.upload = null;
      $(this.footer).hide();
    },

    /**
     * Callback function after an uploaded file has been saved as a media
     * entity on the server.
     */
    onUploadSave: function (response) {
      this.toLibrary.checked = false;
      this.trigger('save', response);
    },

    initialize: function (options) {
      // Due to a bug in Dropzone, the dict* messages are not displayed
      // when the dropzone is created programmatically -- unless, that is,
      // the target element already has the dropzone class.
      // @see https://github.com/enyo/dropzone/issues/655
      var dzElement = $('<div class="dropzone"></div>').get(0);

      this.dropzone = new Dropzone(dzElement, {
        acceptedFiles: 'image/*',
        addRemoveLinks: true,
        dictDefaultMessage: Drupal.t('Click or drag and drop an image here to upload it.'),
        dictFallbackMessage: Drupal.t('Click here to upload an image'),
        maxFiles: 1,
        thumbnailHeight: null,
        thumbnailWidth: null,
        url: options.url
      });
      this.dropzone.on('success', this.onUploadSuccess.bind(this));
      this.dropzone.on('removedfile', this.onUploadRemove.bind(this));

      this.toLibrary = $('<input type="checkbox" />').get(0);
      this.footer = document.createElement('footer');
      this.render();
    },

    render: function () {
      this.$el.append(this.dropzone.element);

      $('<label />')
        .html(Drupal.t('Save this image to my media library'))
        .prepend(this.toLibrary)
        .appendTo(this.footer)
        .parent()
        .hide()
        .appendTo(this.el);
    },

    finalize: function () {
      if (this.upload && this.toLibrary.checked) {
        $.ajax(this.dropzone.options.url + '/' + this.upload.id, {
          method: 'PUT',
          success: this.onUploadSave.bind(this)
        });
      }
      this.upload = null;
      this.dropzone.removeAllFiles();
    },

    getEmbedCode: function () {
      return this.upload ? this.upload.thumbnail : '';
    }

  });

})(jQuery, Drupal, Backbone, Dropzone);
