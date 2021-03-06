<?php

/**
 * @file
 * Contains \Drupal\entity_browser\Plugin\views\field\SelectForm.
 */

namespace Drupal\entity_browser\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\Plugin\views\style\Table;
use Drupal\views\ResultRow;
use Drupal\views\Render\ViewsRenderPipelineMarkup;

/**
 * Defines a bulk operation form element that works with entity browser.
 *
 * @ViewsField("entity_browser_select")
 */
class SelectForm extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    return ViewsRenderPipelineMarkup::create('<!--form-item-' . $this->options['id'] . '--' . $values->index . '-->');
  }

  /**
   * {@inheritdoc}
   */
  public function preRender(&$values) {
    parent::preRender($values);

    // If the view is using a table style, provide a placeholder for a
    // "select all" checkbox.
    if (!empty($this->view->style_plugin) && $this->view->style_plugin instanceof Table) {
      // Add the tableselect css classes.
      $this->options['element_label_class'] .= 'select-all';
      // Hide the actual label of the field on the table header.
      $this->options['label'] = '';
    }
  }

  /**
   * Form constructor for the bulk form.
   *
   * @param array $render
   *   An associative array containing the structure of the form.
   */
  public function viewsForm(&$render) {
    // Only add the bulk form options and buttons if there are results.
    if (!empty($this->view->result)) {
      // Render checkboxes for all rows.
      $render[$this->options['id']]['#tree'] = TRUE;
      $render[$this->options['id']]['#printed'] = TRUE;
      foreach ($this->view->result as $row_index => $row) {
        /** @var \Drupal\Core\Entity\EntityInterface $entity */
        $entity = $row->_entity;

        $element_name = 'entity_browser_select';
        if ($this->options['selection_type'] == 'checkbox') {
          $element_name .= '[' . $row_index . ']';
        }

        $render[$this->options['id']][$row_index] = [
          '#type' => $this->options['selection_type'],
          '#title' => $this->t('Select this item'),
          '#title_display' => 'invisible',
          '#return_value' => $entity->getEntityTypeId() . ':' . $entity->id(),
          '#attributes' => ['name' => $element_name],
          '#parents' => explode('[', rtrim($element_name, ']')),
          '#default_value' => NULL,
        ];
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function query() {}

  /**
   * {@inheritdoc}
   */
  public function clickSortable() { return FALSE; }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['selection_type']['default'] = 'checkbox';

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['selection_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Element type'),
      '#options' => [
        'checkbox' => $this->t('Checkboxes'),
        'radio' => $this->t('Radio buttons'),
      ],
      '#description' => $this->t('If using radio buttons, you will only be able to select one entity.'),
      '#default_value' => $this->options['selection_type'],
    ];
  }

}
