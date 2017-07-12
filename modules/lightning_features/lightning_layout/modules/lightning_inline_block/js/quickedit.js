(function (Drupal) {

  Drupal.behaviors.quickEdit4PanelsIPE = {

    attach: function () {
      var ipe = Drupal.panels_ipe.app_view;

      Drupal.quickedit.collections.entities.on('change:state', function () {
        console.log(arguments);
      });

      /* Drupal.quickedit.collections.entities.on('add', function (model) {
        if (model.get('entityID').indexOf('inline_block_content/') === 0) {
          model.on('change:state', function (undefined, state) {
            if (state === 'closed') {
              ipe.model.set('unsaved', true);

              ipe.tabsView.render();
            }
          });
        }
      }); */
    }

  };

})(Drupal);
