<?php

namespace Drupal\lightning_telemetry;

/**
 * AmplitudeEvent.
 */
class Event {

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
   * The Amplitude user id for the event.
   *
   * We do not use actual user ids. Instead, the UUID of the application is
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
  public function setData($data) {
    $this->data = $data;

    return $this;
  }

  /**
   * Converts the event object to a JSON string that Amplitude will accept.
   *
   * @return string
   *   JSON string that Amplitude will accept.
   */
  public function __toJson() {
    return json_encode(
      [
        'event_type' => $this->type,
        'user_id' => $this->user_id,
        'event_properties' => $this->data,
      ]
    );
  }

}
