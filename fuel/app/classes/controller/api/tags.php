<?php
// TagsテーブルＡＰＩ
//
// 役割：DBの検索タグテーブルへのアクセスAPI
//
class Controller_Api_Tags extends Controller_Rest{

    //
    // 検索タグを１０件取得し、返却する
    //
    // 返却値：検索タグテーブルクエリのJSON
    //
    public function get_list(){

        try {
            $result = DB::query('select distinct keyword from tags limit 10', DB::SELECT)->execute();
        }catch(Exception $e ){
            Log::info('TagsAPI get_list Excepiton '.$e->getMessage());
        }
        return $this->response(array(
            'tag_list' => $result
        ));

    }
}