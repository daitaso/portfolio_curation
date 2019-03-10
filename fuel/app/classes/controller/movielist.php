<?php
//
// 動画一覧画面（Controller)
//
// 役割：動画一覧画面のController
//
class Controller_MovieList extends Controller{

    public function action_index(){

        //view構築
        $view = View::forge('template/index');
        $view->set('head',View::forge('template/head'));
        $view->set('header',View::forge('template/header'));
        $child_view = View::forge('movielist');
        $view->set('contents',$child_view);
        $view->set('footer',View::forge('template/footer'));
        $child_view = View::forge('template/script');
        $child_view->set('jsname','movieList');
        $view->set('script',$child_view);

        return $view;

    }
}

