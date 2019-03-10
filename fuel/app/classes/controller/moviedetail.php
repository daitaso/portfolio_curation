<?php
//
// 動画詳細画面（Controller)
//
// 役割：動画詳細画面のController
//
class Controller_MovieDetail extends Controller{

    public function action_index(){

        //GETパラメータで動画ＩＤ取得
        $movie_id = Input::Get('movie_id');

        //動画リストテーブルから情報を取得
        $result = DB::query('select * from movies where movie_id = '.'\''.$movie_id.'\'', DB::SELECT)->execute();

        //お気に入り情報存在チェック
        $isFavorite = false;
        if(Auth::check()){
            $result2 = DB::query('select * from favorites where movie_id = '.'\''.$movie_id.'\''.' and username = \''.Auth::get_screen_name().'\'', DB::SELECT)->execute();
            if(count($result2) === 1){
                //この動画はお気に入り登録されている
                $isFavorite = true;
            }
        }

        //view構築
        $view = View::forge('template/index');
        $view->set('head',View::forge('template/head'));
        $view->set('header',View::forge('template/header'));
        $child_view = View::forge('moviedetail');
        $child_view->set('embed_tag',$result[0]['embed_tag'],false);
        $child_view->set('title',$result[0]['title']);
        $child_view->set('movie_id',$movie_id);
        $child_view->set('isFavorite',$isFavorite);
        $view->set('contents',$child_view);
        $view->set('footer',View::forge('template/footer'));
        $child_view = View::forge('template/script');
        $child_view->set('jsname','movieDetail');
        $view->set('script',$child_view);

        return $view;
    }

}

