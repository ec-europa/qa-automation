<?php

function notamenu() {
  $wat['page_callback'] = 'notamenucallback';
}

function notamenucallback($arg) {
  return $arg;
}

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
  return "bad input " . check_plain($arg);
}

function foo_callback($arg) {
  drupal_set_message(check_plain($arg));
  return "bad input ";
}

function baz_callback($arg) {
  print check_plain($arg);
  return 'foo';
}

function fish_callback($arg) {
  $arg = check_plain($arg);
  return $arg;
}
