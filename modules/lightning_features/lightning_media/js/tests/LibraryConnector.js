/**
 * @file
 * Contains unit tests of the LibraryConnector class.
 */

describe('LibraryConnector', function () {

  beforeEach(function () {
    this.backend = new LibraryConnector([], { baseUrl: '/' });
    spyOn(this.backend, 'fetch');
  });

  it('should set the correct URL for keyword search', function () {
    this.backend.search('Bamboozle');
    expect(this.backend.url).toBe('/?page=0&keywords=Bamboozle');

    this.backend.search('');
    expect(this.backend.url).toBe('/?page=0');
    expect(this.backend.fetch).toHaveBeenCalledTimes(2);
    expect(this.backend.fetch).not.toHaveBeenCalledWith({ reset: false });
  });

  it('should set the correct URL for bundle filtering', function () {
    this.backend.filterByBundle('foo');
    expect(this.backend.url).toBe('/?page=0&bundle=foo');

    this.backend.filterByBundle('');
    expect(this.backend.url).toBe('/?page=0');
    expect(this.backend.fetch).toHaveBeenCalledTimes(2);
    expect(this.backend.fetch).not.toHaveBeenCalledWith({ reset: false });
  });

  it('should set the correct URL when loading more items', function () {
    this.backend.loadMore();
    expect(this.backend.url).toBe('/?page=1');

    this.backend.search('');
    expect(this.backend.url).toBe('/?page=0');
    expect(this.backend.fetch).toHaveBeenCalledTimes(2);
    expect(this.backend.fetch).toHaveBeenCalledWith({ reset: false });
  });

});
