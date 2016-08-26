<?php

print variable_get('foo', 'bar');

$foo = variable_get('foo', 'bar');
drupal_set_title($foo);

function foo() {
  $bar = "fish " . variable_get('fish', 'int');
  return $bar;
}
