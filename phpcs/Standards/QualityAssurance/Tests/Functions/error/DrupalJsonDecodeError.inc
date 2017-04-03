<?php

/**
 * @file
 * Drupal needs this blank file.
 */

/**
 * This is a function with forbidden use of json_decode.
 */
function _example_json_decode_forbidden_functions() {
  // Invalid use json_decode.
  $json_encode = json_encode('string');
  $json_encode = json_encode('string', TRUE);
  $json_encode = drupal_json_encode('string', FALSE);
  $json_encode = drupal_json_encode('string', TRUE);

  // Valid use of json_decode().
  $json_encode = drupal_json_encode('string');
  $json_encode = json_encode('string', FALSE);
}
