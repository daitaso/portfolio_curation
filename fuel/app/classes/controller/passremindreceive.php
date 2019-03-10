<?php
//
// パスワードリマインダー画面（受信）（Controller)
//
// 役割：パスワードリマインダー画面（受信）のController
//
class Controller_PassRemindReceive extends Controller{

    public function action_index(){

        //パスワードリマインダー送信側からメール送信済みか？
        $auth_key = Session::get('auth_key');
        if ( $auth_key === false ){
            //未送信なら、送信画面へ遷移
            Response::redirect('passremindsend');
        }

        //入力フォーム設定
        $form = Fieldset::forge('');

        //認証キー
        $form->add('authkey', '認証キー', array('type'=>'text', 'placeholder'=>'認証キー'))
            ->add_rule('required')
            ->add_rule('exact_length', 8);

        //送信ボタン
        $form->add('submit', '', array('type'=>'submit', 'value'=>'再発行する'));

        $error = '';
        if (Input::method() === 'POST'){
            //postならば再発行処理

            $val = $form->validation();
            if ($val->run()) {
                //バリデーションＯＫ

                //認証キーの照合
                $auth_ok = true;
                if(Input::post('authkey') !== Session::get('auth_key')){
                    //違うキーが入力された
                    Session::set_flash('errMsg','認証に失敗しました！認証キーが正しくありません！');
                    Session::delete('auth_key');
                    Session::delete('auth_email');
                    Session::delete('auth_key_limit');
                    $auth_ok = false;

                }
                if(time() > Session::get('auth_key_limit')){
                    //期限切れ
                    Session::set_flash('errMsg','認証に失敗しました！認証キーの期限が切れています！');
                    Session::delete('auth_key');
                    Session::delete('auth_email');
                    Session::delete('auth_key_limit');
                    $auth_ok = false;

                }

                if($auth_ok){
                    //認証ＯＫ
                    //パスワードリセット
                    $result = DB::select()->from('users')->where('email',Session::get('auth_email') )->execute();
                    $username = $result[0]['username'];
                    $new_password = Auth::reset_password($username);

                    //新しいパスワードをメールで送信
                    $email = Email::forge();
                    $email->from('info@ecuration.com');
                    $email->to(Session::get('auth_email'));
                    $email->subject('【パスワード再発行完了】｜E-CURATION');
                    $url = Uri::base().'login.php';
                    $honbun = <<<EOT
本メールアドレス宛にパスワードの再発行を致しました。
下記のURLにて再発行パスワードをご入力頂き、ログインください。

ログインページ：{$url}
再発行パスワード：{$new_password}
※ログイン後、パスワードのご変更をお願い致します
EOT;
                    $email->body($honbun);
                    $email->send();

                    //セッション削除
                    Session::delete('auth_key');
                    Session::delete('auth_email');
                    Session::delete('auth_key_limit');

                    Session::set_flash('sucMsg','認証成功！登録メールアドレス宛に新しいパスワードを送付しました！');

                    //ログイン画面へ遷移
                    Response::redirect('login');
                }
            } else {
                // バリデーションエラー！画面表示用にエラー内容を格納
                $error = $val->error();
            }
            // フォームにPOSTされた値をセット
            $form->repopulate();
        }

        //変数としてビューを割り当てる
        $view = View::forge('template/index');
        $view->set('head',View::forge('template/head'));
        $view->set('header',View::forge('template/header'));
        $view->set('contents',View::forge('passremindreceive'));
        $view->set('footer',View::forge('template/footer'));
        $view->set_global('passremindreceive', $form->build(''), false);
        $view->set_global('error', $error);
        $vvv = View::forge('template/script');
        $vvv->set('jsname','passRemindReceive');
        $view->set('script',$vvv);

        // レンダリングした HTML をリクエストに返す
        return $view;
    }
}

//認証キー生成
function makeRandKey($length = 8) {
    static $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJLKMNOPQRSTUVWXYZ0123456789';
    $str = '';
    for ($i = 0; $i < $length; ++$i) {
        $str .= $chars[mt_rand(0, 61)];
    }
    return $str;
}


