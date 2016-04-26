/**
 * @file
 * A Backbone view wrapper providing controls to save to the media library.
 */

var SaveView = Backbone.View.extend({

  events: {
    'click footer button': function () {
      // Let external code decide whether and how to save the model.
      this.trigger(this.isSaving() ? 'save' : 'place', this.model, this);
    }
  },

  isSaving: function () {
    return this.$('footer input[type = "checkbox"]').prop('checked');
  },

  initialize: function (options) {
    this.view = options.view;

    this.listenTo(this.model, 'sync', function () {
      this.$('footer').fadeIn();
    });

    // Don't try to cleverly bind this.reset directly when calling listenTo()
    // -- it will never be called that way, apparently. The tests don't lie!
    this.listenTo(this.model, 'destroy', function () {
      this.reset();
    });

    this.render();
  },

  render: function () {
    this.$el
      .append([this.view.el, '<footer />'])
      .children()
      .last()
      .append('<div><label><input type="checkbox" />' + Drupal.t('Save to my media library') + '</label></div>')
      .append('<div><button>' + Drupal.t('Save') + '</button></div>')
      .hide();

    this.el.title = this.view.el.title;
    this.view.el.title = '';
  },

  reset: function () {
    // Clear the checkbox and hide the footer.
    this.$('footer input[type="checkbox"]')
      .prop('checked', false)
      .closest('footer')
      .hide();

    // Reset the wrapped view as well.
    this.view.reset();
  }

});
