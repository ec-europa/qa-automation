<?php
/**
 * @file
 * Epsocrs_faqs.views_default.inc.
 */

/**
 * Implements hook_views_default_views().
 */
function epsocrs_faqs_views_default_views() {
  $export = array();
  $view = new view();
  $view->name = 'epsocrs_faqs_et_search';
  $view->description = '';
  $view->tag = 'default';
  $view->base_table = 'search_api_index_epsocrs_et_search_index';
  $view->human_name = 'EPSOCRS | FAQs ET Search';
  $view->core = 7;
  $view->api_version = '3.0';
  $view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */
  /* Display: Master */
  $handler = $view->new_display('default', 'Master', 'default');
  $handler->display->display_options['title'] = 'FAQ Search result';
  $handler->display->display_options['use_more_always'] = FALSE;
  $handler->display->display_options['access']['type'] = 'none';
  $handler->display->display_options['cache']['type'] = 'none';
  $handler->display->display_options['query']['type'] = 'views_query';
  $handler->display->display_options['exposed_form']['type'] = 'basic';
  $handler->display->display_options['exposed_form']['options']['submit_button'] = 'Go!';
  $handler->display->display_options['pager']['type'] = 'full';
  $handler->display->display_options['pager']['options']['items_per_page'] = '25';
  $handler->display->display_options['pager']['options']['offset'] = '0';
  $handler->display->display_options['pager']['options']['id'] = '0';
  $handler->display->display_options['pager']['options']['quantity'] = '9';
  $handler->display->display_options['style_plugin'] = 'default';
  $handler->display->display_options['row_plugin'] = 'fields';
  /* Field: Indexed Multilingual Content: Title */
  $handler->display->display_options['fields']['title']['id'] = 'title';
  $handler->display->display_options['fields']['title']['table'] = 'search_api_index_epsocrs_et_search_index';
  $handler->display->display_options['fields']['title']['field'] = 'title';
  $handler->display->display_options['fields']['title']['label'] = '';
  $handler->display->display_options['fields']['title']['element_type'] = 'h3';
  $handler->display->display_options['fields']['title']['element_label_colon'] = FALSE;
  $handler->display->display_options['fields']['title']['link_to_entity'] = 1;
  /* Field: Indexed Multilingual Content: The main body text */
  $handler->display->display_options['fields']['body']['id'] = 'body';
  $handler->display->display_options['fields']['body']['table'] = 'search_api_index_epsocrs_et_search_index';
  $handler->display->display_options['fields']['body']['field'] = 'body';
  $handler->display->display_options['fields']['body']['label'] = '';
  $handler->display->display_options['fields']['body']['exclude'] = TRUE;
  $handler->display->display_options['fields']['body']['alter']['max_length'] = '200';
  $handler->display->display_options['fields']['body']['alter']['strip_tags'] = TRUE;
  $handler->display->display_options['fields']['body']['alter']['trim'] = TRUE;
  $handler->display->display_options['fields']['body']['element_label_colon'] = FALSE;
  /* Field: Search: Excerpt */
  $handler->display->display_options['fields']['search_api_excerpt']['id'] = 'search_api_excerpt';
  $handler->display->display_options['fields']['search_api_excerpt']['table'] = 'search_api_index_epsocrs_et_search_index';
  $handler->display->display_options['fields']['search_api_excerpt']['field'] = 'search_api_excerpt';
  $handler->display->display_options['fields']['search_api_excerpt']['label'] = '';
  $handler->display->display_options['fields']['search_api_excerpt']['element_label_colon'] = FALSE;
  $handler->display->display_options['fields']['search_api_excerpt']['empty'] = '[body]';
  $handler->display->display_options['fields']['search_api_excerpt']['link_to_entity'] = 0;
  /* Field: Content: Link */
  $handler->display->display_options['fields']['view_node']['id'] = 'view_node';
  $handler->display->display_options['fields']['view_node']['table'] = 'views_entity_node';
  $handler->display->display_options['fields']['view_node']['field'] = 'view_node';
  $handler->display->display_options['fields']['view_node']['label'] = '';
  $handler->display->display_options['fields']['view_node']['element_class'] = 'btn btn-default btn-faq-center';
  $handler->display->display_options['fields']['view_node']['element_label_colon'] = FALSE;
  $handler->display->display_options['fields']['view_node']['element_default_classes'] = FALSE;
  $handler->display->display_options['fields']['view_node']['text'] = 'more';
  $handler->display->display_options['filter_groups']['groups'] = array(
    1 => 'AND',
    2 => 'OR',
  );
  /* Filter criterion: Indexed Multilingual Content: Item language */
  $handler->display->display_options['filters']['search_api_language']['id'] = 'search_api_language';
  $handler->display->display_options['filters']['search_api_language']['table'] = 'search_api_index_epsocrs_et_search_index';
  $handler->display->display_options['filters']['search_api_language']['field'] = 'search_api_language';
  $handler->display->display_options['filters']['search_api_language']['value'] = array(
    'current' => 'current',
  );
  $handler->display->display_options['filters']['search_api_language']['group'] = 1;
  /* Filter criterion: Search: Fulltext search */
  $handler->display->display_options['filters']['search_api_views_fulltext']['id'] = 'search_api_views_fulltext';
  $handler->display->display_options['filters']['search_api_views_fulltext']['table'] = 'search_api_index_epsocrs_et_search_index';
  $handler->display->display_options['filters']['search_api_views_fulltext']['field'] = 'search_api_views_fulltext';
  $handler->display->display_options['filters']['search_api_views_fulltext']['group'] = 1;
  $handler->display->display_options['filters']['search_api_views_fulltext']['exposed'] = TRUE;
  $handler->display->display_options['filters']['search_api_views_fulltext']['expose']['operator_id'] = 'search_api_views_fulltext_op';
  $handler->display->display_options['filters']['search_api_views_fulltext']['expose']['label'] = 'Search for';
  $handler->display->display_options['filters']['search_api_views_fulltext']['expose']['operator'] = 'search_api_views_fulltext_op';
  $handler->display->display_options['filters']['search_api_views_fulltext']['expose']['identifier'] = 'keyword';
  $handler->display->display_options['filters']['search_api_views_fulltext']['expose']['required'] = TRUE;
  $handler->display->display_options['filters']['search_api_views_fulltext']['min_length'] = '3';
  $handler->display->display_options['filters']['search_api_views_fulltext']['fields'] = array(
    'body:value' => 'body:value',
    'title_field' => 'title_field',
  );
  /* Filter criterion: Broken/missing handler */
  $handler->display->display_options['filters']['field_epso_archived']['id'] = 'field_epso_archived';
  $handler->display->display_options['filters']['field_epso_archived']['table'] = 'search_api_index_epsocrs_et_search_index';
  $handler->display->display_options['filters']['field_epso_archived']['field'] = 'field_epso_archived';
  $handler->display->display_options['filters']['field_epso_archived']['value'] = array(
    0 => '0',
  );
  $handler->display->display_options['filters']['field_epso_archived']['group'] = 2;
  /* Filter criterion: Broken/missing handler */
  $handler->display->display_options['filters']['field_epso_archived_1']['id'] = 'field_epso_archived_1';
  $handler->display->display_options['filters']['field_epso_archived_1']['table'] = 'search_api_index_epsocrs_et_search_index';
  $handler->display->display_options['filters']['field_epso_archived_1']['field'] = 'field_epso_archived';
  $handler->display->display_options['filters']['field_epso_archived_1']['operator'] = 'empty';
  $handler->display->display_options['filters']['field_epso_archived_1']['value'] = array();
  $handler->display->display_options['filters']['field_epso_archived_1']['group'] = 2;
  /* Display: P | List of results */
  $handler = $view->new_display('page', 'P | List of results', 'page');
  $handler->display->display_options['display_description'] = 'Page with a list of FAQs filtered via exposed fulltext search filter';
  $handler->display->display_options['exposed_block'] = TRUE;
  $handler->display->display_options['defaults']['fields'] = FALSE;
  /* Field: Indexed Multilingual Content: Title */
  $handler->display->display_options['fields']['title']['id'] = 'title';
  $handler->display->display_options['fields']['title']['table'] = 'search_api_index_epsocrs_et_search_index';
  $handler->display->display_options['fields']['title']['field'] = 'title';
  $handler->display->display_options['fields']['title']['label'] = '';
  $handler->display->display_options['fields']['title']['element_type'] = 'h3';
  $handler->display->display_options['fields']['title']['element_label_colon'] = FALSE;
  $handler->display->display_options['fields']['title']['link_to_entity'] = 1;
  /* Field: Indexed Multilingual Content: Question categories */
  $handler->display->display_options['fields']['field_question_ref_categories']['id'] = 'field_question_ref_categories';
  $handler->display->display_options['fields']['field_question_ref_categories']['table'] = 'search_api_index_epsocrs_et_search_index';
  $handler->display->display_options['fields']['field_question_ref_categories']['field'] = 'field_question_ref_categories';
  $handler->display->display_options['fields']['field_question_ref_categories']['link_to_entity'] = 1;
  $handler->display->display_options['fields']['field_question_ref_categories']['view_mode'] = 'full';
  $handler->display->display_options['fields']['field_question_ref_categories']['bypass_access'] = 0;
  /* Field: Indexed Multilingual Content: The main body text */
  $handler->display->display_options['fields']['body']['id'] = 'body';
  $handler->display->display_options['fields']['body']['table'] = 'search_api_index_epsocrs_et_search_index';
  $handler->display->display_options['fields']['body']['field'] = 'body';
  $handler->display->display_options['fields']['body']['label'] = '';
  $handler->display->display_options['fields']['body']['exclude'] = TRUE;
  $handler->display->display_options['fields']['body']['alter']['max_length'] = '200';
  $handler->display->display_options['fields']['body']['alter']['strip_tags'] = TRUE;
  $handler->display->display_options['fields']['body']['alter']['trim'] = TRUE;
  $handler->display->display_options['fields']['body']['element_label_colon'] = FALSE;
  /* Field: Search: Excerpt */
  $handler->display->display_options['fields']['search_api_excerpt']['id'] = 'search_api_excerpt';
  $handler->display->display_options['fields']['search_api_excerpt']['table'] = 'search_api_index_epsocrs_et_search_index';
  $handler->display->display_options['fields']['search_api_excerpt']['field'] = 'search_api_excerpt';
  $handler->display->display_options['fields']['search_api_excerpt']['label'] = '';
  $handler->display->display_options['fields']['search_api_excerpt']['element_label_colon'] = FALSE;
  $handler->display->display_options['fields']['search_api_excerpt']['empty'] = '[body]';
  $handler->display->display_options['fields']['search_api_excerpt']['link_to_entity'] = 0;
  /* Field: Content: Link */
  $handler->display->display_options['fields']['view_node']['id'] = 'view_node';
  $handler->display->display_options['fields']['view_node']['table'] = 'views_entity_node';
  $handler->display->display_options['fields']['view_node']['field'] = 'view_node';
  $handler->display->display_options['fields']['view_node']['label'] = '';
  $handler->display->display_options['fields']['view_node']['element_class'] = 'btn btn-default btn-faq-center';
  $handler->display->display_options['fields']['view_node']['element_label_colon'] = FALSE;
  $handler->display->display_options['fields']['view_node']['element_default_classes'] = FALSE;
  $handler->display->display_options['fields']['view_node']['text'] = 'more';
  $handler->display->display_options['path'] = 'help/faq/search';
  /* Display: P | Export */
  $handler = $view->new_display('page', 'P | Export', 'page_1');
  $handler->display->display_options['defaults']['title'] = FALSE;
  $handler->display->display_options['title'] = 'FAQs | Export';
  $handler->display->display_options['defaults']['fields'] = FALSE;
  /* Field: Indexed Multilingual Content: Title */
  $handler->display->display_options['fields']['title']['id'] = 'title';
  $handler->display->display_options['fields']['title']['table'] = 'search_api_index_epsocrs_et_search_index';
  $handler->display->display_options['fields']['title']['field'] = 'title';
  $handler->display->display_options['fields']['title']['label'] = '';
  $handler->display->display_options['fields']['title']['element_type'] = 'h3';
  $handler->display->display_options['fields']['title']['element_label_colon'] = FALSE;
  $handler->display->display_options['fields']['title']['link_to_entity'] = 1;
  /* Field: Indexed Multilingual Content: Question categories */
  $handler->display->display_options['fields']['field_question_ref_categories']['id'] = 'field_question_ref_categories';
  $handler->display->display_options['fields']['field_question_ref_categories']['table'] = 'search_api_index_epsocrs_et_search_index';
  $handler->display->display_options['fields']['field_question_ref_categories']['field'] = 'field_question_ref_categories';
  $handler->display->display_options['fields']['field_question_ref_categories']['link_to_entity'] = 1;
  $handler->display->display_options['fields']['field_question_ref_categories']['view_mode'] = 'full';
  $handler->display->display_options['fields']['field_question_ref_categories']['bypass_access'] = 0;
  /* Field: Indexed Multilingual Content: The main body text */
  $handler->display->display_options['fields']['body']['id'] = 'body';
  $handler->display->display_options['fields']['body']['table'] = 'search_api_index_epsocrs_et_search_index';
  $handler->display->display_options['fields']['body']['field'] = 'body';
  $handler->display->display_options['fields']['body']['label'] = '';
  $handler->display->display_options['fields']['body']['alter']['max_length'] = '200';
  $handler->display->display_options['fields']['body']['element_label_colon'] = FALSE;
  $handler->display->display_options['defaults']['filter_groups'] = FALSE;
  $handler->display->display_options['defaults']['filters'] = FALSE;
  /* Filter criterion: Indexed Multilingual Content: Item language */
  $handler->display->display_options['filters']['search_api_language']['id'] = 'search_api_language';
  $handler->display->display_options['filters']['search_api_language']['table'] = 'search_api_index_epsocrs_et_search_index';
  $handler->display->display_options['filters']['search_api_language']['field'] = 'search_api_language';
  $handler->display->display_options['filters']['search_api_language']['value'] = array(
    'current' => 'current',
  );
  /* Filter criterion: Search: Fulltext search */
  $handler->display->display_options['filters']['search_api_views_fulltext']['id'] = 'search_api_views_fulltext';
  $handler->display->display_options['filters']['search_api_views_fulltext']['table'] = 'search_api_index_epsocrs_et_search_index';
  $handler->display->display_options['filters']['search_api_views_fulltext']['field'] = 'search_api_views_fulltext';
  $handler->display->display_options['filters']['search_api_views_fulltext']['exposed'] = TRUE;
  $handler->display->display_options['filters']['search_api_views_fulltext']['expose']['operator_id'] = 'search_api_views_fulltext_op';
  $handler->display->display_options['filters']['search_api_views_fulltext']['expose']['label'] = 'Search for';
  $handler->display->display_options['filters']['search_api_views_fulltext']['expose']['operator'] = 'search_api_views_fulltext_op';
  $handler->display->display_options['filters']['search_api_views_fulltext']['expose']['identifier'] = 'keyword';
  $handler->display->display_options['filters']['search_api_views_fulltext']['expose']['required'] = TRUE;
  $handler->display->display_options['filters']['search_api_views_fulltext']['min_length'] = '3';
  $handler->display->display_options['filters']['search_api_views_fulltext']['fields'] = array(
    'body:value' => 'body:value',
    'title_field' => 'title_field',
  );
  /* Filter criterion: Indexed Multilingual Content: Question categories */
  $handler->display->display_options['filters']['field_question_ref_categories']['id'] = 'field_question_ref_categories';
  $handler->display->display_options['filters']['field_question_ref_categories']['table'] = 'search_api_index_epsocrs_et_search_index';
  $handler->display->display_options['filters']['field_question_ref_categories']['field'] = 'field_question_ref_categories';
  $handler->display->display_options['filters']['field_question_ref_categories']['value'] = array();
  $handler->display->display_options['filters']['field_question_ref_categories']['exposed'] = TRUE;
  $handler->display->display_options['filters']['field_question_ref_categories']['expose']['operator_id'] = 'field_question_ref_categories_op';
  $handler->display->display_options['filters']['field_question_ref_categories']['expose']['label'] = 'Question categories';
  $handler->display->display_options['filters']['field_question_ref_categories']['expose']['operator'] = 'field_question_ref_categories_op';
  $handler->display->display_options['filters']['field_question_ref_categories']['expose']['identifier'] = 'field_question_ref_categories';
  $handler->display->display_options['filters']['field_question_ref_categories']['expose']['remember_roles'] = array(
    2 => '2',
    1 => 0,
    4 => 0,
    3 => 0,
    5 => 0,
    6 => 0,
    14 => 0,
    12 => 0,
    7 => 0,
    8 => 0,
    9 => 0,
    10 => 0,
    11 => 0,
    13 => 0,
  );
  $handler->display->display_options['filters']['field_question_ref_categories']['type'] = 'select';
  $handler->display->display_options['filters']['field_question_ref_categories']['hierarchy'] = 1;
  $handler->display->display_options['path'] = 'help/faq/export';
  $export['epsocrs_faqs_et_search'] = $view;
  return $export;
}
