<?php

namespace app\controllers\admin;

use kronion\App;
use kronion\libs\Pagination;
use app\models\User;

class UserController extends AppAdminController
{

    public function loginAdminAction(){
        if(!empty($_POST)){
            $user = new User();
            if(!$user->login(true)){
                $_SESSION['error'] = 'Логин/пароль введены неверно';
            }
            if(User::isAdmin()){
                redirect(ADMIN);
            }else{
                redirect();
            }
        }
        $this->layout = 'login';

    }

    public function indexAction(){
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perpage = App::$app->getProperty('pagination_admin');
        $count = \R::count('user');
        $pagination = new Pagination($page, $perpage, $count);
        $start = $pagination->getStart();
        $users = \R::findAll('user', "LIMIT $start, $perpage");
        $this->setMeta('Список пользователей');
        $this->set(compact('users', 'pagination', 'count'));
    }

    public function addAction(){
        $this->setMeta('Новый пользователь');
    }

    public function signupAction(){
        if(!empty($_POST)):
            $user = new User();
            $data = $_POST;
            $user->load($data);
            if( !$user->validate($data) || !$user->checkUnique() || !$user->checkVerifyPassword() ):
                $user->getErrors();
                $_SESSION['form_data'] = $data;
                redirect();
            else:
                $user->attributes['password'] = password_hash($user->attributes['password'], PASSWORD_DEFAULT);
                if($user_id = $user->save('user')):
                    $_SESSION['success'] = 'Новый пользователь зарегистрирован';
                else:
                    $_SESSION['error'] = 'Ошибка!';
                endif;
            endif;
            redirect();
        endif;
        $this->setMeta('Регистрация нового пользователя');
    }

    public function editAction(){
        if(!empty($_POST)):
            $id = $this->getRequestID(false);
            $user = new \app\models\admin\User();
            $data = $_POST;
            $user->load($data);
            if(!$user->attributes['password']): unset($user->attributes['password']);
            else: $user->attributes['password'] = password_hash($user->attributes['password'], PASSWORD_DEFAULT);
            endif;
            if(!$user->validate($data) || !$user->checkUnique()){
                $user->getErrors();
                $_SESSION['form_data'] = $data;
                redirect();
            }
            if($user->update('user', $id)){
                $_SESSION['success'] = 'Изменения сохранены';
            }
            redirect();
        endif;

        $user_id = $this->getRequestID();
        $user = \R::load('user', $user_id);

        // вставка для пагинации списка имеющихся заказов пользователя
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perpage = App::$app->getProperty('pagination_admin');
        $count = \R::count('orders', "WHERE user_id = {$user_id}");
        $pagination = new Pagination($page, $perpage, $count);
        $start = $pagination->getStart();

        $orders = \R::getAll("SELECT `orders`.`id`, `orders`.`user_id`, `orders`.`status`, `orders`.`date`, `orders`.`update_at`, SUM(`order_product`.`price`) AS `sum` FROM `orders`
                JOIN `order_product` ON `orders`.`id` = `order_product`.`order_id`
                WHERE user_id = ? GROUP BY `orders`.`id` ORDER BY `orders`.`status`, `orders`.`id` LIMIT ?, ?", [$user_id, $start, $perpage]);

        $this->setMeta('Редактирование профиля пользователя');
        $this->set(compact('user', 'orders', 'pagination', 'count'));
    }


}