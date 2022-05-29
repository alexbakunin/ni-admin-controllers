<?php

namespace app\controllers\admin;

use kronion\App;

class MainController extends AppAdminController
{

    public function indexAction(){
        $countNewOrders = \R::count('orders', "status = 'new'");
        $countUsers = \R::count('user');
        $countProducts = \R::count('product');
        $countCategories = \R::count('category');
        $this->setMeta('Панель управления '.App::$app->getProperty('shop_name'));
        $this->set(compact('countNewOrders', 'countCategories', 'countProducts', 'countUsers'));
    }

}