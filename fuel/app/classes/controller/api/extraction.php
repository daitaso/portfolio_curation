<?php
//
// 動画サイト情報抽出ＡＰＩ
//
// 役割：下記URLのHTMLを解析し、動画情報（動画ＩＤ、サムネ、タイトル、検索タグ、共有タグ）を抽出し、ＤＢに格納する
//
// 対象URL
//   TOKYOMOTION（https://www.tokyomotion.net/videos）
//       XVIDEOS（https://www.xvideos.com/lang/japanese）
//           FC2（https://video.fc2.com/a/search/video/free/?category_id=30）
//
class Controller_Api_Extraction extends Controller_Rest
{
    public function get_list(){

        set_time_limit(300);    //処理時間が長いためタイムアウト時間を5分延長

        //TOKYO MOTION
        $url = 'https://www.tokyomotion.net/videos';

        $ch = curl_init($url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_TIMEOUT,10);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);

        $html    = curl_exec($ch);
        curl_close($ch);

        //スクレイピングライブラリ読み込み
        require APPPATH.'vendor/simple_html_dom.php';

        $html = str_get_html($html);

        //詳細ページへのa要素のリストを取得
        $as = $html->find('a[href^=/video/]');

        foreach( $as as $a ){

            //詳細ページへのURLを解析
            $detail_url = $a->getAttribute('href');

            //ムービーID切り出し
            $dirs = explode('/',$detail_url);
            $movie_id = $dirs[2];

            //日本語部分をエンコード
            $detail_url = $dirs[0].'/'.$dirs[1].'/'.$dirs[2].'/'.urlencode($dirs[3]);

            //サムネイル画像保存
            $img_url = $a->find('img[src^=https://cdn.tokyo-motion.net/media/videos/]',0)->getAttribute('src');
            $context = stream_context_create(array(
                'http' => array('ignore_errors' => true)
            ));
            $img = file_get_contents($img_url,false,$context);
            file_put_contents('./assets/img/thumb/' .$movie_id.'.jpg' , $img);

            //タイトル抽出
            $title = $a->find('img[src^=https://cdn.tokyo-motion.net/media/videos/]',0)->getAttribute('title');

            //詳細ページ取得
            $ch = curl_init('https://www.tokyomotion.net'.$detail_url);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
            curl_setopt($ch,CURLOPT_TIMEOUT,10);
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);

            $detail_html = curl_exec($ch);
            curl_close($ch);

            $detail_html = str_get_html($detail_html);

            //共有タグ
            $embed_tag = $detail_html->find('iframe')[0]->outertext;

