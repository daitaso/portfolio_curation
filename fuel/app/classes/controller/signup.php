<?php
//
// サインアップ画面（Controller)
//
// 役割：サインアップ画面のController
//
class Controller_Signup extends Controller{

    public function action_index(){

        // 入力フォーム設定
        $form = Fieldset::forge('');

        //ユーザー名
        $form->add('username', 'ユーザー名', array('type'=>'text', 'placeholder'=>'ユーザー名'))
            ->add_rule('required')
            ->add_rule('min_length', 1)
            ->add_rule('max_length', 255);

        //Email
        $form->add('email', 'Email', array('type'=>'email', 'placeholder'=>'Email'))
            ->add_rule('required')
            ->add_rule('valid_email')
            ->add_rule('min_length', 1)
            ->add_rule('max_length', 255);

        //パスワード
        $form->add('password', 'Password', array('type'=>'password', 'placeholder'=>'パスワード'))
            ->add_rule('required')
            ->add_rule('min_length', 6)
            ->add_rule('max_length', 20);

        //パスワード（再入力）
        $form->add('password_re', 'Password（再入力）', array('type'=>'password', 'placeholder'=>'パスワード（再入力）'))
            ->add_rule('match_field', 'password')
            ->add_rule('required')
            ->add_rule('min_length', 6)
            ->add_rule('max_length', 20);

        //送信ボタン
        $form->add('submit', '', array('type'=>'submit', 'value'=>'登録'));

        $error = '';
        if (Input::method() === 'POST') {
            //POSTならばユーザー登録処理

            $val = $form->validation();
            if ($val->run()) {
                //バリデーション正常終了
                $formData = $val->validated();

                //ユーザー生成試行
                $auth = Auth::instance();
                try{
                    if($auth->create_user($formData['username'], $formData['password'], $formData['email'])){

                        //ユーザー登録成功したらログインする
                        if(Auth::login($formData['username'], $formData['password'])){

                            Session::set_flash('sucMsg','ユーザー登録成功！ログインしました！');

                            //ログイン成功！
                            //ホーム画面へ遷移
                            Response::redirect('home');
                        }else{
                            // ログイン失敗
                            Session::set_flash('errMsg','ログインに失敗しました！時間を置いてお試し下さい！');
                        }

                    }else{
                        // ユーザー生成失敗
                        Session::set_flash('errMsg','ユーザー登録に失敗しました！時間を置いてお試し下さい！');
                    }
                }catch(\SimpleUserUpdateException $e){
                    // メールアドレスが重複
                    if ($e->getCode() == 2){
                        Session::set_flash('errMsg','ユーザー登録に失敗しました！'.$formData['email'].'は、既に登録されているメールアドレスです！');
                    }
                    // ユーザー名が重複
                    elseif ($e->getCode() == 3){
                        Session::set_flash('errMsg','ユーザー登録に失敗しました！'.$formData['username'].'は、既に登録されているユーザー名です！');
                    }
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
        $view->set('contents',View::forge('auth/signup'));
        $view->set('footer',View::forge('template/footer'));
        $view->set_global('signup', $form->build(''), false);
        $view->set_global('error', $error);
        $child_view = View::forge('template/script');
        $child_view->set('jsname','signup');
        $view->set('script',$child_view);

        return $view;
    }
}

