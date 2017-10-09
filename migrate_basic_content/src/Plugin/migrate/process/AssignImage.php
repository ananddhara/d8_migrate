<?php

namespace Drupal\migrate_basic_content\Plugin\migrate\process;

use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

use \Drupal\file\Entity\File;

/**
 * This plugin extracts attributes.
 *
 * @MigrateProcessPlugin(
 *   id = "assign_image",
 *   handle_multiples = TRUE
 * )
 */
class AssignImage extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (isset($value->image)) {
      return $this->manage_multiple_images($value);
    }
    else {
      return $this->manage_single_image($value);
    }
  }

  public function manage_multiple_images($value) {
    $output = array();
    foreach ($value as $key => $image ) {
      $name = trim($image->name);
      $fid = $this->get_file_fid($name);
      if (!$fid) {
        $fid = $this->create_image($name);
      }
      if ($fid) {
        $output[] = $this->attach_image_info( $fid, $image);
      }
    }
    return $output;
  }
  
  public function manage_single_image($image) {
    $output = array();
    $name = trim($image->name);
    if (empty($name)) {
      return $output;
    }
    $fid = $this->get_file_fid($name);
    if (!$fid) {
      $fid = $this->create_image($name);
    }
    if ($fid) {
      $output[] = $this->attach_image_info( $fid, $image);
    }
    return $output;
  }

  public function attach_image_info ($fid, $image) {
    $ftp_path = $image->FTPpath;
    $name = trim($image->name);
    $alt_text = $image->AltText;
    $caption = trim($image->caption);
    $credit = trim($image->credit);
    $seo_file_name = trim($image->SEOFilename);
    $output = array('target_id' => $fid,
      'alt' => trim($alt_text),
      'title' => trim($caption),
    );
    return $output;
  }

  public function create_image($name) {
    $data = file_get_contents("public://private_files/" . $name);
    $file = file_save_data($data, "public://" . $name, FILE_EXISTS_REPLACE);                                                                            $file->id();
    return $file->id();
  }

  public function get_file_fid($filename) {
    $fid = '';
    $filename = trim($filename);
    $query = \Drupal::database()->select('file_managed', 'p');
    $query->addField('p', 'fid');
    $query->condition('p.filename', $filename , 'like');
    $fid = $query->execute()->fetchField();
    return $fid;
  }
}