(function ($, Backbone) {
  "use strict";

  window.MediaLibraryBackend = Backbone.Collection.extend({

    queryString: {
      page: 0
    },

    initialize: function (models, options) {
      this.baseUrl = options.baseUrl;
      this.doReset();
    },

    doFetch: function (options) {
      this.url = this.baseUrl + '?' + $.param(this.queryString);
      this.fetch(options);
    },

    doReset: function () {
      this.doFetch({ reset: true });
    },

    fetchMore: function () {
      this.queryString.page++;
      this.doFetch({ remove: false });
    },

    search: function (keywords) {
      if (keywords) {
        this.queryString.keywords = keywords;
      }
      else {
        delete this.queryString.keywords;
      }

      this.queryString.page = 0;
      this.doReset();
    },

    filterByBundle: function (bundle) {
      if (bundle) {
        this.queryString.bundle = bundle;
      }
      else {
        delete this.queryString.bundle;
      }

      this.queryString.page = 0;
      this.doReset();
    }

  });

})(jQuery, Backbone);
