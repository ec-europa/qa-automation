<?php

  print 'title';

  print $_GET['filter'];

  $args = $_GET
  print $args['title'];

  function getTitle() {
    return $_GET['title'];
  }
  print getTitle();

  drupal_set_title($_GET['title']);

	$title = $_GET['title'];
  print "title " . $title;
?>
