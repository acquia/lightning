/**
 * @file
 * Behaviors for the Entity Browser-based Media Library.
 */

(function ($, _, Drupal) {
  "use strict";

  Drupal.behaviors.entityBrowserSelection = {

    attach: function (context) {
      // All selectable elements which should receive the click behavior.
      var $selectables = $('[data-selectable]', context);

      // Selector for finding the actual form inputs.
      var input = 'input[name ^= "entity_browser_select"]';

      $selectables.on('click', function () {
        // Select this one...
        $(this).addClass('selected').find(input).prop('checked', true);

        // ...and unselect everything else.
        $selectables.not(this).removeClass('selected').find(input).prop('checked', false);
      });
    }

  };

  Drupal.behaviors.changeOnKeyUp = {

    onKeyUp: _.debounce(function () {
      $(this).trigger('change');
    }, 600),

    attach: function (context) {
      $('.keyup-change', context).on('keyup', this.onKeyUp);
    },

    detach: function (context) {
      $('.keyup-change', context).off('keyup', this.onKeyUp);
    }

  };

})(jQuery, _, Drupal);
