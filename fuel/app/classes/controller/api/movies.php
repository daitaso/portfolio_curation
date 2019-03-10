<?php
//
// MoviesテーブルＡＰＩ
//
// 役割：DBの動画情報テーブルへのアクセスAPI
//
class Controller_Api_Movies extends Controller_Rest{

    const PAGE_DATA_NUM                  = 8;     //１ページあたりに表示する、明細データの表示件数
    const PAGENATION_MAX_PAGE_NUM        = 5;     //ページネーションで一度に表示する最大ページリンク数

    //
    // 動画一覧に表示する情報生成し、返却する
    //
    //返却値：
    // 動画一覧リスト表示に必要な情報（JSON）
    //
    public function get_list(){

        //GETパラメータ取得
        $keyword  = Input::Get('keyword');
        $page     = Input::Get('page');
        $favorite = Input::Get('favorite');
        $category = Input::Get('category');
        $show_keyword  = false;
        $show_category = false;

        if(is_null($page)){
            $page = 1;  //page指定が無い時は1ページ目とみなす
        }
        $limit_offset = 'limit '.self::PAGE_DATA_NUM.' offset '.(($page - 1) * self::PAGE_DATA_NUM);

        $sql = '';
        if(is_null($favorite)) {
            try {
                //通常検索orタグ検索orカテゴリー検索
                if(!is_null($keyword)){
                    $sql = 'select * from movies inner join tags on movies.movie_id = tags.movie_id where tags.keyword = \''.$keyword.'\' order by movies.created_at desc ';
                    $show_keyword = true;
                }else if(!is_null($category)) {
                    $sql = 'select * from movies where movies.site_id = \''.$category.'\' order by movies.created_at desc ';
                    $show_category = true;
                }else{
                    $sql = 'select * from movies order by movies.created_at desc ';
                }
            }catch (Exception $e){
                Log::info('MoviesAPI get_list Excepiton');
            }
        }else{
            //お気に入り一覧
            $sql = 'select * from movies inner join favorites on movies.movie_id = favorites.movie_id where favorites.username = \''.Auth::get_screen_name().'\'' ;
        }
        //レコード総件数取得
        $result = DB::query($sql,DB::SELECT)->execute();
        $total_rec_num = count($result);

        //カレントページ分のレコードを取得
        $sql = $sql.$limit_offset;
        $result = DB::query($sql,DB::SELECT)->execute();

        //全データから生成される全ページ数を求める
        $total_page_num = (int)floor($total_rec_num / self::PAGE_DATA_NUM);
        if($total_rec_num % self::PAGE_DATA_NUM !== 0) ++$total_page_num;

        //開始ページ数（ページネーション）
        $start_page = $page;
        for($i = 1; $i <= ((self::PAGENATION_MAX_PAGE_NUM - 1) / 2);++$i){
            if($page - $i === 0) break;
            $start_page = $page - $i;
        }

        //終了ページ数（ページネーション）
        $end_page = $start_page + self::PAGENATION_MAX_PAGE_NUM - 1;
        if($end_page > $total_page_num) $end_page = $total_page_num;

        //カレントページが全データ中最後のページと、その１つ前の時に、開始ページを補正する
        if((int)$page === $total_page_num     && $start_page - 2 > 0) $start_page -= 2;
        if((int)$page === $total_page_num - 1 && $start_page - 1 > 0) $start_page -= 1;

        //ページ番号配列作成
        $pages = range($start_page,$end_page);

        //表示データインデックス（ヘッダー用）
        $start_idx = ($page - 1) * self::PAGE_DATA_NUM;
        $end_idx   = $start_idx + count($result);

        return $this->response(array(
            'movie_list' => $result,
            'pages'      => $pages,
            'cur_page'   => $page,
            'start_idx'  => $start_idx,
            'end_idx'    => $end_idx,
            'total_rec_num' => $total_rec_num,
            'keyword'    => $keyword,
            'category'   => $category,
            'show_keyword' => $show_keyword,
            'show_category' => $show_category
        ));
    }
}