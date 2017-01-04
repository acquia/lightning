(function ($) {

    Drupal.behaviors.entitySearch = {

        attach: function (context) {
            $(context)
                .find('[data-entity-search]')
                .autocomplete({
                    source: '/viewable-content',
                    select: function (event, ui) {
                        event.preventDefault();

                        this.value = ui.item.label;

                        // Get the entity type and ID from the value.
                        var match = ui.item.value.match(/:([a-z0-9_]+)\/([0-9]+):/);
                        $(this.form).find('input[name$="settings[entity_type]"]').val(match[1]);
                        $(this.form).find('input[name$="settings[entity_id]"]').val(match[2]);
                    }
                })
                .on('change', function () {
                    if (this.value.length === 0) {
                        $(this.form).find('input[name$="settings[entity_type]"], input[name$="settings[entity_id]"]').prop('value', null);
                    }
                });
        }

    };

})(jQuery, Drupal);
