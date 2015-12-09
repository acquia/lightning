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

    getActiveWidget: function (element) {
      switch (true) {
        case element === this.library.el:
          return this.library;
        case element === this.upload.el:
          return this.upload;
        default:
          break;
      }
    },

    /**
     * Event triggered when a tab is chosen.
     */
    onTabActivate: function (event, ui) {
      this.widget = this.getActiveWidget(ui.newPanel.get(0));
    },

    /**
     * Event triggered when the jQuery UI tab set is created.
     */
    onTabCreate: function (event, ui) {
      this.widget = this.getActiveWidget(ui.panel.get(0));
    },

    /**
     * Event triggered when a jQuery UI dialog box is closed.
     */
    onDialogClose: function () {
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
      this.listenTo(this.upload.model, 'sync', function (model) {
        this.library.backend.unshift(model);
      });

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
        .prop('id', this.randomId())
        .appendTo(this.el);

      $('<li><a href="#' + this.library.el.id + '">' + Drupal.t('Library') + '</a></li>')
        .appendTo(nav);

      this.upload.$el
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
