<?php

namespace app\controllers\admin;


use kronion\App;
use kronion\libs\Pagination;

class OrderController extends AppAdminController
{

    // просмотр всех заказов
    public function indexAction(){
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perpage = App::$app->getProperty('pagination_admin');
        $count = \R::count('orders');
        $pagination = new Pagination($page, $perpage, $count);
        $start = $pagination->getStart();

        $orders = \R::getAll("SELECT `orders`.`id`, `orders`.`user_id`, `orders`.`status`, DATE_FORMAT(`orders`.`date`, '%H:%i &nbsp; %d/%m/%y') AS `date`, DATE_FORMAT(`orders`.`update_at`, '%H:%i &nbsp; %d/%m/%y') AS `update_at`, `user`.`name`, SUM(`order_product`.`price`*`order_product`.`qty`) AS `sum` FROM `orders`
  JOIN `user` ON `orders`.`user_id` = `user`.`id`
  JOIN `order_product` ON `orders`.`id` = `order_product`.`order_id`
  GROUP BY `orders`.`id` ORDER BY `orders`.`status`, `orders`.`id` DESC LIMIT $start, $perpage");

        $this->setMeta('Список заказов');
        $this->set(compact('orders', 'pagination', 'count'));
    }

    // просмотр одного заказа
    public function viewAction(){
        $order_id = $this->getRequestID();
        $order = \R::getRow("SELECT `orders`.`id`, `orders`.`user_id`, `orders`.`status`, DATE_FORMAT(`orders`.`date`, '%H:%i &nbsp; %d/%m/%y') AS `date`, DATE_FORMAT(`orders`.`update_at`, '%H:%i &nbsp; %d/%m/%y') AS `update_at`, `orders`.`note`, `user`.`name`, `user`.`email`, `user`.`tel`, `user`.`address`, SUM(`order_product`.`price`*`order_product`.`qty`) AS `sum` FROM `orders`
  JOIN `user` ON `orders`.`user_id` = `user`.`id`
  JOIN `order_product` ON `orders`.`id` = `order_product`.`order_id`
  WHERE `orders`.`id` = ?
  GROUP BY `orders`.`id` ORDER BY `orders`.`status`, `orders`.`id` LIMIT 1", [$order_id]);
        if(!$order){
            throw new \Exception('Страница не найдена', 404);
        }
        $order_products = \R::getAll("SELECT `order_product`.*, `product`.`availability` AS `availability` FROM `order_product` 
        JOIN `product` ON `product`.`id` = `order_product`.`product_id` 
        WHERE `order_product`.`order_id` = ?", [$order_id]);
        $this->setMeta("Заказ №{$order_id}");
        $this->set(compact('order', 'order_products'));
    }

    // изменение статуса заказа
    public function changeAction(){
        $order_id = $this->getRequestID();
        if($_GET['status']=='checked' || $_GET['status']=='completed') $status = $_GET['status'];
        else $status = 'new';
        $order = \R::load('orders', $order_id);
        if(!$order){
            throw new \Exception('Страница не найдена', 404);
        }
        $order->status = $status;
        $order->update_at = date("Y-m-d H:i:s");
        \R::store($order);
        $_SESSION['success'] = 'Изменения сохранены';
        redirect();
    }

    // удаление заказа
    public function deleteAction(){
        $order_id = $this->getRequestID();
        $order = \R::load('orders', $order_id);
        \R::trash($order);
        $_SESSION['success'] = 'Заказ удален';
        redirect(ADMIN . '/order');
    }


}