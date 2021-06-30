<?php

/**
 * @file
 * Drupal needs this blank file.
 */

/**
 * This is a function with forbidden functions.
 */
function _example_fail_forbidden_functions() {
  // Forbidden non drupal wrapped functions.
  $json_decode = json_decode($var, FALSE);
}
