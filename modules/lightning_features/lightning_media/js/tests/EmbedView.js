/**
 * @file
 * Contains unit tests of the EmbedView view.
 */

describe('EmbedView', function () {

  beforeEach(function () {
    this.model = new Embed();
    this.view = new EmbedView({ model: this.model });
    this.textarea = this.view.$('textarea').get(0);
    this.preview = this.view.$('.preview').get(0);
  });

  it('\'s textarea should react to model events', function () {
    this.model.trigger('request');
    expect(this.textarea.disabled).toBe(true);

    this.model.trigger('sync', this.model);
    expect(this.textarea.disabled).toBe(false);

    this.model.trigger('request');
    expect(this.textarea.disabled).toBe(true);
    this.model.trigger('destroy', this.model);
    expect(this.textarea.disabled).toBe(false);
  });

  it('s preview area reacts to model events', function () {
    // Set up assertions.
    sinon.spy(Drupal, 'attachBehaviors');
    sinon.spy(Drupal, 'detachBehaviors');

    // When the model is synced, the preview should be set and Drupal behaviors
    // should be attached to it.
    this.model.set('preview', 'Gentlemen, BEHOLD!');
    this.model.trigger('sync', this.model);
    expect(this.preview.innerHTML).toEqual(this.model.get('preview'));
    expect(Drupal.attachBehaviors.withArgs(this.preview).callCount).toBe(1);

    // When the model is destroyed, the preview should be cleared and Drupal
    // behaviors should be detached from it.
    this.model.trigger('destroy', this.model);
    expect(this.preview.innerHTML).toBeFalsy();
    expect(Drupal.detachBehaviors.withArgs(this.preview).callCount).toBe(1);

    // Clean up.
    Drupal.attachBehaviors.restore();
    Drupal.detachBehaviors.restore();
  });

  it('should clear the textarea and preview area without affect the model when reset', function () {
    // Set up assertions.
    spyOn(Drupal, 'detachBehaviors');

    // Put the view into a known state.
    this.textarea.value = 'Foobaz';
    this.preview.innerHTML = 'Look at this crazy thing!';
    this.view.reset();

    // Assert stuff.
    expect(this.textarea.value).toBeFalsy();
    expect(this.preview.innerHTML).toBeFalsy();
    expect(Drupal.detachBehaviors).toHaveBeenCalledTimes(1);
    expect(this.model.hasChanged()).toBe(false);
  });

});
