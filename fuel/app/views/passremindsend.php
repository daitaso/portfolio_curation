<!--パスワードリマインダー（送信）画面(view)-->
<!--役割：パスワードリマインダー（送信）画面のview-->
<div class="l-site-500">

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
    <p>ご指定のメールアドレス宛にパスワード再発行用のＵＲＬと認証キーをお送り致します。</p>

    <!-- フォーム要素    -->
    <?=$passremindsend?>
</div>