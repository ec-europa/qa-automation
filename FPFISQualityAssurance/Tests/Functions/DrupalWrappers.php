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
  $basename = basename('string');
  $chmod = chmod('string');
  $dirname = dirname('string');
  $http_build_query = http_build_query('string');
  $json_decode = json_decode('string');
  $json_encode = json_encode('string');
  $mkdir = mkdir('string');
  $move_uploaded_file = move_uploaded_file('string');
  $parse_url = parse_url('string');
  $realpath = realpath('string');
  $register_shutdown_function = register_shutdown_function('function');
  $rmdir = rmdir('string');
  $session_regenerate = session_regenerate('string');
  $session_start = session_start();
  $set_time_limit = set_time_limit(60);
  $strlen = strlen('string');
  $strtolower = strtolower('string');
  $strtoupper = strtoupper('string');
  $substr = substr('string', 0, 3);
  $tempnam = tempnam('string', 'string');
  $ucfirst = ucfirst('string');
  $unlink = unlink('string');
  $xml_parser_create = xml_parser_create(NULL);
  $eval = eval('string');
}
