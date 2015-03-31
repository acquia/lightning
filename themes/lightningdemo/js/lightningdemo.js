(function ($, Drupal) {

  Drupal.behaviors.lightningdemo = {
    attach: function(context, settings) {
      //sometimes the page is shorter than the off canvas moderation, so set a min height based on whatever is in the offcanvas
      var offcanvasheight = 0; $(".left-off-canvas-menu").children().each(function(){ offcanvasheight += $(this).outerHeight(); });
      $('.off-canvas-wrap, .left-off-canvas-menu').css('min-height', offcanvasheight + 'px');
    }
  };

})(jQuery, Drupal);
