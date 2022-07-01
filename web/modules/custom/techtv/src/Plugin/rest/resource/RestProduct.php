<?php

namespace Drupal\techtv\Plugin\rest\resource;

use Drupal\Core\Database\Query\Query;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Render\Markup;
use Drupal\jsonapi\JsonApiResource\Data;
use Drupal\node\Entity\Node;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\user\Entity\User;
use http\Header;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\Header\Headers;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "rest_product",
 *   label = @Translation("Custom Rest product"),
 *   uri_paths = {
 *     "create" = "/api/product/{type}/create",
 *     "canonical" = "/api/product/{type}/{id}"
 *   }
 * )
 */
class RestProduct extends ResourceBase {

  /**
   * Get list product.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The HTTP response object.
   *
   */
  public function get($type, $id, Request $request) {
    if ($id == 'list' && $type == 'tv') {
      $limit_by = $request->get('limit_by');
      $item_per_page = $request->get('item_per_page');
      $current_page = $request->get('current_page');
      $nids = \Drupal::entityQuery('node')->condition('type','tv')->execute();
      $nodes =  \Drupal\node\Entity\Node::loadMultiple($nids);
      $f = "Y/m/d H:i:s";
      $res = [];
      $i = 1;
      $res['pagination']['limit_by'] = (int) $limit_by;
      $res['pagination']['item_per_page'] = (int) $item_per_page;
      $res['pagination']['current_page'] = (int) $current_page;
      foreach ($nodes as $node) {
        if ($i < $limit_by) {
          if ($i > ($current_page != 1 ? $item_per_page * $current_page + 1 : 0) && $i < (min($limit_by, $item_per_page * $current_page) + 1)) {
            $res[$node->id()]['title'] = $node->get('title')->value;
            $res[$node->id()]['name'] = $node->get('field_n_tv_name')->value;
            $res[$node->id()]['description'] = $node->get('field_n_tv_description')->value;
            $res[$node->id()]['image'] = $node->get('field_n_tv_image')
              ->getValue();
            $res[$node->id()]['price'] = $this->validateNumber($node->get('field_n_tv_price')->value);
            $res[$node->id()]['discount'] = $this->validateNumber($node->get('field_n_tv_discount')->value);
            $res[$node->id()]['status'] = $node->get('status')->value == 1 ? 'open' : 'block';
            $res[$node->id()]['created'] = date_format(DrupalDateTime::createFromTimestamp($node->get('created')->value)
              ->getPhpDateTime(), $f);
            $res[$node->id()]['updated_by'] = User::load($node->getRevisionUserId())
              ->getAccountName();
            $res[$node->id()]['last_updated'] = date_format(DrupalDateTime::createFromTimestamp($node->get('changed')->value)
              ->getPhpDateTime(), $f);
          }
        }
        //continue here
        $i++;
      }
      $res['pagination']['total_page'] = ceil($limit_by / $item_per_page);
      return new JsonResponse($res, 200);
    }
    return new JsonResponse(['message' => 'Invalid request api!'], 400);
  }

  /**
   * Create new product.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The HTTP response object.
   *
   */
  public function post(Request $request, $type) {
    $data = json_decode($request->getContent());
    if ($type == 'tv') {
      $node = Node::create([
        'type' => $type,
        'title' => Markup::create($data->title),
        'field_n_tv_description' => Markup::create($data->description),
        'field_n_tv_discount' => $this->validateNumber($data->discount),
        'field_n_tv_image' => $data->image,
      ]);
      $node->save();
      return new JsonResponse(['message' => 'Create success!'], 200);
    }
    return new JsonResponse(['message' => 'Invalid request api!'], 400);
  }

  /**
   * @param $number
   *
   * @return float|int
   */
  public function validateNumber($number): float|int {
    if (empty($number) || !is_numeric($number)) {
      return 0;
    }
    return (float) $number;
  }

  /**
   * Update product.
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
   * Delete product.
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
