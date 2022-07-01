<?php

namespace Drupal\techtv\Plugin\rest\resource;

use Drupal\Core\File\FileSystemInterface;
use Drupal\media\Entity\Media;
use Drupal\rest\Plugin\ResourceBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "rest_image",
 *   label = @Translation("Custom Rest image"),
 *   uri_paths = {
 *     "create" = "/api/image/create",
 *     "canonical" = "/api/image/list"
 *   }
 * )
 */
class RestImage extends ResourceBase {
  /**
   * Get list image.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The HTTP response object.
   *
   */
  public function get() {
    $ids = \Drupal::entityQuery('media')
      ->condition('bundle', 'image')
      ->execute();
    $medias = Media::loadMultiple($ids);
    $response = [];
    foreach ($medias as $key => $media) {
      foreach ($media->getFields() as $value) {
        $response[$ids[$key]] = $value->getValue();
      }
    }
    return new JsonResponse($response, 200);
  }

  /**
   * Create new image.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The HTTP response object.
   *
   */
  public function post(Request $request) {
    $data = json_decode($request->getContent());
    $directory = 'public://media';
    $url = $data->url;

    if(\Drupal::service('file_system')->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY)) {
      $file = system_retrieve_file(trim($url), $directory, true);
    }
    \Drupal::entityTypeManager()
      ->getStorage('media')
      ->create([
        'bundle' => 'image',
        'name' => $data->name,
        'field_media_image' => [
          'target_id' => $file->id(),
        ],
      ])->save();
    return new JsonResponse(['message' => 'Create success!'], 200);
  }
}
