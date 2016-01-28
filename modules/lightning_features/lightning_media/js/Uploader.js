(function ($, Drupal, Backbone, Dropzone) {
  "use strict";

  window.Uploader = Backbone.View.extend({

    attributes: {
      class: 'upload'
    },

    initialize: function (options) {
      this.model = new Backbone.Model();
      this.model.urlRoot = options.url;

      var dzElement = document.createElement('div');
      // The dict* messages are not displayed when the dropzone is created
      // programmatically unless the target element already has the dropzone
      // class. @see https://github.com/enyo/dropzone/issues/655
      dzElement.classList.add('dropzone');

      this.dz = new Dropzone(dzElement, {
        acceptedFiles: 'image/*',
        addRemoveLinks: true,
        dictDefaultMessage: Drupal.t('Click or drag and drop an image here to upload it.'),
        dictFallbackMessage: Drupal.t('Click here to upload an image'),
        maxFiles: 1,
        thumbnailHeight: null,
        thumbnailWidth: null,
        url: options.url
      });

      // The dropzone's event handlers need access to this.
      var self = this;

      // This handler is never detached, so it can be safely anonymous.
      this.dz.on('success', function (file, response) {
        self.model.set(response);
        $(self.footer).fadeIn();
      });

      // This handler is detached before the model is finalized, then
      // re-attached afterwards, so it cannot be anonymous.
      this.onUploadRemove = function () {
        self.model.destroy({
          success: function (model) {
            model.clear();
            $(self.footer).hide();
          }
        });
      };
      this.dz.on('removedfile', this.onUploadRemove);

      this.toLibrary = $('<input type="checkbox" />').get(0);
      this.footer = document.createElement('footer');
      this.render();
    },

    render: function () {
      this.$el.append(this.dz.element);

      $('<label />')
        .html(Drupal.t('Save this image to my media library'))
        .prepend(this.toLibrary)
        .appendTo(this.footer)
        .parent()
        .hide()
        .appendTo(this.el);
    },

    finalize: function () {
      if (this.model.id) {
        var self = this;

        // Stop listening to removedfile events until the model is finalized.
        this.dz.off('removedfile', this.onUploadRemove).removeAllFiles();

        return (this.toLibrary.checked ? this.model.save() : Promise.resolve(this.model)).then(function () {
          self.toLibrary.checked = false;
          $(self.footer).hide();

          var clone = self.model.clone();
          self.model.clear();
          self.dz.on('removedfile', self.onUploadRemove);

          return clone;
        });
      }
      else {
        return Promise.reject();
      }
    }

  });

})(jQuery, Drupal, Backbone, Dropzone);
