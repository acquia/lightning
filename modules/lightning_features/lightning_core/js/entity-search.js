(function ($) {

    Drupal.behaviors.entitySearch = {

        attach: function (context) {
            $(context).find('input#search').autocomplete({
                select: function (event, ui) {
                    // Put the label in the text field, not the value.
                    event.preventDefault();
                    this.value = ui.item.label;

                    var match = ui.item.value.match(/:([a-z0-9_]+)\/([0-9]+):/);
                    this.form.elements['settings[entity_type]'].value = match[1];
                    this.form.elements['settings[entity_id]'].value = match[2];
                },
                source: '/viewable-content'
            })
                .on('change', function () {
                   if (this.value.length == 0) {
                       this.form.elements['settings[entity_type]'].value = null;
                       this.form.elements['settings[entity_id]'].value = null;
                   }
                });
        },

        detach: function (context) {
            $(context).find('input#search').autocomplete('destroy');
        }

    };

})(jQuery, Drupal);
