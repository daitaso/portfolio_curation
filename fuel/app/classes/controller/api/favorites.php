<?php
//
// FavoritesテーブルＡＰＩ
//
// 役割：DBのお気に入り情報テーブルへのアクセスAPI
//
class Controller_Api_Favorites extends Controller{

    //
    // お気に入り情報のトグル（存在したら削除し、無ければ挿入する）
    //
    // パラメータ（POST)
    // :movie_id 動画ID
    //
    // 返却値
    // なし
    //
    public function action_index(){

        if (Input::method() === 'POST' && Auth::check()) {

            $m_id = Input::post('movieId');
            try{
                $result = DB::query('select * from favorites where movie_id = '.'\''. $m_id . '\''.' and username = \'' . Auth::get_screen_name() . '\'', DB::SELECT)->execute();
                if (count($result) === 1) {
                    $result = DB::query('delete from favorites where movie_id = '.'\''. $m_id .'\''.' and username = \'' . Auth::get_screen_name() . '\'', DB::DELETE)->execute();
                } else {
                    DB::insert('favorites')->columns(array('movie_id', 'username', 'created_at'))->values(array($m_id, Auth::get_screen_name(), date('Y-m-d H:i:s')))->execute();
                }
            }catch (Exception $e){
                Log::info('FavoritesAPI  Excepiton');
            }
        }

        return ;
    }
}
?>