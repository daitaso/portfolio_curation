import $ from "jquery";
//
// 共通ＪＳ
//
// 役割：全てのページで読み込むＪＳ
//
$(function() {

  //画面上部に表示するメッセージ用
  var $toggleMsg = $('.js-toggle-msg');
  if($toggleMsg.length){
    $toggleMsg.slideDown();
    setTimeout(function(){ $toggleMsg.slideUp(); },3000);
  }

  // フッターを最下部に固定
  var $ftr = $('footer');
  if (window.innerHeight > $ftr.offset().top + $ftr.outerHeight()) {
    $ftr.attr({'style': 'position:fixed; top:' + (window.innerHeight - $ftr.outerHeight()) + 'px;'});
  }

})