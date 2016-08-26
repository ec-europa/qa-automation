<?php

$result = db_query("SELECT title FROM node WHERE nid = " . $nid);

function foo_menu() {
  $items['bar'] = array(
    'page callback' => 'bar_callback',
  );
  return $items;
}

function bar_callback($arg) {
  $num_updated = db_update('node')
  ->fields(array(
    'uid' => 5,
    'status' => 1,
  ))
  ->condition('nid', $arg) 
  ->execute();
  return "bad input ";
}
