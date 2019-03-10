<?php
//
// お気に入り一覧画面（Controller）
//
// 役割：お気に入り一覧画面のController
//
class Controller_FavoriteList extends Controller{

    public function action_index(){

        //view構築
        $view = View::forge('template/index');
        $view->set('head',View::forge('template/head'));
        $view->set('header',View::forge('template/header'));
        $child_view = View::forge('favoritelist');
        $view->set('contents',$child_view);
        $view->set('footer',View::forge('template/footer'));
        $child_view = View::forge('template/script');
        $child_view->set('jsname','favoriteList');
        $view->set('script',$child_view);

        return $view;
    }
}

