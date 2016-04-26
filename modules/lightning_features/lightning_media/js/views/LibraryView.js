/**
 * @file
 * A Backbone view for browsing existing media entities.
 */

var LibraryView = Backbone.View.extend({

  attributes: {
    class: 'library'
  },

  events: {
    'change header select': function (event) {
      var bundle = jQuery(event.target).val();
      this.backend.filterByBundle(bundle);
    },

    'change header input': function (event) {
      this.backend.search(event.target.value);
    },

    'keyup header input': _.debounce(function (event) {
      jQuery(event.target).trigger('change');
    }, 600),

    'click footer button': function () {
      this.trigger('place', this.collectionView.getSelectedModel(), this);
    },

    'appear .load-more': function () {
      this.backend.loadMore();
    }
  },

  initialize: function (options) {
    this.backend = options.backend;

    this.listenTo(this.backend, 'request sync', function () {
      this.$('footer').toggleClass('waiting');
    });

    // The view used to render each media item in the library.
    var _thumbnailView = Backbone.View.extend({
      initialize: function (options) {
        this.el.innerHTML = options.model.get('thumbnail');
      }
    });

    this.collectionView = new Backbone.CollectionView({
      collection: this.backend,
      el: document.createElement('ul'),
      emptyListCaption: Drupal.t('There are no items to display.'),
      modelView: _thumbnailView
    });

    this.render();

    // The backend may have loaded asynchronously, so fire a reset event to
    // account for that.
    this.backend.trigger('reset', this.backend, {});

    // options.bundles is an optional promise wrapping an array of [id, label]
    // pairs for all filterable media bundles.
    if (options.bundles) {
      function _addBundleOption (id, label) {
        this.$('header select')
          .append('<option value="' + id + '">' + label + '</option>')
          .parent()
          .show();
      }
      var self = this;

      options.bundles.then(function (bundles) {
        _.each(bundles, function (bundle) {
          _addBundleOption.apply(self, bundle);
        });
      });
    }
  },

  render: function () {
    this.$el.append(['<header />', this.collectionView.el, '<footer />']);

    this.$('header').append([
      '<div><input type="search" class="search" placeholder="' + Drupal.t('Search') + '" /></div>',
      // The wrapper should be hidden initially, and displayed as soon as we
      // add a bundle. See private _addBundleOption function in initialize().
      '<div style="display: none;"><select id="__bundle"><option>' + Drupal.t('- all -') + '</option></select></div>'
    ]);

    this.$('footer').append('<div><button>' + Drupal.t('Place') + '</button></div>');

    // Render the collection view.
    this.collectionView.render();

    // Wrap the collection view in a scrollable DIV and add an element to
    // trigger infinite scrolling.
    this.collectionView.$el.wrap('<div class="scroll"></div>').parent().append('<div class="load-more"></div>');
  },

  reset: function () {
    // NOP
    // This function is only here for interface conformance.
  }

});
