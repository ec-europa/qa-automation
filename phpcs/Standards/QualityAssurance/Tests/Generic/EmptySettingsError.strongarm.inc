<?php

/**
 * @file
 * fisheries_views.strongarm.inc
 */

/**
 * Implements hook_strongarm().
 */
function fisheries_views_strongarm() {
  $export = array();

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'comment_anonymous_fisheries_homepage_slider';
  $strongarm->value = '';
  $export['comment_anonymous_fisheries_homepage_slider'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'node_submitted_newsroom_item';
  $strongarm->value = 0;
  $export['node_submitted_newsroom_item'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'pathauto_node_nems_publication_pattern';
  $strongarm->value = '';
  $export['pathauto_node_nems_publication_pattern'] = $strongarm;

  return $export;
}
