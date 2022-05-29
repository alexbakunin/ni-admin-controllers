<?php


namespace app\controllers\admin;


use app\models\admin\Post;
use kronion\App;
use kronion\libs\Pagination;

class PostController extends AppAdminController
{
    use TParamsImg;

    public function indexAction(){
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perpage = App::$app->getProperty('pagination_admin');
        $count = \R::count('post');
        $pagination = new Pagination($page, $perpage, $count);
        $start = $pagination->getStart();
        $posts = \R::find('post', "ORDER BY date DESC LIMIT $start, $perpage");
        $this->setMeta('Все статьи');
        $this->set(compact('posts', 'pagination', 'count'));
    }

    public function addImageAction()
    {
        if(isset($_GET['upload'])){
            $name = $_POST['name'];
            $dir = $_POST['dir'];
            self::getParamsImg();
            $product = new Post();
            $product->uploadImg($name, $dir, self::$wmax, self::$hmax);
        }
    }

    public function addAction(){
        if(!empty($_POST)){
            $post = new Post();
            $data = $_POST;
            $post->load($data);
            $post->attributes['status'] = $post->attributes['status'] ? 'show' : 'hide';
            $post->getImg();
            $post->attributes['date'] = date_create();

            if(!$post->validate($data)){
                $post->getErrors();
                $_SESSION['form_data'] = $data;
                redirect();
            }

            if($id = $post->save('post')){
                $_SESSION['success'] = 'Статья добавлена';
            }
            redirect();
        }

        $this->setMeta('Новая статья');
    }

    public function editAction(){
        if(!empty($_POST)){
            $id = $this->getRequestID(false);
            $post = new Post();
            $data = $_POST;
            $post->load($data);
            $post->attributes['status'] = $post->attributes['status'] ? 'show' : 'hide';
            $post->getImg();
            $post->attributes['date'] = date_create();
            if(!$post->validate($data)){
                $post->getErrors();
                redirect();
            }
            if($post->update('post', $id)){
                $_SESSION['success'] = 'Изменения сохранены';
                redirect();
            }
        }

        $id = $this->getRequestID();
        $post = \R::load('post', $id);
        $this->setMeta("Редактирование статьи {$post->title}");
        $this->set(compact('post'));
    }

    public function deleteAction(){
        $id = $this->getRequestID();
        $post = \R::load('post', $id);
        \R::trash($post);
        if ($post->img != 'no-image.png') @unlink(WWW . "/image/post/" . $post->img);
        $_SESSION['success'] = "Статья удалена";
        redirect();
    }



}