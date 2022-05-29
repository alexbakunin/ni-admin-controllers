<?php


namespace app\controllers\admin;


use kronion\App;

trait TParamsImg
{
    private static $wmax;
    private static $hmax;

    private function getParamsImg(){
        if($_POST['name'] == 'single' && $_POST['dir'] == 'product-h450'){
            self::$wmax = App::$app->getProperty('img_width');
            self::$hmax = App::$app->getProperty('img_height');
        }elseif($_POST['name'] == 'single' && $_POST['dir'] == 'brand'){
            self::$wmax = App::$app->getProperty('img_brand_width');
            self::$hmax = App::$app->getProperty('img_brand_height');
        }elseif($_POST['name'] == 'single' && $_POST['dir'] == 'post'){
            self::$wmax = App::$app->getProperty('img_post_width');
            self::$hmax = App::$app->getProperty('img_post_height');
        }else{
            self::$wmax = App::$app->getProperty('gallery_width');
            self::$hmax = App::$app->getProperty('gallery_height');
        }
    }

}