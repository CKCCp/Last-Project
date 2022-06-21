<?php

namespace Drupal\techtv\Plugin\rest\resource;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Serializer;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "rest_user",
 *   label = @Translation("Custom Rest user"),
 *   uri_paths = {
 *     "create" = "/api/user",
 *     "canonical" = "/api/user"
 *   }
 * )
 */
class RestUser extends ResourceBase {

  /**
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request.
   *
   */
  public function get(Request $request) {

    return new JsonResponse(['data' => ""], 200);
  }

  /**
   * Create new user.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The HTTP response object.
   *
   */
  public function post(Request $request) {
    $data = json_decode($request->getContent(), TRUE);

    return new JsonResponse(['data' => ""], 200);
  }

  /**
   * Update user.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The HTTP response object.
   *
   */
  public function patch(Request $request) {
    $data = json_decode($request->getContent(), TRUE);

    return new JsonResponse(['data' => ""], 200);
  }
}
