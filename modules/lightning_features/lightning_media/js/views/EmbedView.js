/**
 * @file
 * A Backbone view for creating media entities from embed codes.
 */

var EmbedView = Backbone.View.extend({

  attributes: {
    class: 'embed'
  },

  events: {
    'change textarea': function (event) {
      this.model.set('embed_code', event.target.value);
    },

    'keyup textarea': _.debounce(function (event) {
      this.$(event.target).change();
    }, 600)
  },

  initialize: function () {
    // Prevent the user from changing the embed code after an AJAX request has begun.
    this.listenTo(this.model, 'request', function () {
      this.$('textarea').prop('disabled', true);
    });

    // Enable the textarea once the model has been synced or destroyed.
    this.listenTo(this.model, 'sync destroy', function () {
      this.$('textarea').prop('disabled', false);
    });

    // Display the preview once the model has been synced.
    this.listenTo(this.model, 'sync', function (model) {
      var element = this.$('.preview').get(0);
      element.innerHTML = model.get('preview');
      Drupal.attachBehaviors(element, drupalSettings);
    });

    // Clear the preview once the model has been destroyed.
    this.listenTo(this.model, 'destroy', this.clearPreview);

    this.render();
  },

  clearPreview: function () {
    var element = this.$('.preview').get(0);
    Drupal.detachBehaviors(element, drupalSettings, 'unload');
    element.innerHTML = '';
  },

  reset: function () {
    this.$('textarea').prop('value', '');
    this.clearPreview();
  },

  render: function () {
    this.$el.append('<textarea id="__embed_code" placeholder="' + Drupal.t('Enter a URL or embed code...') + '" />');
    this.$el.append('<div class="preview" />');
  }

});
