<!--共通head要素(view)-->
<!--役割：全ページ共通のHTML5のhead要素です-->
<meta charset="utf-8">
<title>E-CURATION</title>

<!--ファビコン-->
<?php
echo html_tag('link', array(
'rel' => 'icon',
'href' => Asset::get_file('favicon.ico', 'img'),
));
?>
<!-- 5段階評価用☆マーク表示用css-->
<?= Asset::css('fontawesome-stars.css') ?>
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">

<!--メインCSS-->
<?= Asset::css('style.css') ?>

<!--日本語フォント-->
<link href="https://fonts.googleapis.com/css?family=Noto+Serif+SC:400,700&amp;subset=japanese" rel="stylesheet">

