(function ($, Backbone, _, Drupal, drupalSettings) {
  "use strict";

  window.EmbedCode = Backbone.View.extend({

    attributes: {
      class: 'embed-code'
    },

    events: {

      'change textarea': function (event) {
        var self = this;
        var preview = $('.preview', this.el).get(0);

        function onDestroy (model) {
          preview.innerHTML = '';
          model.clear();
          $(self.footer).hide();
        }

        var embed_code = event.target.value;
        if (embed_code) {
          Drupal.ajax({
            url: this.model.url(),
            submit: {
              embed_code: embed_code
            }
          })
          .execute()
          .then(function (response) {
            preview.innerHTML = response.preview;
            Drupal.attachBehaviors(preview, drupalSettings);

            self.model.set(response);
            $(self.footer).show();
          });
        }
        else {
          this.model.destroy({ success: onDestroy });
        }
      },

      'keyup textarea': _.debounce(function (e) { $(e.target).change() }, 600)

    },

    initialize: function (options) {
      this.model = new Backbone.Model();
      this.model.urlRoot = options.url;

      this.toLibrary = $('<input type="checkbox" />').get(0);
      this.footer = document.createElement('footer');
      this.render();
    },

    render: function () {
      $('<textarea />').attr('placeholder', Drupal.t('Enter a URL or embed code...')).appendTo(this.el);
      this.$el.append('<div class="preview" />');

      $('<label />')
      .html(Drupal.t('Save this to my media library'))
      .prepend(this.toLibrary)
      .appendTo(this.footer)
      .parent()
      .hide()
      .appendTo(this.el);
    },

    reset: function () {
      this.toLibrary.checked = false;
      $(this.footer).hide();

      $('textarea', this.el).val('');
      $('.preview', this.el).empty();

      var clone = this.model.clone();
      this.model.clear();
      return clone;
    },

    finalize: function () {
      var reset = this.reset.bind(this);
      return this.toLibrary.checked ? this.model.save().then(reset) : Promise.resolve(reset());
    }

  });

})(jQuery, Backbone, _, Drupal, drupalSettings);
