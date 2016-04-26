/**
 * @file
 * A Backbone view wrapper displaying inner views as a set of jQuery UI tabs.
 */

var TabsView = Backbone.View.extend({

  initialize: function () {
    this.render();
  },

  render: function () {
    // Create the empty UL for switching between tabs.
    this.$el.prepend('<ul />').tabs({
      show: 'fadeIn'
    });
  },

  active: function () {
    var i = this.$el.tabs('option', 'active') + 1;

    return this.$el
      .children()
      .eq(i)
      .data('view');
  },

  addTab: function (view) {
    function randomID () {
      var id = '';
      for (var i = 0; i < 16; i++) {
        id += 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.charAt(Math.floor(Math.random() * 52));
      }
      return id;
    }

    // Propagate all events.
    this.listenTo(view, 'all', function () {
      this.trigger.apply(this, Array.prototype.slice.call(arguments));
    });

    view.$el
      // Store a reference to the view on the tab element.
      .data('view', view)
      .attr('id', function (undefined, id) {
        // Use the existing ID if set, otherwise generate a random one. All tabs
        // must have a unique ID.
        return id || randomID();
      });

    this.$el
      .append(view.el)
      .children('ul')
      .first()
      // Set the title attribute on the tab link so that the tests can target
      // it reliably. (Targeting by link text is unreliable.)
      .append('<li><a href="#' + view.el.id + '" title="' + view.el.title + '">' + view.el.title + '</a></li>')
      .parent()
      .tabs('refresh');

    // If this is the first tab to be added, activate it now.
    if (this.$el.children(':data(view)').not('ul').length == 2) {
      this.$el.tabs('option', 'active', 0);
    }
  },

  reset: function () {
    // Reset all inner views.
    this.$el.children(':gt(0):data(view)').each(function () {
      $(this).data('view').reset();
    });
  }

});
