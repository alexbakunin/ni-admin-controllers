<?php

namespace app\controllers\admin;

use app\models\admin\Product;
use app\models\AppModel;
use kronion\App;
use kronion\libs\Pagination;

class ProductController extends AppAdminController
{
    use TParamsImg;

    public function indexAction()
    {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perpage = App::$app->getProperty('pagination_admin');
        $count = \R::count('product');
        $pagination = new Pagination($page, $perpage, $count);
        $start = $pagination->getStart();
        $products = \R::getAll("SELECT product.*, category.title AS cat, brand.title AS brand FROM product JOIN category ON category.id = product.category_id JOIN brand ON brand.id = product.brand_id ORDER BY product.category_id, product.brand_id LIMIT ?, ?", [$start, $perpage]);
        $this->setMeta('Список товаров');
        $this->set(compact('products', 'pagination', 'count'));
    }

    public function addImageAction()
    {
        if(isset($_GET['upload'])){
            $name = $_POST['name'];
            $dir = $_POST['dir'];
            self::getParamsImg();
            $product = new Product();
            $product->uploadImg($name, $dir, self::$wmax, self::$hmax);
        }
    }

    public function addAction()
    {
        if(!empty($_POST)){
            $product = new Product();
            $data = $_POST;
            $product->load($data);
            $product->attributes['old_price'] = !empty($product->attributes['old_price']) ? $product->attributes['old_price'] : null;
            $product->attributes['availability'] = $product->attributes['availability'] ? 'yes' : 'no';
            $product->attributes['status'] = $product->attributes['status'] ? 'show' : 'hide';
            $product->attributes['new'] = $product->attributes['new'] ? 'yes' : 'no';
            $product->attributes['hit'] = $product->attributes['hit'] ? 'yes' : 'no';
            $product->getImg();

            if(!$product->validate($data)){
                $product->getErrors();
                $_SESSION['form_data'] = $data;
                redirect();
            }

            if($id = $product->save('product')){
                $alias = AppModel::createAlias('product', 'alias', $data['title'], $id);
                $p = \R::load('product', $id);
                $p->alias = $alias;
                \R::store($p);
                $_SESSION['success'] = 'Товар добавлен';
            }
            redirect();
        }

        $this->setMeta('Новый товар');
    }

    public function editAction()
    {
        if(!empty($_POST)){
            $id = $this->getRequestID(false);
            $product = new Product();
            $data = $_POST;
            $product->load($data);
            $product->attributes['old_price'] = !empty($product->attributes['old_price']) ? $product->attributes['old_price'] : null;
            $product->attributes['availability'] = $product->attributes['availability'] ? 'yes' : 'no';
            $product->attributes['status'] = $product->attributes['status'] ? 'show' : 'hide';
            $product->attributes['new'] = $product->attributes['new'] ? 'yes' : 'no';
            $product->attributes['hit'] = $product->attributes['hit'] ? 'yes' : 'no';
            $product->getImg();
            if(!$product->validate($data)){
                $product->getErrors();
                redirect();
            }
            if($product->update('product', $id)){
                $alias = AppModel::createAlias('product', 'alias', $data['title'], $id);
                $product = \R::load('product', $id);
                $product->alias = $alias;
                \R::store($product);
                $_SESSION['success'] = 'Изменения сохранены';
                redirect();
            }
        }

        $id = $this->getRequestID();
        $product = \R::load('product', $id);
        App::$app->setProperty('category_of_product', $product->category_id);
        App::$app->setProperty('brand_of_product', $product->brand_id);
        $this->setMeta("Редактирование товара {$product->title}");
        $this->set(compact('product'));
    }

    // изменение категории должно вести к изменению возможных фильтров (эффектов)
    public function changeEffectsAction()
    {
        $main_parent_cat_id = $_GET['mainparentcat'];
        new \app\widgets\effect\Effect( $main_parent_cat_id,
            [
                'tpl' => APP . '/widgets/effect/effect_tpl/effect_for_edit_product.php',
                'class' => 'admin-filter-effect',
            ]
        );
        exit();
    }

    public function deleteMainImgAction(){
        $id = isset($_POST['id']) ? $_POST['id'] : null;
        $src = isset($_POST['src']) ? $_POST['src'] : null;
        if(!$id || !$src){
            return;
        }
        if(\R::exec("UPDATE product SET img = ? WHERE id = ?", ['no_image.jpg', $id])){
            @unlink(WWW . "/images/$src");
            exit('1');                                                                              // возвращаем в AJAX '1'
        }
        return;
    }

    public function deleteAction()
    {
        $id = $this->getRequestID();
        $product = \R::load('product', $id);
        \R::trash($product);
        if ($product->img != 'no-image.png') @unlink(WWW . "/image/product-h450/" . $product->img);
        $_SESSION['success'] = "Товар удалён";
        redirect();
    }



}