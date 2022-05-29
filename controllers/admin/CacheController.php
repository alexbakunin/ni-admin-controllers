<?php

namespace app\controllers\admin;

use kronion\Cache;

class CacheController extends AppAdminController
{

    public function indexAction(){
        $this->setMeta('Очистка кэша');
    }

    public function deleteAction(){
        $key = isset($_GET['key']) ? $_GET['key'] : null;
        $cache = Cache::instance();
        switch($key){
            case 'category':
                $cache->delete('cats');
                $cache->delete('shop_menu');
                break;
            case 'brands':
                $cache->delete('brands');
                $cache->delete('ishop_brand');
                $cache->delete('ishop_brand_logo');
                $cache->delete('ishop_brand_filter');
                break;
            case 'hit':
                $cache->delete('hit');
                $cache->delete('hit_post');
                $cache->delete('hit_sidebar');
                $cache->delete('post_sidebar');
                break;
        }
        $_SESSION['success'] = 'Выбранный кэш удален';
        redirect();
    }

}