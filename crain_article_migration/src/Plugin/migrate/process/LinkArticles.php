<?php

/**
 * @file
 * Contains \Drupal\crain_article_migration\Plugin\migrate\process\LinkArticles.
 */

namespace Drupal\crain_article_migration\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 *
 * @MigrateProcessPlugin(
 *   id = "link_articles",
 *   handle_multiples = TRUE
 * )
 */
class LinkArticles extends ProcessPluginBase {
  	/**
  	 * {@inheritdoc}
  	 */
   	public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
      return $this->process_links($value);
    }
  
    /**
     * Process article internal links.
     *
     * @param array $value
     *   Array containing link information.
     *
     * @return array
     *   Returns article link information.
     */
    function process_links($value) {
      if (isset($value->connection)) {
        return $this->manage_multiple_links($value);
      }
      else {
        return $this->manage_single_link($value);
      }
    }

    /**
     * Process single link.
     *
     * @param array $link
     *   Array containing article link information.
     *
     * @return array
     *   Returns link information.
     */
    public function manage_single_link($link) {
      $output = array();
      $row_guid = trim($link->guid);
      if (empty($row_guid)) {
        return $output;
      }
      $nid = $this->get_article_nid($row_guid);
      if ($nid) {
        $output[] = $this->attach_link_info( $nid);
      }
      return $output;
    }

    /**
     * Attach link related information.
     *
     * @param integer $nid
     *   Article nid.
     *
     * @return array
     *   Returns array containing link information.
     */
    public function attach_link_info ($nid) {
      $output = array('target_id' => $nid);
      return $output;
    }

    /**
     * Process Multiple links.
     *
     * @param array $links
     *   Array containing article link information.
     *
     * @return array
     *   Returns link information.
     */
    public function manage_multiple_links($links) {
      $output = array();
      foreach ($links as $key => $link ) {
        $row_guid = trim($link->guid);
        $nid = $this->get_article_nid($row_guid);
        if ($nid) {
          $output[] = $this->attach_link_info( $nid);
        }
      }
      return $output;
    }

    /**
     * Get article nid associated with the row_guid.
     *
     * @param string $row_guid
     *   Article row_guid.
     *
     * @return integer
     *   Returns article nid.
     */
    public function get_article_nid($row_guid) {
      $table_name = (!empty(trim($this->configuration['link_table']))) ? trim($this->configuration['link_table'])
        : 'migrate_map_article_content_xml';
      $row_guid = trim($row_guid);
      $query = \Drupal::database()->select($table_name, 'p');
      $query->addField('p', 'destid1');
      $query->condition('p.sourceid1', $row_guid , 'like');
      $nid = $query->execute()->fetchField();
      return $nid;
    }
  }
