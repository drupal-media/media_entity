<?php

/**
 * @file
 * Contains \Drupal\media_entity\Controller\MediaController.
 */

namespace Drupal\media_entity\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\media_entity\MediaBundleInterface;
use Drupal\media_entity\MediaInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for Media entity routes.
 */
class MediaController extends ControllerBase {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a new class instance.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(LanguageManagerInterface $language_manager, RendererInterface $renderer) {
    $this->languageManager = $language_manager;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('language_manager'),
      $container->get('renderer')
    );
  }

  /**
   * Displays a media.
   *
   * @param \Drupal\media_entity\MediaInterface $media
   *   The media we are displaying.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function page(MediaInterface $media) {
    $build = array(
      'media' => $this->entityManager()->getViewBuilder('media')->view($media),
    );

    $build['#attached']['html_head_link'][] = array(
      array(
        'rel' => 'canonical',
        'href' => $media->url('canonical'),
      ), TRUE);

    $build['#attached']['html_head_link'][] = array(
      array(
        'rel' => 'shortlink',
        'href' => $media->url('canonical', array('alias' => TRUE)),
      ), TRUE);

    return $build;
  }

  /**
   * The _title_callback for the entity.media.canonical route.
   *
   * @param \Drupal\media_entity\MediaInterface $media
   *   The current media.
   *
   * @return string
   *   The page title.
   */
  public function pageTitle(MediaInterface $media) {
    return $this->entityManager()->getTranslationFromContext($media)->label();
  }

  /**
   * Displays add media links for available media bundles.
   *
   * Redirects to media/add/[bundle] if only one bundle is available.
   *
   * @return array
   *   A render array for a list of the bundles that can be added; however,
   *   if there is only one defined for the site, the function
   *   redirects to the media add page for that one type and returns
   *   RedirectResponse.
   */
  public function addPage() {
    $build = [
      '#theme' => 'media_add_list',
      '#cache' => [
        'tags' => $this->entityManager()->getDefinition('media_bundle')->getListCacheTags(),
      ]
    ];

    $content = array();

    // Only use media bundles the user has access to.
    foreach ($this->entityManager()->getStorage('media_bundle')->loadMultiple() as $type) {
      $access = $this->entityManager()->getAccessControlHandler('media')->createAccess($type->id(), NULL, [], TRUE);
      if ($access->isAllowed()) {
        $content[$type->id()] = $type;
      }
      $this->renderer->addCacheableDependency($build, $access);
    }

    // Bypass the media/add listing if only one bundle is available.
    if (count($content) == 1) {
      $type = array_shift($content);
      return $this->redirect('media.add', array('media_bundle' => $type->id));
    }

    $build['#content'] = $content;

    return $build;
  }

  /**
   * Page callback: Provides the media submission form.
   *
   * @param \Drupal\media_entity\MediaBundleInterface $media_bundle
   *   The media bundle object for the submitted media.
   *
   * @return array
   *   A media submission form.
   */
  public function add(MediaBundleInterface $media_bundle) {
    $user = \Drupal::currentUser();

    $bundle = $media_bundle->id();
    $langcode = $this->moduleHandler()->invoke('language', 'get_default_langcode', array('media', $bundle));
    $media = $this->entityManager()->getStorage('media')->create(array(
      'uid' => $user->id(),
      'bundle' => $bundle,
      'langcode' => $langcode ? $langcode : $this->languageManager->getDefaultLanguage()->getId(),
    ));

    return $this->entityFormBuilder()->getForm($media);
  }

  /**
   * The _title_callback for the media.add route.
   *
   * @param \Drupal\media_entity\MediaBundleInterface $media_bundle
   *   The current media.
   *
   * @return string
   *   The page title.
   */
  public function addPageTitle(MediaBundleInterface $media_bundle) {
    return $this->t('Create @name', array('@name' => $media_bundle->label()));
  }
}
