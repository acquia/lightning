(function ($) {

  Drupal.behaviors.inlineBlockForm = {

    attach: function (context) {
      var elements = context.querySelector('form.block-content-form').elements;

      $(elements.global).on('change', function () {
        var title = $(elements['info[0][value]']);

        if (this.checked) {
          title
            .prop('required', true)
            .attr('aria-required', 'true')
            .addClass('required')
            .prop('labels')
            .forEach(function (label) {
              label.classList.add('form-required');
            });
        }
        else {
          title
            .prop('required', false)
            .attr('aria-required', 'false')
            .removeClass('required')
            .prop('labels')
            .forEach(function (label) {
              label.classList.remove('form-required');
            });
        }
      }).trigger('change');
    }

  };

})(jQuery);
