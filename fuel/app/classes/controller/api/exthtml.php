<?php
//
// Tモーション　詳細画面抽出
//
// GET
// html = 詳細画面のHTML
//
class Controller_Api_Exthtml extends Controller_Rest
{
    public function get_list(){

        //スクレイピングライブラリ読み込み
        require APPPATH.'vendor/simple_html_dom.php';
        $url  = Input::Get('url');
        $ch = curl_init($url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_TIMEOUT,10);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $html = curl_exec($ch);
        curl_close($ch);

        $html = str_get_html($html);

        //タイトル抽出
        $title = $html->find('meta[name=description]',0)->getAttribute('content');

        //共有ID
        $embed_id = $html->find('source',0)->getAttribute('src');
        $colums = explode('/',$embed_id);
        $embed_id = $colums[5];

        //共有タグ
        $embed_tag = '<iframe width="640" height="360" src="https://www.tokyomotion.net/embed/'.$embed_id.'" frameborder="0" allowfullscreen></iframe>';

        //サムネイル画像保存
        $img_url = $html->find('meta[property^=og:image]',0)->getAttribute('content');
        $context = stream_context_create(array(
            'http' => array('ignore_errors' => true)
        ));
        $img = file_get_contents($img_url,false,$context);
        file_put_contents('./assets/img/thumb/' .$embed_id.'.jpg' , $img);

        //検索タグ抽出
        $keywords = $html->find('meta[name=keywords]',0)->getAttribute('content');
        $keywords = explode(',',$keywords);

        try{
            //動画ＴＢＬ更新
            $query = DB::insert('movies');
            $query->set(array(
                'site_id'  => 'TOKYOMOTION',
                'movie_id' => $embed_id,
                'title'    => $title,
                'embed_tag' => $embed_tag,
                'created_at' => date('Y-m-d H:i:s'),));
            $query->execute();
            $query->reset();

            //検索タグＴＢＬ更新
            foreach ($keywords as $keyword) {
                $query = DB::insert('tags');
                $query->set(array(
                    'movie_id' => $embed_id,
                    'keyword' => trim(mb_convert_kana($keyword, "s", 'UTF-8')), //全角空白のtrim
                    'created_at' => date('Y-m-d H:i:s'),));
                $query->execute();
                $query->reset();
            }

        }catch (Exception $e){
            Log::info('ExtractionAPI TOKYOMOTION Excepiton');
        }

        return ;
    }
}
?>