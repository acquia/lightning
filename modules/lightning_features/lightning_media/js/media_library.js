/**
 * @file
 * A media library plugin for CKEditor with upload and embed support.
 */

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
            buttons: [{
              text: 'Place',
              click: function () {
                library.widget.finalize().then(function (model) {
                  if (model.get('entity_type') === 'file') {
                    // Files are not renderable by default (not without
                    // file_entity installed and configured, anyway), so if
                    // the entity is a file, embed the thumbnail directly.
                    editor.insertHtml(model.get('thumbnail'));
                  }
                  else {
                    var el = document.createElement('drupal-entity');

                    el.setAttribute('data-align', 'none');
                    el.setAttribute('data-embed-button', 'media_library');
                    el.setAttribute('data-entity-embed-display', 'entity_reference:entity_reference_entity_view');
                    el.setAttribute('data-entity-embed-settings', '{"view_mode":"embedded"}');
                    el.setAttribute('data-entity-type', model.get('entity_type'));
                    el.setAttribute('data-entity-bundle', model.get('bundle'));
                    el.setAttribute('data-entity-id', model.id);
                    el.setAttribute('data-entity-label', model.get('label'));
                    el.setAttribute('data-entity-uuid', model.get('uuid'));

                    editor.insertHtml(el.outerHTML);
                  }
                });
                $(this).dialog('close');
              }
            }],
            modal: true,
            title: Drupal.t('Media Library'),
            width: '60%'
          });
        }

      });
    }
  });

})(jQuery, Drupal, CKEDITOR);
