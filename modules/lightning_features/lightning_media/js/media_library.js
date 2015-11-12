(function ($, _, Backbone, Drupal, CKEDITOR) {

    "use strict";

    var MediaLibrary = Backbone.View.extend({

        initialize: function (options) {
            var self = this;

            var t = this.el;
            if (t.rows.length === 0) {
                t.insertRow().insertCell(-1).colSpan = 4;
            }

            function onAdd (model) {
                var r = t.rows[t.rows.length - 1];

                if (r.cells.length === 4) {
                    t.insertRow().insertCell(-1).colSpan = 4;
                    r = r.nextSibling;
                }

                var c = r.cells[r.cells.length - 1];
                r.insertCell(-1).colSpan = (c.colSpan - 1);
                c.colSpan = 1;
                c.style.width = '25%';
                c.innerHTML = model.get('thumbnail__target_id');
                $(c).on('click', function () {
                    var el = options.editor.document.createElement('drupal-entity');

                    el.setAttribute('data-align', 'none');
                    el.setAttribute('data-embed-button', 'media_library');
                    el.setAttribute('data-entity-embed-display', 'entity_reference:entity_reference_entity_view');
                    el.setAttribute('data-entity-embed-settings', '{&quot;view_mode&quot;:&quot;default&quot;}');
                    el.setAttribute('data-entity-id', model.get('mid'));
                    el.setAttribute('data-entity-label', 'N/A');
                    el.setAttribute('data-entity-type', 'media');
                    el.setAttribute('data-entity-uuid', model.get('uuid'));

                    options.editor.insertHtml(el.getOuterHtml());
                    self.$el.dialog('close');
                });
            }

            options.collection.on('add', onAdd).fetch();
        },

    });

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
                    var source = new Backbone.Collection();
                    source.url = Drupal.url('media-library');
                    var lib = new MediaLibrary({ collection: source, el: document.createElement('table'), editor: editor });

                    lib.$el.dialog({
                        draggable: false,
                        height: '50%',
                        modal: true,
                        open: function () {
                            $(this).width('100%').css('margin', 0);
                        },
                        resizable: false,
                        title: data.label,
                        width: '60%'
                    });
                }
            });
        }
    });

})(jQuery, _, Backbone, Drupal, CKEDITOR);
