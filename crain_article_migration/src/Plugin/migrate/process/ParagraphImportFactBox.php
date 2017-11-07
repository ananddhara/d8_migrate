<?php

/**
 * @file
 * Contains \Drupal\crain_article_migration\Plugin\migrate\process\ParagraphImportFactBox.
 */

namespace Drupal\crain_article_migration\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

/**
 *
 * @MigrateProcessPlugin(
 *   id = "paragraphs_import_factbox"
 * )
 */
class ParagraphImportFactBox extends ProcessPluginBase {
  	/**
  	 * {@inheritdoc}
  	 */
   	public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
      // Process multiple and single paragraph.
      if (isset($value->factbox)) {
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
    $paragraphs = [];
    $output = $this->attach_paragraph_info($paragraph);
    if (isset($output['target_id'])) {
      $paragraphs[] = $output;
    }
    return $paragraphs;
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
    $title = (string)$paragraph->factbox_title;
    $text = (string)$paragraph->factbox_text;
    if (empty($title) && empty($text)) {
      return array();
    }
    $para_values = array(
      'id' => NULL,
      'type' => 'factbox',
      'field_title' => $title,
      'field_body' => [
        'value' =>  $text,
        'format' => 'full_html',
      ],
    );
    $paragraph_value = Paragraph::create($para_values);
    $paragraph_value->save();

    $target_id_dest = $paragraph_value->Id();
    $target_revision_id_dest = $paragraph_value->getRevisionId();
    return array('target_id' => $target_id_dest, 'target_revision_id' => $target_revision_id_dest);
  }

}
