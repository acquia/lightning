/**
 * @file
 * Contains unit tests of the TabsView class.
 */

describe('TabsView', function () {

  beforeEach(function () {
    this.view = new TabsView();
  });

  it('should have an empty navigation element', function () {
    expect(this.view.$el.children('ul:first-child').length).toBe(1);
    expect(this.view.$('ul:first-child').children().length).toBe(0);
  });

  it('should accept a view as a tab', function () {
    var tab = new Backbone.View();
    this.view.addTab(tab);
    expect(this.view.$el.children().length).toBeGreaterThan(1);
    expect(this.view.$el.children('ul:first-child').children('li').length).toBe(1);
  });

  it('should be able to return the active view', function () {
    var tab = new Backbone.View();
    this.view.addTab(tab);
    this.view.$el.children('ul:first-child li:first-child a').click();
    expect(this.view.active()).toBe(tab);
  });

  it('should propagate the save event', function () {
    spyOn(this.view, 'trigger');

    var tab = new Backbone.View();
    this.view.addTab(tab);
    tab.trigger('save', {}, {});
    expect(this.view.trigger).toHaveBeenCalledWith('save', jasmine.any(Object), jasmine.any(Object));
  });

});
