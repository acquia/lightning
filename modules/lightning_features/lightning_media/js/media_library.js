(function ($, Drupal, CKEDITOR) {

    "use strict";

    CKEDITOR.plugins.add('media_library', {
        requires: 'drupalentity',

        beforeInit: function (editor) {
            _.each(editor.config.mediaLibrary.buttons, function (button) {
                editor.ui.addButton(button.id, {
                    label: button.label,
                    data: button,
                    click: function (editor) {
                        editor.execCommand('media_library', this.data);
                    },
                    icon: button.image
                });
            });

            editor.addCommand('media_library', {
                exec: function(editor, data) {
                    // The modal dialog is opened by Drupal's Ajax API, which means there's no way
                    // to attach a handler to its 'open' event (well, you CAN, but it will be
                    // completely scopeless and thus more useless than a sack of moldy potatoes).
                    // This elegant kludge attaches an event handler that reacts to the creation
                    // of any jQuery UI dialog, but -- thanks to the magic of $.one() -- it will
                    // only execute once, then destroy itself. This isn't ideal, but it allows us
                    // to handle the click events only in the dialog we're about to open, rather
                    // than all of them. Boo-yah!
                    $(window).one('dialogcreate', function (event) {
                        $(event.target).on('click', 'drupal-entity', function (event) {
                            editor.insertHtml( $(this).clone().empty().get(0).outerHTML );
                            $(event.delegateTarget).dialog('close');
                        });
                    });

                    Drupal.ckeditor.openDialog(editor, Drupal.url('media-library'), {}, function() {}, {
                        title: data.label
                    });
                }
            });
        }
    });

})(jQuery, Drupal, CKEDITOR);
