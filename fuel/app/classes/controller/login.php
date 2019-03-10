<?php
//
// ログイン画面（Controller)
//
// 役割：ログイン画面のController
//
class Controller_Login extends Controller{
    public function action_index(){

        // 入力フォーム設定
        $form = Fieldset::forge('');

        //ユーザー名
        $form->add('username', 'ユーザー名', array('type'=>'text','placeholder'=>'ユーザー名'))
            ->add_rule('required')
            ->add_rule('min_length', 1)
            ->add_rule('max_length', 255);

        //パスワード
        $form->add('password', 'Password', array('type'=>'password','placeholder'=>'パスワード'))
            ->add_rule('required')
            ->add_rule('min_length', 6)
            ->add_rule('max_length', 20);

        //送信ボタン
        $form->add('submit', '', array('type'=>'submit', 'value'=>'ログイン'));

        $error = "";
        if (Input::method() === 'POST') {
            //POSTならばログイン処理

            $val = $form->validation();
            if ($val->run()) {
                //バリデーション正常終了
                $formData = $val->validated();

                //ログイン試行
                if(Auth::login($formData['username'], $formData['password'])){

                    //ログイン成功！
                    //ホーム画面へ遷移
                    Response::redirect('home');
                }else{
                    // ログイン失敗
                    Session::set_flash('errMsg','ログインに失敗しました！時間を置いてお試し下さい！');
                }
            } else {
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
        $view->set('contents',View::forge('auth/login'));
        $view->set('footer',View::forge('template/footer'));
        $view->set_global('login', $form->build(''), false);
        $view->set_global('error', $error);
        $child_view = View::forge('template/script');
        $child_view->set('jsname','login');
        $view->set('script',$child_view);

        return $view;
    }
}

