(function ($, Drupal, _, Backbone) {
  "use strict";

  var Thumbnail = Backbone.View.extend({

    initialize: function (options) {
      this.model = options.model;
    },

    render: function () {
      this.el.innerHTML = this.model.get('thumbnail');
      if (this.model.get('bundle').toLowerCase() !== 'image') {
        this.el.innerHTML += '<div>' + this.model.get('label') + '</div>';
      }
    }

  });

  window.EntityGrid = Backbone.View.extend({

    attributes: {
      class: 'library'
    },

    events: {

      'appear footer': function () {
          this.backend.fetchMore();
      },

      'keyup input[type = "search"]': _.debounce(function (event) {
        this.backend.search(event.target.value);
      }, 400)

    },

    initialize: function (options) {
      this.backend = options.backend;

      this.search = document.createElement('input');
      this.search.type = 'search';
      this.search.placeholder = Drupal.t('Search');

      this.header = document.createElement('header');

      this.innerView = new Backbone.CollectionView({
        collection: this.backend,
        el: document.createElement('ul'),
        emptyListCaption: Drupal.t('There are no items to display.'),
        modelView: Thumbnail
      });

      this.render();
    },

    render: function () {
      this.header.appendChild(this.search);
      this.el.appendChild(this.header);
      this.innerView.render();
      this.el.appendChild(this.innerView.el);
      $('<footer />').css({ height: 0, padding: 0 }).appear().appendTo(this.el);
    },

    finalize: function () {
      return Promise.resolve(this.innerView.getSelectedModel());
    }

  });

})(jQuery, Drupal, _, Backbone);
