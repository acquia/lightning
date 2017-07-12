(function (Drupal) {

  Drupal.AjaxCommands.prototype.update_panels_ipe = function (ajax, response)
  {
    if (response.attributes)
    {
      Drupal.panels_ipe.app_view.model.set(response.attributes).tabsView.render();
    }
  };

})(Drupal);
