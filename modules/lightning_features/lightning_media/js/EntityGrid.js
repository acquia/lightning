(function (Drupal, Backbone) {
  "use strict";

  window.EntityGrid = Backbone.View.extend({

    searchTimeoutId: -1,

    events: {
      'keyup header input[type = "search"]': 'onSearch'
    },

    /**
     * Event triggered when the search field is changed.
     */
    onSearch: function (event) {
      clearTimeout(this.searchTimeoutId);
      this.searchTimeoutId = setTimeout(function () { this.backend.search(event.target.value); }.bind(this), 400);
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
        modelView: DrupalEntity
      });

      this.render();
    },

    render: function () {
      this.header.appendChild(this.search);
      this.el.appendChild(this.header);
      this.innerView.render();
      this.el.appendChild(this.innerView.el);
    },

    finalize: function () {
      // NOP. This is for interface conformance with Uploader.
    },

    getEmbedCode: function () {
      return this.innerView.getSelectedModel({ by: 'view' }).$el.clone().empty().prop('outerHTML');
    }

  });

})(Drupal, Backbone);
