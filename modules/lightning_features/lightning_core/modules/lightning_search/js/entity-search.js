(function ($, Drupal) {

    Drupal.behaviors.entitySearch = {

        attach: function (context) {
            $(context)
                .find('[data-entity-search]')
                .autocomplete({
                    source: Drupal.url('lightning/content-index'),
                    select: function (event, ui) {
                        event.preventDefault();

                        // Get the entity type and ID from the value.
                        var match = ui.item.value.match(/:([a-z0-9_]+)\/([0-9]+):/);

                        $(this.form)
                            .find('input[name$="settings[entity_type]"]')
                            .val(match[1]);

                        $(this.form)
                            .find('input[name$="settings[entity_id]"]')
                            .val(match[2]);

                        $(this).val(ui.item.label).trigger('change');
                    }
                })
                .on('change', function () {
                    var $elements = $(this.form)
                        .find('input[name$="settings[entity_type]"], input[name$="settings[entity_id]"]');

                    if (this.value.length === 0) {
                        $elements.prop('value', null);
                    }
                    $elements.trigger('keyup');
                });
        }

    };

})(jQuery, Drupal);
