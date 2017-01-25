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
  $basename = drupal_basename('string');
  $chmod = drupal_chmod('string');
  $dirname = drupal_dirname('string');
  $http_build_query = drupal_http_build_query('string');
  $json_decode = drupal_json_decode('string');
  $json_encode = drupal_json_encode('string');
  $mkdir = drupal_mkdir('string');
  $move_uploaded_file = drupal_move_uploaded_file('string');
  $parse_url = drupal_parse_url('string');
  $realpath = drupal_realpath('string');
  $register_shutdown_function = drupal_register_shutdown_function('function');
  $rmdir = drupal_rmdir('string');
  $session_regenerate = drupal_session_regenerate('string');
  $session_start = drupal_session_start();
  $set_time_limit = drupal_set_time_limit(60);
  $strlen = drupal_strlen('string');
  $strtolower = drupal_strtolower('string');
  $strtoupper = drupal_strtoupper('string');
  $substr = drupal_substr('string', 0, 3);
  $tempnam = drupal_tempnam('string', 'string');
  $ucfirst = drupal_ucfirst('string');
  $unlink = drupal_unlink('string');
  $xml_parser_create = drupal_xml_parser_create(NULL);
  $eval = php_eval('string');
}
