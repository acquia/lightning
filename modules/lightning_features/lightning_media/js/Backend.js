<<<<<<< HEAD
(function ($, Backbone) {
=======
(function (Backbone) {
>>>>>>> origin/8.x-1.x
  "use strict";

  window.MediaLibraryBackend = Backbone.Collection.extend({

<<<<<<< HEAD
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
=======
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
>>>>>>> origin/8.x-1.x
    }

  });

<<<<<<< HEAD
})(jQuery, Backbone);
=======
})(Backbone);
>>>>>>> origin/8.x-1.x
