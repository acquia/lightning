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

    // POSTing to the upload URL will always return {id: 1} -- great success!
    server.respondWith('POST', '/upload', [200, {'Content-Type': 'application/json'}, '{"id":1}']);
  });

  afterEach(function () {
    // Destroy the fake server and restore XMLHTTPRequest.
    server.restore();
  });

  it('should update the model on successful upload', function () {
    dropzone.on('success', function () {
      expect(model.id).toBe(1);
    })
    .addFile({
      name: 'foobar.jpg',
      type: 'image/jpeg'
    });
    // Normally this is done automatically, but for some reason it doesn't
    // happen when I call addFile().
    dropzone.processQueue();
  });

  it('should try to delete an upload when a file is removed', function () {
    server.respondWith('DELETE', '/upload/1', function (request) {
      expect(request).toBeTruthy();
      request.respond(200, {'Content-Type': 'application/json'}, '{}');
    });

    dropzone.on('success', function (file) {
      // Immediately remove the file to trigger a DELETE request.
      this.removeFile(file);
    })
    .addFile({
      name: 'foobar.jpg',
      type: 'image/jpeg'
    });
    // See the previous spec.
    dropzone.processQueue();
  });

});
