<?php
//
// ホーム機能
//
// 役割：動画一覧画面へ遷移する
//
class Controller_Home extends Controller{
    public function action_index(){
        // 動画一覧画面へ
        Response::redirect('movielist');

        return ;
    }
}

