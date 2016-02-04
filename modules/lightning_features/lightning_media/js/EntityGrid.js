/**
 * @file
 * A widget for choosing an existing media item from the library.
 */

(function ($, Drupal, _, Backbone) {
  "use strict";

  var Thumbnail = Backbone.View.extend({

    initialize: function (options) {
      this.model = options.model;
    },

    render: function () {
      this.el.innerHTML = this.model.get('thumbnail');
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
      }, 400),

      'change select': function (e) {
        var index = e.target.selectedIndex;
        this.backend.filterByBundle(index > 0 ? e.target.options[e.target.selectedIndex].value : null);
      }

    },

    initialize: function (options) {
      this.backend = options.backend;

      this.search = document.createElement('input');
      this.search.type = 'search';
      this.search.placeholder = Drupal.t('Search');

      this.bundleFilter = document.createElement('select');
      var all_option = document.createElement('option');
      all_option.text = Drupal.t('- all -');
      this.bundleFilter.add(all_option);

      var self = this, ajax_options = {
        headers: {
          Accept: 'application/json'
        },
        url: Drupal.url('lightning/media/media_bundle')
      };
      $.ajax(ajax_options)
        .then(function (data) {
          for (var id in data) {
            var option = document.createElement('option');
            option.value = id;
            option.text = data[id];
            self.bundleFilter.add(option);
          }
        });

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
      // The CSS ID is applied for testing purposes.
      this.bundleFilter.id = 'lightning-media-bundle';
      $(this.header).append([this.search, this.bundleFilter]).appendTo(this.el);
      this.innerView.render();
      this.el.appendChild(this.innerView.el);
      $('<footer />').css({ height: 0, padding: 0 }).appear().appendTo(this.el);
    },

    finalize: function () {
      return Promise.resolve(this.innerView.getSelectedModel());
    }

  });

})(jQuery, Drupal, _, Backbone);
