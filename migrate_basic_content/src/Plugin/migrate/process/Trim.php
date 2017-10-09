<?php

namespace Drupal\migrate_basic_content\Plugin\migrate\process;

use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * This plugin extracts attributes.
 *
 * @MigrateProcessPlugin(
 *   id = "trim",
 *   handle_multiples = TRUE
 * )
 */
class Trim extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    return trim($value);
  }

}