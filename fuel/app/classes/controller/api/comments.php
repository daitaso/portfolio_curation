<?php
//
// CommentsテーブルＡＰＩ
//
// 役割：DBのコメントテーブルへのアクセスAPI
//
class Controller_Api_Comments extends Controller_Rest{

    //
    // コメントリストの取得
    //
    // パラメータ（GET)
    // :movie_id 動画ID
    //
    // 返却値
    // :CommentsテーブルクエリのJSON
    //
    //
    public function get_list(){

        $movie_id  = Input::Get('movie_id');
        try {
            $result = DB::query('select * from comments where movie_id = ' . '\'' . $movie_id . '\'' . ' order by created_at desc', DB::SELECT)->execute();
        }catch(Exception $e){
            Log::info('CommentsAPI get_list Excepiton');
        }

        return $this->response(array(
            'comment_list' => $result
        ));
    }

    //
    // コメントの投稿
    //
    // パラメータ（POST)
    // :movie_id 動画ID
    // :comment  投稿されたコメントの本文
    // :review   投稿された５段階評価値
    //
    // 返却値 なし
    //
    public function post_list(){

        //入力パラメータ取得
        $movie_id = Input::json('movie_id');
        $comment  = Input::json('comment');
        $review   = Input::json('review');

        //投稿者の名前
        $user_name = '';
        if(Auth::check()){
            //ログイン時はユーザ名を使用
            $user_name = Auth::get_screen_name();
        }else{
            $user_name = 'ゲスト';
        }

        //DBへ挿入
        try {
            DB::insert('comments')->set(array('movie_id' => $movie_id, 'user_name' => $user_name, 'comment' => $comment, 'review' => $review, 'created_at' => date('Y-m-d H:i:s')))->execute();
        }catch(Exception $e){
            Log::info('CommentsAPI post_list Excepiton');
        }

        return ;
    }


}