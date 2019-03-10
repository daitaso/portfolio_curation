<!--サインアップ画面(view)-->
<!--役割：サインアップ画面のview-->
<div class="l-site-500">
    <h1>ユーザー登録</h1>

    <!-- バリデーションエラーのメッセージ表示用領域  -->
    <?php
        if(!empty($error)):
    ?>
        <ul class="p-area-error-msg">
    <?php
        foreach ($error as $key => $val):
    ?>
        <li><?=$val?></li>
    <?php
        endforeach;
    ?>
            </ul>
    <?php
        endif;
    ?>

    <!-- フォーム要素   -->
    <?=$signup?>

</div>