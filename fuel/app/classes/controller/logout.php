<?php
//
// ログアウト機能
//
// 役割：ログアウトし、ホーム画面へ遷移する
//
class Controller_Logout extends Controller{

    public function action_index(){

        //ログアウト
        Auth::logout();

        // リダイレクト
        Response::redirect('home');

        return ;
    }
}

