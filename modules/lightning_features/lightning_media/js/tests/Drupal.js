/**
 * @file
 * Contains a testing version of the Drupal JavaScript API.
 */

var Drupal = {

  ajax: function (options) {
    return {
      execute: function () {
        return jQuery.get(options.url);
      }
    };
  },

  t: function () {
    return arguments[0];
  },

  attachBehaviors: function () {
  },

  detachBehaviors: function () {
  }

};

var drupalSettings = {};
