<?php

/**
 * @file
 * Drupal needs this blank file.
 */

/**
 * This is a function with forbidden use of json_decode.
 */
function _example_json_decode_forbidden_functions() {
  $data = '';

  // Invalid use json_decode.
  $data = json_decode('string');
  $data = json_decode('string', TRUE);
  $data = drupal_json_decode('string', FALSE);
  $data = drupal_json_decode('string', TRUE);

  // Valid use of json_decode().
  $data = drupal_json_decode('string');
  $data = json_decode('string', FALSE);

  return $data;
}
