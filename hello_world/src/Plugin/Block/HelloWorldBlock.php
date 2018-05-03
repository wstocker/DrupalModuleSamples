<?php

namespace Drupal\hello_world\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Link;

/**
 * Provides a block with a link list of 'Hello World Articles' and enabled terms.
 *
 * @Block(
 *   id = "hello_world_block",
 *   admin_label = @Translation("Hello World Block"),
 * )
 */
class HelloWorldBlock extends BlockBase {

  /**
   * {@inheritdoc}
  */
  public function build() {
    // Get a container of the taxonomy term entities that have the vocabulary 'sections'.
    $entities = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('sections', 0, NULL, TRUE);
    // Create an array of the term ids that have the field enabled set to true.
    foreach ($entities as $entity) {
      if ($entity->field_enabled->value) {
        $enabled_tids[] = $entity->tid->value;
      }
    }
    // Get a container of nodes that have the property field 'sections' using term id array to get results.
    $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['field_sections' => $enabled_tids]);
    // Loop through the entities and create an absolute link to each one.
    foreach ($nodes as $key => $node) {
      $options = ['absolute' => TRUE];
      $link = Link::createFromRoute($node->getTitle(), 'entity.node.canonical', ['node' => $node->id()], $options);
      $links[] = $link->toRenderable();
    }
    // Return the hello world block theme with populated template variables.
    return [
      '#theme' => 'hello_world_block',
      '#title' => 'Hello World!',
      '#links' => $links,
    ];
  }

}
