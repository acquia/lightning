/**
 * @file
 * Behaviors for the Entity Browser-based Media Library.
 */

(function ($, _, Drupal) {
  "use strict";

  Drupal.behaviors.entityBrowserSelection = {

    attach: function (context) {
      var $all = $('[data-selectable]', context);

      $all.on('click', function () {
        var $input = $('input[name^="entity_browser_select"]', this);

        $('input[name ^= "entity_browser_select"]', this).prop('checked', true);
        $(this).addClass('selected');

        if ($input.is('[type = "radio"]')) {
          $all.not(this).removeClass('selected');
        }
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
