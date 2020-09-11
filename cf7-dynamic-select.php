<?php
/*
Plugin Name: CF7 Dynamic Select
Plugin URI: https://naman.io/
Description: Dynamic select element for contact form 7
Author: Naman Aggarwal
Author URI: https://naman.io/
*/


add_filter( 'wpcf7_form_tag', 'form_tag_dynamic_select_field', 10, 2);

function form_tag_dynamic_select_field ( $scanned_tag, $replace ) {
  if($scanned_tag["type"] != "select"){
    return $scanned_tag;
  }

  $pos= strpos($scanned_tag["name"],"cff--");

  if($pos === false || $pos != 0) {
    return $scanned_tag;
  }

  $data = explode("--",$scanned_tag["name"]);

  global $wpdb;
  $cfdb          = apply_filters('cfdb7_database', $wpdb);
  $table_name    = $cfdb->prefix . 'db7_forms';
  $form_post_id  = $data[1];
  $results  = $cfdb->get_results("SELECT * FROM $table_name WHERE form_post_id = $form_post_id", OBJECT);

  if(count($results) == 0) {
    return $scanned_tag;
  }
  $scanned_tag['raw_values'] = [];
  $scanned_tag['raw_values'][] = "Select";
  foreach ($results as $row) {
    $form_data  = unserialize($row->form_value);
    foreach($form_data as $key=>$value){
      if($key == $data[2]){
        $scanned_tag['raw_values'][] = $value;
      }
    }
  }

  $pipes = new WPCF7_Pipes($scanned_tag['raw_values']);

  $scanned_tag['values'] = $scanned_tag['raw_values'];
  $scanned_tag['pipes'] = $pipes;
  $scanned_tag['labels'] = $scanned_tag['raw_values'];

  return $scanned_tag;
}
