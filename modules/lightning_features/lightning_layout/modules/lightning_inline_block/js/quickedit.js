(function (Drupal) {

  Drupal.behaviors.inlineEntityQuickEdit = {

    attach: function () {
      function onModelStateChange (undefined, state) {
        if (state === 'closed') {
          Drupal.panels_ipe.app_view.model.set('unsaved', true).tabsView.render();
        }
      }

      if (Drupal.panels_ipe && Drupal.quickedit)
      {
        Drupal.quickedit.collections.entities.on('add', function (model) {
          if (model.get('entityID').indexOf('inline_block_content/') === 0) {
            model.on('change:state', onModelStateChange);
          }
        });
      }
    }

  };

})(Drupal);
