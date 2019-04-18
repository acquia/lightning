<?php

namespace Drupal\acquia_telemetry;

/**
 * An event object as defined by Amplitude API specifications.
 *
 * @see https://developers.amplitude.com/#http-api
 */
final class Event implements \JsonSerializable {

  /**
   * The event type.
   *
   * @var string
   */
  private $type;

  /**
   * An array of event data.
   *
   * This key value pair will be sent to Amplitude as event_properties.
   *
   * @var array
   */
  private $data = [];

  /**
   * The Amplitude user ID for the event.
   *
   * We do not use actual user ID. Instead, the UUID of the application is
   * used to uniquely identify the event while preserving anonymity.
   *
   * @var string
   */
  private $userId;

  /**
   * Event constructor.
   *
   * @param string $type
   *   The event type.
   * @param string $user_id
   *   The Amplitude user ID. This does not connote a Drupal user id. In the
   *   context of this acquia_telemetry, it is simply a uuid.
   * @param array $data
   *   The event properties.
   */
  public function __construct($type, $user_id, array $data = []) {
    $this->type = $type;
    $this->userId = $user_id;
    $this->data = $data;
  }

  /**
   * Sets $this->data.
   *
   * @param array $data
   *   The event properties.
   *
   * @return $this
   *   The event object.
   */
  public function setData(array $data) {
    $this->data = $data;

    return $this;
  }

  /**
   * Implements \JsonSerializable::jsonSerialize().
   *
   * @return array
   *   An array, to be serialized JSON that the Amplitude API will accept.
   *
   * @see https://amplitude.zendesk.com/hc/en-us/articles/204771828#keys-for-the-event-argument
   */
  public function jsonSerialize() {
    return [
      'event_type' => $this->type,
      'user_id' => $this->userId,
      'event_properties' => $this->data,
    ];
  }

}