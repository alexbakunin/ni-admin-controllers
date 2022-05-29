<?php


namespace app\controllers\admin;


use app\models\admin\Effect;

class EffectController extends AppAdminController
{
    public function indexAction()
    {
        $main_cats = \R::getAll("SELECT id, title FROM category WHERE parent_id = 0");
        $effects = \R::findAll("filter");

        $this->set(compact('main_cats', 'effects'));
        $this->setMeta('Фильтры (Эффекты)');
    }

    public function addAction()
    {
        $main_parent_cat_id = !empty($_GET['cat-id']) ? $_GET['cat-id'] : null;
        if (!$main_parent_cat_id) redirect('/admin/order');
        // проверка "Главности" Категории
        $cat = \R::getRow("SELECT id, parent_id, title FROM category WHERE id = ?", [$main_parent_cat_id] );
        if ($cat['parent_id'] != 0) redirect();

        if(!empty($_POST)){
        $effect = new Effect();
        $data = $_POST;
        $effect->load($data);
            if(!$effect->validate($data)){
                $effect->getErrors();
                redirect();
            }
            if ($effect->save('filter')){
                $_SESSION['success'] = 'Фильтр добавлен';
            }
            redirect('/admin/effect?i='.$cat['id']);
        }

        $this->set(compact('cat'));
        $this->setMeta('Новый фильтр (эффект)');
    }

    public function editAction()
    {
        if (!empty($_POST)){
            $id = $this->getRequestID(false);
            $effect = new Effect();
            $data = $_POST;
            $effect->load($data);
            if(!$effect->validate($data)){
                $effect->getErrors();
                redirect();
            }
            if ($effect->update('filter', $id)){
                $_SESSION['success'] = 'Изменения сохранены';
            }else{
                $effect->getErrors();
                redirect();
            }
            redirect('/admin/effect?i='.$effect->attributes['main_category_id']);
        }

        $id = $this->getRequestID();
        $effect = \R::load('filter', $id);

        $this->set(compact('effect'));
        $this->setMeta('Изменить/удалить фильтр (эффект)');
    }

    public function deleteAction()
    {
        $id = $this->getRequestID();
        $count_products = \R::count('product', 'effect_id = ?', [$id]);
        if ($count_products){
            $_SESSION['error'] = 'Удаление невозможно - этот фильтр присутствует в '.$count_products.' товар'.pluralForm($count_products, 'е', 'ах', 'ах');
            redirect();
        }
        $effect = \R::load('filter', $id);
        \R::trash($effect);
        $_SESSION['success'] = 'Фильтр удалён';
        redirect('/admin/effect');
    }


}