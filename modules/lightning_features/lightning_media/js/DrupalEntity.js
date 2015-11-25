(function (Backbone) {
  "use strict";

  window.DrupalEntity = Backbone.View.extend({

    tagName: 'drupal-entity',

    initialize: function (options) {
      this.model = options.model;
    },

    render: function () {
      this.el.setAttribute('data-align', 'none');
      this.el.setAttribute('data-embed-button', 'media_library');
      this.el.setAttribute('data-entity-embed-display', 'entity_reference:entity_reference_entity_view');
      this.el.setAttribute('data-entity-embed-settings', '{"view_mode":"embedded"}');
      this.el.setAttribute('data-entity-type', this.model.get('entity_type'));
      this.el.setAttribute('data-entity-bundle', this.model.get('bundle'));
      this.el.setAttribute('data-entity-id', this.model.id);
      this.el.setAttribute('data-entity-label', this.model.get('label'));
      this.el.setAttribute('data-entity-uuid', this.model.get('uuid'));
      this.el.innerHTML = this.model.get('thumbnail');
    }

  });

})(Backbone);
