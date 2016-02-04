/**
 * @file
 * Tests of the Uploader widget.
 */

describe('Upload widget', function () {
  "use strict";

  /**
   * Fake server to respond to Ajax requests.
   */
  var server;

  /**
   * The widget, naturally.
   */
  var widget;

  /**
   * The model maintained by the widget.
   */
  var model;

  /**
   * The widget's live dropzone instance.
   */
  var dropzone;

  beforeEach(function () {
    // Initialize the fake server which will respond to the widget's Ajax
    // requests with the responses we set up in the tests.
    server = sinon.fakeServer.create({
      // Normally we'd need to call server.respond() to trigger a response
      // (i.e., to simulate asynchronicity). But there's no real need for that
      // in these tests.
      respondImmediately: true
    });

    // Set up the widget.
    widget = new Uploader({
      url: '/upload'
    });
    model = widget.model;
    dropzone = widget.dz;

    // Set up server responses.
    server.respondWith('POST', '/upload', [
      200, {'Content-Type': 'application/json'}, '{"id":1}'
    ]);
    server.respondWith('DELETE', '/upload/1', [
      200, {'Content-Type': 'application/json'}, '{}'
    ]);
    server.respondWith('PUT', '/upload/1', [
      200, {'Content-Type': 'application/json'}, '{"id":2}'
    ]);
  });

  afterEach(function () {
    // Destroy the fake server and restore XMLHTTPRequest.
    server.restore();
  });

  it('should update the model on successful upload', function (done) {
    dropzone
      .on('success', function () {
        expect(model.id).toBe(1);
        done();
      })
      .addFile({ type: 'image/jpeg' });
  });

  it('should try to delete an upload when a file is removed', function (done) {
    spyOn(model, 'destroy').and.callThrough();
    spyOn(model, 'clear');

    dropzone
      .on('success', function (file) {
        this.removeFile(file);
        expect(model.destroy).toHaveBeenCalled();
        expect(model.clear).toHaveBeenCalled();
        done();
      })
      .addFile({ type: 'image/jpeg' });
  });

  it('should not save the model unless asked', function (done) {
    spyOn(model, 'save');

    dropzone
      .on('success', function () {
        widget.finalize().then(function () {
          expect(model.save).not.toHaveBeenCalled();
          done();
        })
      })
      .addFile({ type: 'image/jpeg' });
  });

  it('should save the model if asked', function (done) {
    spyOn(model, 'save').and.callThrough();

    dropzone
      .on('success', function () {
        widget.toLibrary.checked = true;

        widget.finalize().then(function (output) {
          expect(model.save).toHaveBeenCalled();
          // The promise returned from finalize() is resolved with a *clone* of
          // the original model -- the original model itself is cleared out,
          // and since model is a reference to the original model, model.id
          // will be undefined. output is the clone.
          expect(output.id).toBe(2);
          done();
        })
      })
      .addFile({ type: 'image/jpeg' });
  });

});
