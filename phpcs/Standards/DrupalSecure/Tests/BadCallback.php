<?php

function foo_menu() {
  $items['bar'] = array(
    'page callback' => 'bar_callback',
  );
  $items['foo'] = array(
    'page callback' => 'foo_callback',
  );
  $items['baz'] = array(
    'page callback' => 'baz_callback',
  );
  $items['fish'] = array(
    'page callback' => 'fish_callback',
  );
  return $items;
}

function bar_callback($arg) {
  return "bad input " . $arg;
}

function foo_callback($arg) {
  drupal_set_message($arg);
  return "bad input ";
}

function baz_callback($arg) {
  drupal_set_message(bounce($arg));
  return "wat input ";
}

function bounce($input) {
  return $input;
}

function fish_callback($arg) {
  print $arg;
  return 'foo';
}
