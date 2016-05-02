/**
 * @file
 * A media library plugin for CKEditor with upload and embed support.
 */

(function ($, Drupal, CKEDITOR) {

  "use strict";

  CKEDITOR.plugins.add('media_library', {
    requires: 'drupalentity',

    afterInit: function (editor) {
      _.each(editor.config.mediaLibrary.buttons, function (button) {
        // Trick Entity Embed into thinking this button was defined by, well,
        // Entity Embed.
        editor.config.DrupalEntity_buttons[button.id] = button;
      });
    },

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

        exec: function (editor) {
          new MediaCreator(editor)
            .createLibrary('lightning/media/library', 'lightning/media/media_bundle')
            .createUpload('lightning/upload')
            .createEmbed('lightning/embed-code')
            .view.on('place save', function () {
              this.$el.dialog('close');
            })
            .$el.dialog({
              modal: true,
              title: Drupal.t('Media Library'),
              width: '60%'
            });
        }

      });
    }
  });

})(jQuery, Drupal, CKEDITOR);
