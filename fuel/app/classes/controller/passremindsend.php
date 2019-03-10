<?php
//
// パスワードリマインダー画面（送信）（Controller)
//
// 役割：パスワードリマインダー画面（送信）のController
//
class Controller_PassRemindSend extends Controller{
    public function action_index(){

        // 入力フォーム設定
        $form = Fieldset::forge('');

        //Email
        $form->add('email', 'Ｅメール', array('type'=>'email', 'placeholder'=>'Ｅメール'))
            ->add_rule('required')
            ->add_rule('valid_email')
            ->add_rule('max_length', 255);

        //送信ボタン
        $form->add('submit', '', array('type'=>'submit', 'value'=>'送信する'));

        $error = '';
        if (Input::method() === 'POST') {
            //POSTならば送信ボタン処理

            $val = $form->validation();
            if ($val->run()) {
                //バリデーション正常終了
                $formData = $val->validated();

                //Email存在チェック
                $result = DB::query('select * from users where email = '.'\''. $formData['email'] . '\'', DB::SELECT)->execute();
                if (count($result) === 1) {
                    //EmailがＤＢに登録済

                    //認証キー生成
                    $auth_key = makeRandKey();

                    //メール送信
                    $email = Email::forge();
                    $email->from('info@ecuration.com');
                    $email->to($formData['email']);
                    $email->subject('【パスワード再発行認証】｜E-CURATION');
                    $url = Uri::base().'passremindreceive.php';
                    $honbun = <<<EOT
本メールアドレス宛にパスワード再発行のご依頼がありました。
下記のURLにて認証キーをご入力頂くとパスワードが再発行されます。

パスワード再発行認証キー入力ページ：{$url}
認証キー：{$auth_key}
※認証キーの有効期限は30分となります

認証キーを再発行されたい場合は下記ページより再度再発行をお願い致します。
{$url}
EOT;
                    $email->body($honbun);
                    $email->send();

                    //認証に必要な情報をセッションへ保存
                    Session::set('auth_key', $auth_key);
                    Session::set('auth_email', $formData['email']);
                    Session::set('auth_key_limit', time()+(60*30));

                    Session::set_flash('sucMsg','登録メールアドレス宛に再発行のご案内メールを送付しました！');

                    //パスワードリマインダー（受信）画面へ遷移
                    Response::redirect('passremindreceive');

                }else{
                    //DBに存在しないEmail
                    Session::set_flash('errMsg','認証に失敗しました！ユーザー登録されていないメールアドレスです！');
                }
            }else{
                // バリデーションエラー！画面表示用にエラー内容を格納
                $error = $val->error();
            }

            // フォームにPOSTされた値を再セット
            $form->repopulate();
        }

        //view構築
        $view = View::forge('template/index');
        $view->set('head',View::forge('template/head'));
        $view->set('header',View::forge('template/header'));
        $view->set('contents',View::forge('passremindsend'));
        $view->set('footer',View::forge('template/footer'));
        $view->set_global('passremindsend', $form->build(''), false);
        $view->set_global('error', $error);
        $child_view = View::forge('template/script');
        $child_view->set('jsname','passRemindSend');
        $view->set('script',$child_view);

        return $view;
    }
}
//
//認証キー生成
//
//役割：ユーザー認証用のキーを生成する。
//
function makeRandKey($length = 8) {
    static $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJLKMNOPQRSTUVWXYZ0123456789';
    $str = '';
    for ($i = 0; $i < $length; ++$i) {
        $str .= $chars[mt_rand(0, 61)];
    }
    return $str;
}
