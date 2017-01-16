(function ($, Drupal) {

    function setSetting (container, setting, value) {
        $(container)
            .find('input[name $= "settings[' + setting + ']"]')
            .val( value )
            .trigger('change')
            .trigger('keyup');
    }

    function setSearchStatus (el, status) {
        // The search status element is created by onAutoCompleteCreate().
        var $status = $(el).closest('div').children('.search-status');

        if (status) {
            $status.text( Drupal.t(status) );
        }
        else {
            $status.empty();
        }
    }

    function icon (url) {
        return 'url(' + Drupal.url(url) + ')';
    }

    Drupal.behaviors.entitySearch = {

        initialize: function () {
            this.style.backgroundImage = icon('core/misc/throbber-inactive.png');
            $(this).closest('div').append('<p class="search-status" />');
        },

        onSearchStart: function () {
          this.style.backgroundImage = icon('core/misc/throbber-active.gif');
          setSearchStatus(this, null);
          setSetting(this.form, 'entity_type', null);
          setSetting(this.form, 'entity_id', null);
        },

        onSearchEnd: function (event, ui) {
            this.style.backgroundImage = icon('core/misc/throbber-inactive.png');

            if (ui.content.length == 0) {
                setSearchStatus(this, 'Search returned no results');
            }
        },

        onSelect: function (event, ui) {
            event.preventDefault();

            // Get the entity type and ID from the value.
            var match = ui.item.value.match(/:([a-z0-9_]+)\/([0-9]+):/);

            setSetting(this.form, 'entity_type', match[1]);
            setSetting(this.form, 'entity_id', match[2]);

            $(this).val( ui.item.label ).trigger('change');
        },

        onSearchKeyUp: function () {
            if (this.value.length == 0) {
                setSetting(this.form, 'entity_type', null);
                setSetting(this.form, 'entity_id', null);
                setSearchStatus(this, null);
            }
        },

        attach: function (context) {
            $(context)
                .find('[data-entity-search]')
                .autocomplete({
                    source: Drupal.url('lightning/content-index'),
                    create: this.initialize,
                    search: this.onSearchStart,
                    response: this.onSearchEnd,
                    select: this.onSelect
                })
                .on('keyup', this.onSearchKeyUp);
        }

    };

})(jQuery, Drupal);
