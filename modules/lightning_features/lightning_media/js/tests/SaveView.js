/**
 * @file
 * Contains unit tests of the SaveView class.
 */

describe('SaveView', function () {

  beforeEach(function () {
    var _view = new Backbone.View({
      attributes: {
        title: 'Foobaz'
      }
    });
    this.view = new SaveView({
      view: _view,
      model: new Backbone.Model()
    });
  });

  it('should take on the title of the inner view', function () {
    expect(this.view.el.title).toBe('Foobaz');
    expect(this.view.view.el.title).toBeFalsy();
  });

  it('should have all the necessary HTML elements when rendered', function () {
    expect(this.view.$('label input[type="checkbox"]').length).toBe(1);
    expect(this.view.$('button').length).toBe(1);
  });

  it('should display the footer when the model has been synced', function () {
    // @TODO: It'd be preferable here to assert that the footer is visible
    // (so as not to be coupled to an implementation detail), but I can't figure
    // out how to make Jasmine wait for the fade to complete.
    spyOn(jQuery.prototype, 'fadeIn');

    expect(this.view.$('footer:visible').length).toBe(0);
    this.view.model.trigger('sync');
    expect(jQuery.prototype.fadeIn).toHaveBeenCalled();
  });

  it('should reset itself when the model is destroyed', function () {
    spyOn(this.view, 'reset');

    this.view.model.trigger('destroy');
    expect(this.view.reset).toHaveBeenCalled();
  });

  it('should hide the footer, uncheck the checkbox, and reset the inner view when reset', function () {
    this.view.view.reset = new Function();
    spyOn(this.view.view, 'reset');
    this.view.model.trigger('sync');
    this.view.$('input[type="checkbox"]').prop('checked', true);
    this.view.reset();

    expect(this.view.$('footer:visible').length).toBe(0);
    expect(this.view.$('input[type="checkbox"]').prop('checked')).toBe(false);
    expect(this.view.view.reset).toHaveBeenCalled();
  });

  it('should fire the place event if the checkbox is unchecked', function () {
    spyOn(this.view, 'trigger');

    this.view.$('button').click();
    expect(this.view.trigger).toHaveBeenCalledWith('place', this.view.model, this.view);
  });

  it('should fire the save event if the checkbox is checked', function () {
    spyOn(this.view, 'trigger');

    this.view.$('input[type="checkbox"]').prop('checked', true);
    this.view.$('button').click();
    expect(this.view.trigger).toHaveBeenCalledWith('save', this.view.model, this.view);
  });

});
