<?php

print check_plain($node->title);

function foo() {
  drupal_set_message(check_plain($foo->title));
}
