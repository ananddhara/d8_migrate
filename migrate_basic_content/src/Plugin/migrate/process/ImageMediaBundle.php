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
 *   id = "image_media_bundle",
 *   handle_multiples = TRUE
 * )
 */
class ImageMediaBundle extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
  //Not all entities have a sav
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
    $output = array();
    if (!$fid) {
      return $output;
    }
    $mid = $this->get_media_mid($fid);
    if ($mid) {
      return array('target_id' => $mid);
    }
    $caption = ($this->configuration['title']) ? $image->{$this->configuration['title']} : $image->caption;
    $entity = entity_create('media', array('bundle' => 'image'));
    $entity->image = array('target_id' => $fid);
    $entity->field_media_in_library = array('value' => 1);
    $entity->field_caption = array('value' => $caption);
    $entity->save();
    if ($entity->id()) {
      $output = array('target_id' => $entity->id());
    }
    return $output;
  }

  /**
   * Get Media mid based on the file fid.
   *
   * @param integer $fid
   *   File fid.
   *
   * @return integer
   *   Returns Media mid if present.
   */
  public function get_media_mid($fid) {
    $fid = intval($fid);
    $mid = '';
    if (!$fid) {
      return $mid;
    }
    $query = \Drupal::database()->select('media__image', 'p');
    $query->addField('p', 'entity_id');
    $query->condition('p.image_target_id', $fid);
    $mid = $query->execute()->fetchField();
    return $mid;
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
