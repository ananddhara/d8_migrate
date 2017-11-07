<?php

/**
 * @file
 * Contains \Drupal\crain_article_migration\Plugin\migrate\process\ParagraphImportParagraphPhoto.
 */

namespace Drupal\crain_article_migration\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use \Drupal\file\Entity\File;

/**
 *
 * @MigrateProcessPlugin(
 *   id = "paragraphs_import_paragraph_photo"
 * )
 */
class ParagraphImportParagraphPhoto extends ProcessPluginBase {
  	/**
  	 * {@inheritdoc}
  	 */
   	public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
      $constants = $row->getSourceProperty('constants');
      $this->configuration['constants'] = $constants;
      // Process multiple and single paragraph.
      if (isset($value->image)) {
        return $this->manage_multiple_paragraph($value);
      }
      else {
        return $this->manage_single_paragraph($value);
      }
    }
  
  /**
   * Process Multiple paragraphs.
   *
   * @param array $paragraphs
   *   Array containing paragraphs information.
   *
   * @return array
   *   Returns paragraph information.
   */
  public function manage_multiple_paragraph($paragraphs) {
    $paragraphs_output = array();
    foreach ($paragraphs as $key => $paragraph ) {
      $output = $this->attach_paragraph_info($paragraph);
      if (isset($output['target_id'])) {
        $paragraphs_output[] = $output;
      }
    }
    return $paragraphs_output;
  }

  /**
   * Process single Paragraph.
   *
   * @param array $paragraph
   *   Array containing Paragraph information.
   *
   * @return array
   *   Returns paragraph entity information.
   */
  public function manage_single_paragraph($paragraph) {
    $paragraphs_output = [];
    $output = $this->attach_paragraph_info($paragraph);
    if (isset($output['target_id'])) {
      $paragraphs_output[] = $output;
    }
    return $paragraphs_output;
  }

  /**
   * Attach paragraph related information.
   *
   * @param object $paragraph
   *   Object containing paragraph information.
   *
   * @return array
   *   Returns array containing paragraph entity information.
   */
  public function attach_paragraph_info ($paragraph) {
    $name = (string)$paragraph->name;
    $name = str_ireplace(".image/", ".", $name);
    if (empty($name)) {
      return array();
    }
    $paragraph->name = $name;
    $title = (string)$paragraph->AltText;
    $caption = (string)$paragraph->caption;
    $credit = (string)$paragraph->credit;
    $para_values = array(
      'id' => NULL,
      'type' => 'photographs',
      'field_image_title' => $title,
      'field_credit' => $credit,
      'field_caption' => $caption,
      'field_photo' => $this->manage_single_image($paragraph),
    );
    $paragraph_value = Paragraph::create($para_values);
    $paragraph_value->save();

    $target_id_dest = $paragraph_value->Id();
    $target_revision_id_dest = $paragraph_value->getRevisionId();
    return array('target_id' => $target_id_dest, 'target_revision_id' => $target_revision_id_dest);
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
      $output = $this->attach_image_info( $fid, $image);
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
    $image_folder_path = (isset($this->configuration['constants']['image_folder_path'])) ? trim($this->configuration['constants']['image_folder_path']) : 'images/2017/09/';
    $destination_base_path = (isset($this->configuration['constants']['destination_base_path'])) ? trim($this->configuration['constants']['destination_base_path']) : 'public://';
    $data = file_get_contents($source_base_path . $image_folder_path . $name);
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
