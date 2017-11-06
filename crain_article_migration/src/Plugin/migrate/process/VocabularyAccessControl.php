<?php

namespace Drupal\crain_article_migration\Plugin\migrate\process;

use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * This plugin process taxonomy term.
 *
 * @MigrateProcessPlugin(
 *   id = "vocabulary_access_control",
 *   handle_multiples = TRUE
 * )
 */
class VocabularyAccessControl extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $output = array();
    $value = trim($value);
    $tid = $this->get_taxonomy_tid($value);
    if ($tid) {
      $output[] = array('target_id' => $tid);
      return $output;
    }
    return $output;
  }

  /**
   * Get taxonomy based on the taxonomy term and vocabulary.
   *
   * @param string $name
   *   Name of the taxonomy term.
   * @param string $vocabulary_name
   *   Name of the vocabulary.
   *
   * @return integer
   *   Returns taxonomy term tid.
   */
  public function get_taxonomy_tid($name, $vocabulary_name = 'access_control') {
    $name = trim($name);
    $query = \Drupal::database()->select('taxonomy_term_field_data', 't');
    $query->addField('t', 'tid');
    $query->condition('t.name', $name , '=');
    $query->condition('t.vid', $vocabulary_name , '=');
    $tid = $query->execute()->fetchField();
    return $tid;
  }
}
