<?php

namespace Drupal\calibr8_socialmedia\Plugin\Block;

use Drupal\Component\Utility\SortArray;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Template\Attribute;

/**
 * Provides a 'Social media' block.
 *
 * @Block(
 *   id = "calibr8_socialmedia",
 *   admin_label = @Translation("Social media block"),
 * )
 */
class SocialMediaBlock extends BlockBase  {

  /**
   * Return a list of available platforms
   */
  private function getPlatforms() {
    return array(
      'facebook' => $this->t('Facebook'),
      'linkedin' => $this->t('Linkedin'),
      'twitter' => $this->t('Twitter'),
      'googleplus' => $this->t('Google+'),
      'instagram' => $this->t('Instagram'),
      'reddit' => $this->t('Reddit'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $conf = array();
    foreach ($this->getPlatforms() as $platform => $name) {
      $conf[$platform]['link'] = '';
      $conf[$platform]['weight'] = 0;
    }
    return $conf;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['table'] = array(
      '#type' => 'table',
      '#header' => array(t('Platform'), t('Weight'), t('Url')),
      '#tableselect' => FALSE,
      '#tabledrag' => array(
        array(
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'table-order-weight',
        ),
      ),
    );

    foreach ($this->getPlatforms() as $platform => $name) {

      $rows[$platform]['#attributes']['class'][] = 'draggable';
      $rows[$platform]['#weight'] = $this->configuration[$platform]['weight'];
      $rows[$platform]['label'] = array(
        '#plain_text' => $name,
      );
      $rows[$platform]['weight'] = array(
        '#type' => 'weight',
        '#title' => $this->t('Weight for @name', array('@name' => $name)),
        '#title_display' => 'invisible',
        '#default_value' => $this->configuration[$platform]['weight'],
        '#attributes' => array('class' => array('table-order-weight')),
      );
      $rows[$platform]['element'] = array(
        '#type' => 'textfield',
        '#title' => $this->t('@name link', array('@name' => $name)),
        '#default_value' => $this->configuration[$platform]['link'],
      );

    }

    // do our own sorting, because drupal does not seem to do this
    uasort($rows, '\Drupal\Component\Utility\SortArray::sortByWeightProperty');
    $form['table'] = array_merge($form['table'], $rows);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {

    foreach ($this->getPlatforms() as $platform => $name) {
      $this->configuration[$platform]['link'] = $form_state->getValue(array('table', $platform, 'element'));
      $this->configuration[$platform]['weight'] = $form_state->getValue(array('table', $platform, 'weight'));
    }

  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    foreach ($this->getPlatforms() as $platform => $name) {
      if ($this->configuration[$platform]['link']) {
        $links[$platform] = array(
          'title' => $name,
          'url' => Url::fromUri($this->configuration[$platform]['link']),
          'weight' => $this->configuration[$platform]['weight'],
          'attributes' => new Attribute(array('class' => 'social-link--' . $platform . ' icon-social-' . $platform)),
        );
      }
    }

    uasort($links, '\Drupal\Component\Utility\SortArray::sortByWeightElement');

    $build = array(
      '#theme' => 'calibr8_social_links',
      '#links' => $links,
    );

    return $build;
  }

}
