import Vue from 'vue'
import axios from 'axios';
import $ from 'jquery';
import moment from 'moment';

//
// 動画詳細画面ＪＳ
//
// 役割：動画詳細画面のＪＳ
//

//イベントハブ
let eventHub = new Vue()

//
// コメント、５段階評価の入力（vueコンポーネント）
//
Vue.component('review-input', {
  props:['movie_id'],
  data:  ()=> {
    return {
      star_count: 1,
      input_text: ''
    }
  },
  methods:{
    //
    // 星がクリックされると、評価の数を変更される
    //
    onStarChange: function (e) {

      let base_attr = e.currentTarget.getAttribute('class')
      this.star_count = 0

      //押された☆の左側をactiveにする
      base_attr = base_attr.replace(' p-icn-star--active','')
      let el = e.currentTarget.previousElementSibling
      while (el) {
        el.setAttribute('class',base_attr + ' p-icn-star--active')
        el = el.previousElementSibling;
        this.star_count++
      }
      //押された☆をactiveにする
      e.currentTarget.setAttribute('class',base_attr + ' p-icn-star--active')
      this.star_count++

      //押された☆の右側を非activeにする
      el = e.currentTarget.nextElementSibling
      while (el) {
        el.setAttribute('class',base_attr + ' p-icn-star')
        el = el.nextElementSibling
      }
    },
    //テキストエリアに文字が入力された
    onKeyUp: function(e){
      this.input_text = e.currentTarget.value

      //バリデーション（１文字～１４０文字以外は送信ボタンが押せない）
      var $button = $('.js-review-button') || null;
      if($button !== null){
        if(this.input_text.length > 0 && this.input_text.length <= 140) {
          $button.removeClass('p-review-text-input__button--cant-push').addClass('p-review-text-input__button--can-push')
        }else{
          $button.removeClass('p-review-text-input__button--can-push').addClass('p-review-text-input__button--cant-push')
        }
      }
    },
    //送信ボタンクリック
    onSubmit: function (e) {

      axios.post('api/comments/list.json', {
        movie_id: this.movie_id,
        comment:  this.input_text,
        review:   this.star_count
      }).then(response => {

        //送信したコメントを即座に表示されるよう、review-panel-listに通知する
        eventHub.$emit('comment-update',this.movie_id)

        //テキストアリアとボタンを元に戻す
        $('.js-review-text').val('')
        this.input_text = ''
        $('.js-review-button').removeClass('p-review-text-input__button--can-push').addClass('p-review-text-input__button--cant-push')

      }).catch(error => {
        console.log(error);
      });
    }
  },
  template:
      `
                <div>
                    <div class="review-star-input">
                        <i class="fas fa-star p-icn-star--active" @click="onStarChange"></i>
                        <i class="fas fa-star p-icn-star " @click="onStarChange"></i>
                        <i class="fas fa-star p-icn-star " @click="onStarChange"></i>
                        <i class="fas fa-star p-icn-star " @click="onStarChange"></i>
                        <i class="fas fa-star p-icn-star " @click="onStarChange"></i>
                    </div>
                    <div class="p-review-text-input">
                        <textarea class="p-review-text-input__textarea js-review-text" @keyup="onKeyUp" name="comment" id="" cols="10" rows="10" placeholder="どうでしたか？"></textarea>
                        <button class="p-review-text-input__button p-review-text-input__button--cant-push js-review-button" @click="onSubmit">送信</button>
                    </div>
                </div>
             `
})

//
// コメント、５段階評価の表示リスト（vueコンポーネント）
//
//評価パネルリスト
Vue.component('review-panel-list', {
  props:['movie_id'],
  data () {
    return {
      info: null,
      flg:false
    }
  },
  methods:{
    onCommentUpdate: function (movie_id) {
      axios
          .get('api/comments/list.json?movie_id=' + movie_id)
          .then(response => {
            this.info = response.data
            this.flg  = true
          })
    }
  },
  created(){
    eventHub.$on('comment-update', this.onCommentUpdate)
  },
  beforeDestroy() {
    eventHub.$off('comment-update', this.onCommentUpdate)
  },
  mounted () {
    this.onCommentUpdate(this.movie_id)
  },
  template: `
                    <div v-if="flg">
                        <review-panel v-for="comment in info.comment_list" :comment="comment"></review-panel>
                    </div>
                  `
})

//
// コメント、５段階評価の表示パネル（review-panel-listの子コンポーネント）
//
Vue.component('review-panel', {
  props:['comment'],
  computed: {
    Review: function (){
      return Number(this.comment.review)
    },
    zeroReview: function () {
      return 5 - this.comment.review
    },
    fromNow: function (){
      var date = this.comment.created_at
      moment.locale( 'ja' )
      return moment(date, 'YYYY/MM/DD HH:mm:S').fromNow()
    }
  },
  template: `
                    <div class="p-review-area">
                        <ul class="p-review-area__ul">
                            <li class="p-review-area__ul__li" v-for="n in this.Review" ><i class="fas fa-star p-icn-star--active"></i></li>
                            <li class="p-review-area__ul__li" v-for="n in this.zeroReview" ><i class="fas fa-star p-icn-star "></i></li>
                        </ul>
                        <p>{{comment.user_name}}<span class="u-from-now">{{this.fromNow}}</span></p>
                        <p>{{comment.comment}}</p>
                    </div>
                  `
})

//
// ルートvueインスタンス（コメント５段階評価、入力側）
//
new Vue({
  el: '#review_input',
  data () {
    return {
      info: null
    }
  }
})

//
// ルートvueインスタンス（コメント５段階評価、表示側）
//
new Vue({
  el: '#review_list'
})

$(function() {

  // お気に入り登録・削除
  var $like,
      likeMovieId;
  $like = $('.js-click-like') || null;
  likeMovieId = $like.data('movie_id') || null;
  if (likeMovieId !== undefined && likeMovieId !== null) {
    $like.on('click', function () {
      var $this = $(this);

      $.ajax({
        type: "POST",
        url: "api/favorites.php",
        data: {movieId: likeMovieId}
      }).done(function (data) {
        console.log('Ajax Success');
        // クラス属性をtoggleでつけ外しする
        $this.toggleClass('p-icn-like--active');

      }).fail(function (msg) {
        console.log('Ajax Error');
      });
    });
  }

})
