<?php

namespace Drupal\calibr8_socialmedia\Plugin\Block;

use Drupal\Component\Utility\SortArray;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Template\Attribute;

/**
 * Provides a 'Social media sharing' block.
 *
 * @Block(
 *   id = "calibr8_socialmedia_sharing",
 *   admin_label = @Translation("Social media sharing block"),
 * )
 */
class SocialMediaSharingBlock extends BlockBase  {

  /**
   * Return a list of available platforms
   */
  private function getPlatforms() {
    return array(
      'facebook' => $this->t('Facebook'),
      'linkedin' => $this->t('Linkedin'),
      'twitter' => $this->t('Twitter'),
      'googleplus' => $this->t('Google+'),
      'reddit' => $this->t('Reddit'),
      'mail' => $this->t('Mail'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $conf = array();
    foreach ($this->getPlatforms() as $platform => $name) {
      $conf[$platform]['enabled'] = '';
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
      '#header' => array(t('Platform'), t('Weight'), t('Enabled')),
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
        '#type' => 'checkbox',
        '#default_value' => $this->configuration[$platform]['enabled'],
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
      $this->configuration[$platform]['enabled'] = $form_state->getValue(array('table', $platform, 'element'));
      $this->configuration[$platform]['weight'] = $form_state->getValue(array('table', $platform, 'weight'));
    }

  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    global $base_url;
    $build = array();

    $current_path = \Drupal::service('path.current')->getPath();
    $path_alias = \Drupal::service('path.alias_manager')->getAliasByPath($current_path);
    $current_url = \Drupal\Core\Url::fromUserInput($path_alias, array('absolute' => TRUE))->toString();

    foreach ($this->getPlatforms() as $platform => $name) {
      if ($this->configuration[$platform]['enabled']) {
        $platformprefix = "";
        switch ($platform) {
          case 'facebook':
            $platformprefix = 'http://facebook.com/sharer.php?u=';
            break;
          case 'linkedin':
            $platformprefix = 'http://www.linkedin.com/shareArticle?url=';
            break;
          case 'twitter':
            $platformprefix = 'http://twitter.com/intent/tweet?url=';
            break;
          case 'mail':
            $platformprefix = 'mailto:?body=';
            break;
          case 'googleplus':
            $platformprefix = 'https://plus.google.com/share?url=';
            break;
          case 'reddit':
            $platformprefix = 'https://www.reddit.com/submit?url=';
            break;
          default:
            $platformprefix = '';
            break;
        }

        $links[$platform] = array(
          'title' => $name,
          'url' => $platformprefix . $current_url,
          'weight' => $this->configuration[$platform]['weight'],
          'attributes' => new Attribute(array('class' => 'social-share-link--' . $platform . ' icon-social-' . $platform/*, 'target' => "_blank"*/)),
        );
      }
    }

    uasort($links, '\Drupal\Component\Utility\SortArray::sortByWeightElement');

    $build = array(
      '#theme' => 'calibr8_social_sharing',
      '#links' => $links,
      '#attached' => array(
        'library' => array(
          'calibr8_socialmedia/calibr8_socialmedia',
        )
      ),
    );

    return $build;
  }

  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), array('route'));
  }

}
