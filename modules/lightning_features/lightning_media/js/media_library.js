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

        exec: function(editor) {
          if (! this.hasOwnProperty('library')) {
            this.library = new MediaLibrary();
          }

          var library = this.library;
          library.$el.dialog({
            buttons: [
              {
                text: 'Place',
                click: function () {
                  editor.insertHtml(library.widget.getEmbedCode());
                  library.widget.finalize();
                  $(this).dialog('close');
                }
              }
            ],
            modal: true,
            title: Drupal.t('Media Library'),
            width: '60%'
          });
        }

      });
    }
  });

})(jQuery, Drupal, CKEDITOR);
