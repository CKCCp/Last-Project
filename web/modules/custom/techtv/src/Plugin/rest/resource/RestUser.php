<?php

namespace Drupal\techtv\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "rest_user",
 *   label = @Translation("Custom Rest user"),
 *   uri_paths = {
 *     "create" = "/api/user/create",
 *     "canonical" = "/api/user/{id}"
 *   }
 * )
 */
class RestUser extends ResourceBase {

  /**
   * Get list user.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The HTTP response object.
   *
   */
  public function get($id) {
    if ($id == 'list') {
      $uid = \Drupal::entityQuery('user')->execute();
      $users = User::loadMultiple($uid);
      $res = [];
      foreach ($users as $key => $user) {
        if (!empty($user->getAccountName())) {
          $res[$user->id()]['username'] = $user->getAccountName();
          $res[$user->id()]['status'] = $user->isActive() ? 'active' : 'block';
        }
      }
      return new JsonResponse($res, 200);
    }
    return new JsonResponse(['message' => 'Invalid request api!'], 400);
  }

  /**
   * Create new user.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The HTTP response object.
   *
   */
  public function post(Request $request) {
    $data = json_decode($request->getContent());
    $user = User::create();
    $user->setPassword($data->password);
    $user->enforceIsNew();
    $user->setEmail($data->email);
    $user->setUsername($data->username);
    $user->set('field_first_name', $data->field_first_name);
    $user->set('field_last_name', $data->field_last_name);
    $user->set('field_mobile', $data->field_mobile);
    $user->set('user_picture', $data->user_picture);
    $user->activate();
    $user->save();

    return new JsonResponse(['message' => 'Create success!'], 200);
  }

  /**
   * Update user.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The HTTP response object.
   *
   */
  public function patch(Request $request, $id) {
    $data = json_decode($request->getContent());
    $user = User::load($id);
    if (empty($user)) {
      return new JsonResponse(['message' => "User not found!"], 404);
    }
    else {
      $user->setPassword($data->password);
      $user->setEmail($data->email);
      $user->set('name',$data->username);
      $user->set('field_first_name', $data->field_first_name);
      $user->set('name', $data->email);
      $user->set('field_last_name', $data->field_last_name);
      $user->set('field_mobile', $data->field_mobile);
      $user->set('user_picture', $data->user_picture);
      $user->save();
    }

    return new JsonResponse(['message' => "Update success!"], 200);
  }

  /**
   * Delete user.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The HTTP response object.
   *
   */
  public function delete($id) {
    $user = User::load($id);
    if (empty($user)) {
      return new JsonResponse(['message' => "User not found!"], 404);
    }
    else {
      $user->delete();
    }
    return new JsonResponse(['message' => "Delete success!"], 200);
  }
}
