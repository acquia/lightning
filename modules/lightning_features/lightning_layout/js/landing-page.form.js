/**
 * @file
 * JavaScript for the simplified landing page creation form.
 */

(function ($, _, Drupal) {
  "use strict";

  Drupal.behaviors.landingPageForm = {
    attach: function (context) {
      var form = $('form#landing-page', context).get(0);

      if (form) {
        // Create a handler to derive the path from the title no more than
        // every 200 milliseconds.
        var onTitleKeyUp = _.debounce(function (event) {
          $(form.elements.path).val(function () {
            var title = event.target.value;
            if (title) {
              return '/' + title.toLowerCase().replace(/[^a-z0-9_]+/g, '-');
            }
          });
        }, 200);
        $(form.elements.title).on('keyup', onTitleKeyUp);

        // When the path element's value is changed by the user, stop listening
        // to the title field.
        $(form.elements.path).on('change', function () {
          $(form.elements.title).off('keyup', onTitleKeyUp);
        });
      }
    }
  };

})(jQuery, _, Drupal);
