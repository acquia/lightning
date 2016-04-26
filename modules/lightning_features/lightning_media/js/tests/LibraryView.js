/**
 * @file
 * Contains unit tests of the LibraryView class.
 */

describe('LibraryView', function () {

  beforeEach(function () {
    this.backend = new LibraryConnector([], { baseUrl: '/' });
    this.view = new LibraryView({
      backend: this.backend
    });
  });

  it('should toggle the waiting class on the footer when the backend is fetching', function () {
    this.backend.trigger('request');
    expect(this.view.$('footer').hasClass('waiting')).toBe(true);

    this.backend.trigger('sync');
    expect(this.view.$('footer').hasClass('waiting')).toBe(false);
  });

  it('should trigger a search when the search box value is changed', function () {
    spyOn(this.backend, 'search');

    this.view.$('header input').val('Foo').trigger('change');
    expect(this.backend.search).toHaveBeenCalledWith('Foo');
  });

  it('should trigger the place event when the Place button is clicked', function () {
    spyOn(this.view, 'trigger');

    this.view.$('footer button').click();
    expect(this.view.trigger).toHaveBeenCalledWith('place', jasmine.any(Object), this.view);
  });

  it('should load more items when the footer fires the appear event', function () {
    spyOn(this.backend, 'loadMore');

    this.view.$('.load-more').trigger('appear');
    expect(this.backend.loadMore).toHaveBeenCalled();
  });

});
