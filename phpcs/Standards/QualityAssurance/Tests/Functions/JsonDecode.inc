<?php

/**
 * @file
 * Drupal needs this blank file.
 */

/**
 * This is a function with forbidden functions.
 */
function _example_fail_forbidden_functions() {
  // Forbidden function without 2nd parameter.
  $json_decode = json_decode($var);
}
