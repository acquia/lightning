(function ($, Drupal) {

  Drupal.behaviors.lightningdemo = {
    attach: function(context, settings) {

    var text = $('.left-off-canvas-menu > .stack.button-group > li.active > a').contents().filter(function() {
             return this.nodeType == 3;
           }).text().replace('View','');;

	$( document ).ready(function() {
 	   $(".activestate").html(document.createTextNode('(Currently Viewing: ' + text + ')'));

   });
  }
};

})(jQuery, Drupal);


