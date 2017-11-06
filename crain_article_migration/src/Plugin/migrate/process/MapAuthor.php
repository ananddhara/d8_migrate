<?php

namespace Drupal\crain_article_migration\Plugin\migrate\process;

use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use \Drupal\node\Entity\Node;

/**
 * This plugin process author mapping.
 *
 * @MigrateProcessPlugin(
 *   id = "map_author",
 *   handle_multiples = TRUE
 * )
 */
class MapAuthor extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $output = array();
    $staff_id = trim($value->StaffID);
    echo $staff_id;
    $nid = $this->get_author_nid($staff_id);
    echo $nid;
    if ($nid) {
      $output[] = array('target_id' => $nid);
    }
    if (!$nid) {
      $nid = $this->create_author($staff_id);
      $output[] = array('target_id' => $nid);
    }
    return $output;
  }

  public function create_author($name) {
    $node = Node::create([
      'type' => 'author',
      'title' => $name,
    ]);  
    $node->save();
    $nid = $node->id();
    return $nid;
  }

  /**
   * Get Author based on the name.
   *
   * @param string $name
   *   Name of the taxonomy term.
   *
   * @return integer
   *   Returns Author nid.
   */
  public function get_author_nid($name, $type = 'author') {
    $name = trim($name);
    $query = \Drupal::database()->select('node_field_data', 'n');
    $query->addField('n', 'nid');
    $query->condition('n.title', $name , '=');
    $query->condition('n.type', $type , '=');
    $nid = $query->execute()->fetchField();
    return $nid;
  }
}
