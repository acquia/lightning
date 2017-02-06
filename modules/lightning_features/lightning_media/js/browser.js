/**
 * @file
 * Behaviors for the Entity Browser-based Media Library.
 */

(function ($, Backbone, Drupal) {

  "use strict";

  var Selection = Backbone.View.extend({

    events: {
      'click [data-selectable]': 'onClick'
    },

    initialize: function () {
      // This view must be created on an element which has this attribute.
      // Otherwise, things will blow up and rightfully so.
      this.uuid = this.el.getAttribute('data-entity-browser-uuid');

      // If we're in an iFrame, reach into the parent window context to get the
      // settings for this entity browser.
      var settings = (frameElement ? parent : window).drupalSettings.entity_browser[this.uuid];

      // Assume a single-cardinality entity browser with no existing selection.
      this.count = settings.count || 0;
      this.cardinality = settings.cardinality || 1;
    },

    deselect: function (item) {
      this.$(item)
        .removeClass('selected')
        .find('input[name ^= "entity_browser_select"]')
        .prop('checked', false);
    },

    /**
     * Deselects all items in the entity browser.
     */
    deselectAll: function () {
      // Create a version of deselect() that can be called within each() with
      // this as its context.
      var _deselect = jQuery.proxy(this.deselect, this);

      this.$el
        .find('[data-selectable]')
        .each(function (undefined, item) { _deselect(item); });
    },

    select: function (item) {
      this.$(item)
        .addClass('selected')
        .find('input[name ^= "entity_browser_select"]')
        .prop('checked', true);
    },

    /**
     * Prevents any unselected items in the entity browser from being clicked.
     */
    lock: function () {
      this.$el
        .find('[data-selectable]:not(.selected)')
        .addClass('disabled');
    },

    /**
     * Allows all items in the entity browser to be clicked.
     */
    unlock: function () {
      this.$el
        .find('[data-selectable]')
        .removeClass('disabled');
    },

    /**
     * Handles click events for any item in the entity browser.
     *
     * @param {jQuery.Event} event
     */
    onClick: function (event) {
      var chosen_one = this.$(event.currentTarget);

      if (this.cardinality === 1) {
        this.deselectAll();
        this.select(chosen_one);
      }
      else if (chosen_one.hasClass('selected')) {
        this.deselect(chosen_one);
        this.count--;
        this.unlock();
      }
      else if (this.count < this.cardinality) {
        this.select(chosen_one);
        this.count++;

        if (this.count === this.cardinality) {
          this.lock();
        }
      }
    }

  });

  Drupal.behaviors.entityBrowserSelection = {

    getElement: function (context) {
      // If we're in a document context, search for the first available entity
      // browser form. Otherwise, ensure that the context is itself an entity
      // browser form.
      return $(context)[context === document ? 'find' : 'filter']('form[data-entity-browser-uuid]').get(0);
    },

    attach: function (context) {
      var element = this.getElement(context);

      if (element) {
        $(element).data('view', new Selection({ el: element }));
      }
    },

    detach: function (context) {
      var element = this.getElement(context);

      if (element) {
        var view = $(element).data('view');

        if (view instanceof Selection) {
          view.undelegateEvents();
        }
      }
    }

  };

})(jQuery, Backbone, Drupal);
