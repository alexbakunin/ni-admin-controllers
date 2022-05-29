<?php


namespace app\controllers\admin;


use app\models\admin\Brand;
use app\models\AppModel;
use kronion\App;

class BrandController extends AppAdminController
{
    use TParamsImg;

    public function indexAction(){
        $this->setMeta('Список брендов');
    }

    public function addImageAction()
    {
        if(isset($_GET['upload'])){
            $name = $_POST['name'];
            $dir = $_POST['dir'];
            self::getParamsImg();
            $product = new Brand();
            $product->uploadImg($name, $dir, self::$wmax, self::$hmax);
        }
    }


    public function addAction(){
        if(!empty($_POST)){
            $brand = new Brand();
            $data = $_POST;
            $brand->load($data);
            $brand->getImg();

            if(!$brand->validate($data)){
                $brand->getErrors();
                $_SESSION['form_data'] = $data;
                redirect();
            }

            if($id = $brand->save('brand')){
                $alias = AppModel::createAlias('brand', 'alias', $data['title'], $id);
                $b = \R::load('brand', $id);
                $b->alias = $alias;
                \R::store($b);
                $_SESSION['success'] = 'Бренд добавлен';
            }
            redirect();
        }

        $this->setMeta('Новый бренд');
    }


    public function editAction(){
        if(!empty($_POST)){
            $id = $this->getRequestID(false);
            $brand = new Brand();
            $data = $_POST;
            $brand->load($data);
            $brand->getImg();
            if(!$brand->validate($data)){
                $brand->getErrors();
                redirect();
            }
            if($brand->update('brand', $id)){
                $alias = AppModel::createAlias('brand', 'alias', $data['title'], $id);
                $brand = \R::load('brand', $id);
                $brand->alias = $alias;
                \R::store($brand);
                $_SESSION['success'] = 'Изменения сохранены';
                redirect();
            }
        }

        $id = $this->getRequestID();
        $brand = \R::load('brand', $id);

        $this->setMeta("Редактирование бренда {$brand->title}");
        $this->set(compact('brand'));
    }


    public function deleteAction(){
        $id = $this->getRequestID();
        $products = \R::count('product', 'brand_id = ?', [$id]);
        if($products){
            $_SESSION['error'] = "Удаление невозможно - есть товары этого бренда";
            redirect();
        }
        $brand = \R::load('brand', $id);
        \R::trash($brand);
        if ($brand->img != 'no-image.png') @unlink(WWW . "/image/brand/" . $brand->img);
        $_SESSION['success'] = 'Бренд удален';
        redirect();
    }


}