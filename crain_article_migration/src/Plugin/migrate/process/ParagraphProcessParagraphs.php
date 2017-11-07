<?php

/**
 * @file
 * Contains \Drupal\crain_article_migration\Plugin\migrate\process\ParagraphProcessParagraphs.
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
 *   id = "paragraph_process_paragraphs"
 * )
 */
class ParagraphProcessParagraphs extends ProcessPluginBase {
  	/**
  	 * {@inheritdoc}
  	 */
   	public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
      $factbox = $row->getDestinationProperty('paragraph_factbox');
      $body = $row->getDestinationProperty('paragraph_body');
      $photographs = $row->getDestinationProperty('paragraph_photographs');
      $final = array_merge($body,$factbox,$photographs);
      return $final;
    }
}