            try{
                $query = DB::insert('movies');
                $query->set(array(
                    'site_id'  => 'TOKYOMOTION',
                    'movie_id' => $movie_id,
                    'embed_tag'    => $embed_tag,
                    'title'    => $title,
                    'created_at' => date('Y-m-d H:i:s'),));
                $query->execute();
                $query->reset();

                //検索タグ抽出
                $keywords = $detail_html->find('meta[name=keywords]',0)->getAttribute('content');
                $keywords = explode(',',$keywords);
                foreach ($keywords as $keyword) {
                    $query = DB::insert('tags');
                    $query->set(array(
                        'movie_id' => $movie_id,
                        'keyword' => trim(mb_convert_kana($keyword, "s", 'UTF-8')), //全角空白のtrim
                        'created_at' => date('Y-m-d H:i:s'),));
                    $query->execute();
                    $query->reset();
                }
            }catch (Exception $e){
                Log::info('ExtractionAPI TOKYOMOTION Excepiton');
            }
        }

        //XVIDEOS
        $url = 'https://www.xvideos.com/lang/japanese';

        $ch = curl_init($url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_TIMEOUT,10);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
        $html = curl_exec($ch);
        curl_close($ch);

        $html = str_get_html($html);
        $blocks = $html->find('div.thumb-block');

        foreach( $blocks as $block ) {

            //ムービーID
            $colums = explode('_',$block->id);
            $movie_id = $colums[1];

            //タイトル
            $title = $block->find('div.thumb-under',0)->find('a',0)->getAttribute('title');

            //サムネイル画像保存
            $img_url = $block->find('div.thumb',0)->find('img[data-src^=https://img-]',0)->getAttribute('data-src');
            $img_url = str_replace('THUMBNUM','1',$img_url);
            $img = file_get_contents($img_url);
            file_put_contents('./assets/img/thumb/' .$movie_id.'.jpg' , $img);

            //詳細ページ取得
            $detail_url = 'https://www.xvideos.com'.$block->find('div.thumb',0)->find('a',0)->getAttribute('href');

            $ch = curl_init($detail_url);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
            curl_setopt($ch,CURLOPT_TIMEOUT,10);
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            $detail_html = curl_exec($ch);

            curl_close($ch);

            $detail_html = str_get_html($detail_html);

            //共有タグ
            $embed_tag = $detail_html->find('input#copy-video-embed',0);
            if($embed_tag === null){
                continue;
            }

            $embed_tag = $embed_tag->getAttribute('value');
            $embed_tag = htmlspecialchars_decode($embed_tag);

            //検索タグ抽出
            $keywords = $detail_html->find('a[href^=/tags/]');


            foreach($keywords as $keyword){
                Log::info($keyword->plaintext);
            }

            try{
                $query = DB::insert('movies');
                $query->set(array(
                    'site_id'  => 'XVIDEOS',
                    'movie_id' => $movie_id,
                    'embed_tag'    => $embed_tag,
                    'title'    => $title,
                    'created_at' => date('Y-m-d H:i:s'),));
                $query->execute();
                $query->reset();

                //検索タグ抽出
                foreach ($keywords as $keyword) {
                    $query = DB::insert('tags');
                    $query->set(array(
                        'movie_id' => $movie_id,
                        'keyword' => trim(mb_convert_kana($keyword->plaintext, "s", 'UTF-8')), //全角空白のtrim
                        'created_at' => date('Y-m-d H:i:s'),));
                    $query->execute();
                    $query->reset();
                }
            }catch (Exception $e){
                Log::info('ExtractionAPI XVIDEOS Excepiton');
            }
        }

        //FC2
        $url = 'https://video.fc2.com/a/search/video/free/?category_id=30';

        $ch = curl_init($url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_TIMEOUT,10);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
        $html = curl_exec($ch);
        curl_close($ch);

        $html = str_get_html($html);
        $blocks = $html->find('li.c-boxList-111_video');

        foreach( $blocks as $block ) {

            //ムービーID
            $movie_id = $block->find('div.c-image-101',0)->getAttribute('data-id');

            //サムネイル画像保存
            $style = $block->find('div.c-image-101_image',0)->getAttribute('style');
            $img_url = str_replace('background-image: url(','',$style);
            $img_url = str_replace(');','',$img_url);
            $img = file_get_contents($img_url);
            file_put_contents('./assets/img/thumb/' .$movie_id.'.jpg' , $img);

            //タイトル
            $title = $block->find('a.c-boxList-111_video_ttl',0)->getAttribute('title');


            //詳細ページ取得
            $detail_url = $block->find('a.c-boxList-111_video_ttl',0)->getAttribute('href');

            $ch = curl_init($detail_url);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
            curl_setopt($ch,CURLOPT_TIMEOUT,10);
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            $detail_html = curl_exec($ch);

            curl_close($ch);

            $detail_html = str_get_html($detail_html);

            //共有タグ
            $embed_tag = $detail_html->find('textarea.cont_v2_info_share030103',0);
            if($embed_tag === null){
                continue;
            }

            $embed_tag = htmlspecialchars_decode($embed_tag->plaintext);

            //検索タグ抽出
            $keywords = $detail_html->find('li.tag_lock');

            try{
                $query = DB::insert('movies');
                $query->set(array(
                    'site_id'  => 'FC2',
                    'movie_id' => $movie_id,
                    'embed_tag'    => $embed_tag,
                    'title'    => $title,
                    'created_at' => date('Y-m-d H:i:s'),));
                $query->execute();
                $query->reset();

                //検索タグ抽出
                foreach ($keywords as $keyword) {
                    $query = DB::insert('tags');
                    $query->set(array(
                        'movie_id' => $movie_id,
                        'keyword' => trim(mb_convert_kana($keyword->find('span',0)->plaintext, "s", 'UTF-8')), //全角空白のtrim
                        'created_at' => date('Y-m-d H:i:s'),));
                    $query->execute();
                    $query->reset();
                }
            }catch (Exception $e){
                Log::info('ExtractionAPI FC2 Excepiton');
            }
        }

        return ;
    }
}
?>