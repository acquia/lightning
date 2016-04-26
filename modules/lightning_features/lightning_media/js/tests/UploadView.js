/**
 * @file
 * Contains unit tests of the UploadView class.
 */

describe('UploadView', function () {

  beforeEach(function () {
    this.view = new UploadView({ url: '/' });
    this.model = this.view.model;

    jasmine.Ajax.install();
  });

  afterEach(function () {
    jasmine.Ajax.uninstall();
  });

  it('should have the dropzone class', function () {
    expect(this.view.$el.hasClass('dropzone')).toBe(true);
  });

  it('should sync the model to the server upon successful upload', function () {
    sinon.spy(this.model, 'trigger');

    this.view.dz.addFile({ type: 'image/png' });
    this.view.dz.processQueue();
    jasmine.Ajax.requests.mostRecent().respondWith({
      status: 200,
      contentType: 'application/json',
      responseText: '{"id": 31}'
    });
    expect(this.model.id).toBe(31);
    expect(this.model.trigger.withArgs('sync').callCount).toBe(1);

    this.model.trigger.restore();
  });

  it('should destroy the model with a file is removed', function () {
    spyOn(this.model, 'destroy');

    this.view.dz.addFile({ type: 'image/png' });
    this.view.dz.removeAllFiles();

    expect(this.model.destroy).toHaveBeenCalled();
  });

  it('should remove all files without destroying the model when reset', function () {
    spyOn(this.model, 'destroy');
    sinon.spy(this.view.dz, 'removeAllFiles');

    this.view.dz.addFile({ type: 'image/png' });
    this.view.reset();

    expect(this.model.destroy).not.toHaveBeenCalled();
    expect(this.view.dz.removeAllFiles.called).toBe(true);

    this.view.dz.removeAllFiles.restore();
  });

});
