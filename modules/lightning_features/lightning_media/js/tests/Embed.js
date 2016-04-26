/**
 * @file
 * Contains unit tests of the Embed model.
 */

describe('Embed', function () {

  beforeEach(function () {
    this.model = new Embed();
    // There must be a urlRoot in order to do AJAX things or we'll get errors.
    this.model.urlRoot = '/';

    jasmine.Ajax.install();
  });

  afterEach(function () {
    jasmine.Ajax.uninstall();
  });

  it('should destroy itself if given a falsy embed code', function () {
    // Set up assertions.
    spyOn(this.model, 'destroy');

    // Put the model into a known state.
    this.model.set('embed_code', false);

    // Assert stuff.
    expect(this.model.destroy).toHaveBeenCalledTimes(1);
  });

  it('should sync to the server if given a truthy embed code', function () {
    // Set up assertions.
    sinon.spy(Drupal, 'ajax');
    sinon.spy(this.model, 'trigger');

    // Put the model into a known state.
    this.model.set('embed_code', 'foobar');
    jasmine.Ajax.requests.mostRecent().respondWith({
      status: 200,
      contentType: 'application/json',
      responseText: '{"id": 31, "commands": []}'
    });

    // Assert stuff.
    expect(Drupal.ajax.callCount).toBe(1);
    expect(this.model.id).toBe(31);
    expect(this.model.get('commands')).toBeUndefined();
    expect(this.model.trigger.withArgs('request').callCount).toBe(1);
    expect(this.model.trigger.withArgs('sync').callCount).toBe(1);

    // Clean up.
    Drupal.ajax.restore();
  });

  it('should sync to the server when the embed code is changed', function () {
    // Set up assertions.
    sinon.spy(this.model, 'destroy');
    sinon.spy(this.model, 'set');

    // Put the model into a known state.
    this.model.set('embed_code', 'foobar');
    jasmine.Ajax.requests.mostRecent().respondWith({
      status: 200,
      contentType: 'application/json',
      responseText: '{"id": 31}'
    });

    this.model.set('embed_code', 'wambooli');
    jasmine.Ajax.requests.mostRecent().respondWith({
      status: 200,
      contentType: 'application/json',
      responseText: '{}'
    });

    // Assert stuff.
    expect(this.model.destroy.callCount).toBe(1);
    expect(this.model.set.withArgs('embed_code', 'foobar').callCount).toBe(1);
    expect(this.model.set.withArgs('embed_code', 'wambooli').callCount).toBe(2);
  });

});
