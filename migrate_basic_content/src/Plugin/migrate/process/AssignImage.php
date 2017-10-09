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
    $constants = $row->getSourceProperty('constants');
    $this->configuration['constants'] = $constants;
    // Process multiple and single image.
    if (isset($value->image)) {
      return $this->manage_multiple_images($value);
    }
    else {
      return $this->manage_single_image($value);
    }

  }

  /**
   * Process Multiple images.
   *
   * @param array $images
   *   Array containing image information.
   *
   * @return array
   *   Returns file information.
   */
  public function manage_multiple_images($images) {
    $output = array();
    foreach ($images as $key => $image ) {
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

  /**
   * Process single image.
   *
   * @param array $image
   *   Array containing image information.
   *
   * @return array
   *   Returns file information.
   */
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

  /**
   * Attach image related information.
   *
   * @param integer $fid
   *   File fid of the image.
   * @param object $image
   *   Object containing image information.
   *
   * @return array
   *   Returns array containing file information.
   */
  public function attach_image_info ($fid, $image) {
    $alt_text = ($this->configuration['alt']) ? $image->{$this->configuration['alt']} : $image->AltText;
    $title = ($this->configuration['title']) ? $image->{$this->configuration['title']} : $image->caption;
    $output = array('target_id' => $fid,
      'alt' => trim($alt_text),
      'title' => trim($title),
    );
    return $output;
  }

  /**
   * Create an image.
   *
   * @param type $name
   *   Name of the image.
   *
   * @return integer
   *   Returns file fid.
   */
  public function create_image($name) {
    $source_base_path = (isset($this->configuration['constants']['source_base_path'])) ? trim($this->configuration['constants']['source_base_path']) : 'public://private_files/';
    $destination_base_path = (isset($this->configuration['constants']['destination_base_path'])) ? trim($this->configuration['constants']['destination_base_path']) : 'public://';
    $data = file_get_contents($source_base_path . $name);
    $file = file_save_data($data, $destination_base_path . $name, FILE_EXISTS_REPLACE);
    return $file->id();
  }

  /**
   * Get file information from the file_managed schema.
   *
   * @param string $filename
   *   Name of the file.
   *
   * @return integer
   *   Returns file fid if present.
   */
  public function get_file_fid($filename) {
    $filename = trim($filename);
    $query = \Drupal::database()->select('file_managed', 'p');
    $query->addField('p', 'fid');
    $query->condition('p.filename', $filename , 'like');
    $fid = $query->execute()->fetchField();
    return $fid;
  }
}
