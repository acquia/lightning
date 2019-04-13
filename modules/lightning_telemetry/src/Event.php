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
   * @var array
   */
  protected $data;

  protected $user_id;

  /**
   * Event constructor.
   *
   * @param string $type
   * @param array $data
   */
  public function __construct($type, $user_id, $data = []) {
    $this->type = $type;
    $this->user_id = $user_id;
    $this->data = $data;
  }

  /**
   * @param $data
   *
   * @return $this
   */
  public function setData($data) {
    $this->data = $data;

    return $this;
  }

  public function addData($data) {
    $this->data = array_merge_recursive($this->data, $data);

    return $this;
  }

  /**
   * @return string
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