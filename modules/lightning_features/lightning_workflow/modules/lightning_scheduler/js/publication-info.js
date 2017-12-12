(function ($) {

  $(document)
    .find('input[name="scheduled_publication[0][value][date]"], input[name="scheduled_publication[0][value][time]"]')
    .change(function () {
      var date_element = this.name.replace('[time]', '[date]');
      var time_element = this.name.replace('[date]', '[time]');
      var date_value = this.form[date_element].value;
      var time_value = this.form[time_element].value;
      var info_element = this.form.querySelector('#scheduled-publication-info');

      if (date_value) {
        if (time_value) {
          date_value += ' ' + time_value;
        }
        date_value = new Date(date_value);
        info_element.innerHTML = ' on ' + date_value.toLocaleString();
      }
      else {
        info_element.innerHTML = '';
      }
    }).trigger('change');

})(jQuery);
