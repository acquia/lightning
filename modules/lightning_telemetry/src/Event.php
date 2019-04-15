<?php

namespace Drupal\lightning_telemetry;

use Drupal\Component\Serialization\Json;

/**
 * An event object as defined by Amplitude API specifications.
 *
 * @see https://developers.amplitude.com/#http-api
 */
class Event implements \JsonSerializable {

  /**
   * The event type.
   *
   * @var string
   */
  protected $type;

  /**
   * An array of event data.
   *
   * This key value pair will be sent to Amplitude as event_properties.
   *
   * @var array
   */
  protected $data;

  /**
   * The Amplitude user ID for the event.
   *
   * We do not use actual user ID. Instead, the UUID of the application is
   * used to uniquely identify the event while preserving anonymity.
   *
   * @var string
   */
  protected $user_id;

  /**
   * Event constructor.
   *
   * @param string $type
   *   The event type.
   * @param array $data
   *   The event properties.
   */
  public function __construct($type, $user_id, $data = []) {
    $this->type = $type;
    $this->user_id = $user_id;
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
   * Converts the event object to a serializable array for the Amplitude API.
   *
   * This method will be be called by PHP whenever json_encode() is used on
   * an object of this class.
   *
   * @return array
   *   An array, to be serialized json  that the Amplitude API will accept.
   *
   * @see https://amplitude.zendesk.com/hc/en-us/articles/204771828#keys-for-the-event-argument
   */
  public function jsonSerialize() {
     return [
        'event_type' => $this->type,
        'user_id' => $this->user_id,
        'event_properties' => $this->data,
      ];
  }

}
