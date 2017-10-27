<?php

/**
 * @file
 * Contains \Drupal\crain_article_migration\Plugin\migrate\process\ParagraphImport.
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
 *   id = "paragraphs_import"
 * )
 */
class ParagraphImport extends ProcessPluginBase {
  	/**
  	 * {@inheritdoc}
  	 */
   	public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

        $paragraphs = [];
        $para_values = array(
          'id' => NULL,
          'type' => 'paragraph_test1_type',
          'field_index' => $value->index,
          'field_subheading' => $value->subheading,
          'field_text' => [
            'value' => $value->text,
            'format' => 'full_html',
          ],
        );

        $paragraph_value = Paragraph::create($para_values);
        $paragraph_value->save();

        $target_id_dest = $paragraph_value->Id();
        $target_revision_id_dest = $paragraph_value->getRevisionId();
        $paragraphs[] = array('target_id' => $target_id_dest, 'target_revision_id' => $target_revision_id_dest);
        return $paragraphs;
    }
}
