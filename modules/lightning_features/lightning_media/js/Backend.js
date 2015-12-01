(function (Backbone) {
  "use strict";

  window.MediaLibraryBackend = Backbone.Collection.extend({

    initialize: function (models, options) {
      this.baseUrl = this.url = options.baseUrl;
      this.fetch({ reset: true });
    },

    search: function (keywords) {
      this.url = this.baseUrl;
      if (keywords) {
        this.url += '?keywords=' + keywords;
      }
      this.fetch({ reset: true });
    }

  });

})(Backbone);
