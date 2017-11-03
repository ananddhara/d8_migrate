<?php

namespace Drupal\crain_article_migration\Plugin\migrate\process;

use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

use \Drupal\file\Entity\File;

/**
 * This plugin extracts attributes.
 *
 * @MigrateProcessPlugin(
 *   id = "map_custom_fields",
 *   handle_multiples = TRUE
 * )
 */
class MapCustomFields extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    foreach($value->field as $pos => $xml) {
      $name =  (string)$xml->attributes()['name'][0];
      if ($name == $this->configuration['source_field']) {
        $data = (string)$xml;
        return $data;
      }
    }
  }

}
