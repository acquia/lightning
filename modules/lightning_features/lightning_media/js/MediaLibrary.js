(function ($, Drupal, Backbone) {
  "use strict";

  window.MediaLibrary = Backbone.View.extend({

    widget: null,

    events: {
      'dialogclose': 'onDialogClose'
    },

    attributes: {
      class: 'media-library'
    },

    /**
     * Event triggered when a tab is chosen.
     */
    onTabActivate: function (event, ui) {
      this.widget = this[ui.newPanel.prop('id')];
    },

    /**
     * Event triggered when the jQuery UI tab set is created.
     */
    onTabCreate: function (event, ui) {
      this.widget = this[ui.panel.prop('id')];
    },

    /**
     * Event triggered when an uploaded file is saved as a media entity
     * by the upload widget.
     */
    onUploadSave: function (response) {
      this.library.backend.unshift(new Backbone.Model(response));
    },

    /**
     * Event triggered when a jQuery UI dialog box is closed.
     */
    onDialogClose: function () {
      this.widget.finalize();
      this.$el.tabs('option', 'active', 0);
    },

    initialize: function () {
      this.library = new EntityGrid({
        backend:
          new MediaLibraryBackend([], { baseUrl: Drupal.url('media-library') })
      });

      this.upload = new Uploader({
        url: Drupal.url('lightning/upload')
      });
      this.listenTo(this.upload, 'save', this.onUploadSave);

      this.render();
    },

    randomId: function () {
      var text = '';
      for (var i = 0; i < 8; i++) {
        text += 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.charAt(Math.floor(Math.random() * 52));
      }
      return text;
    },

    render: function () {
      var nav = document.createElement('ul');

      this.library.$el
        .addClass('library')
        .prop('id', this.randomId())
        .appendTo(this.el);

      $('<li><a href="#' + this.library.el.id + '">' + Drupal.t('Library') + '</a></li>')
        .appendTo(nav);

      this.upload.$el
        .addClass('upload')
        .prop('id', this.randomId())
        .appendTo(this.el);

      $('<li><a href="#' + this.upload.el.id + '">' + Drupal.t('Upload') + '</a></li>')
        .appendTo(nav);

      this.$el.prepend(nav).tabs({
        activate: this.onTabActivate.bind(this),
        create: this.onTabCreate.bind(this),
        show: 400
      });
    }

  });

})(jQuery, Drupal, Backbone);
