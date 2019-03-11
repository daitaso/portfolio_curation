import Vue from 'vue'
import axios from 'axios';
import $ from 'jquery';
import moment from 'moment';

//
// 動画一覧画面ＪＳ
//
// 役割：動画一覧画面のＪＳ
//

//イベントハブ
let eventHub = new Vue()

//
// 検索結果ヘッダー（vueコンポーネント）
//
Vue.component('search-result-header', {
  props:['exists','start_idx','end_idx','total_rec_num','keyword','category','show_keyword','show_category'],
  template: `
                <div>
                    <h1 v-if="exists">検索結果　{{ start_idx + 1 }} - {{ end_idx }}件 / {{total_rec_num}}件中 <span v-if="show_keyword">　タグ　{{keyword}}</span><span v-else-if="show_category">　カテゴリー　{{category}}</span></h1>         
                    <h1 v-else>動画が登録されていません</h1>   
                </div>
            `
})

//
// サムネイルパネル（vueコンポーネント）
//
Vue.component('thumb-panel', {
  props:['movie_id','title','created_at'],
  computed: {
    fromNow: function (){
      var date = this.created_at;
      moment.locale( 'ja' );
      return moment(date, 'YYYY/MM/DD HH:mm:S').fromNow();
    }
  },
  template: `
                    <a :href="'moviedetail.php?movie_id=' + movie_id " class="c-panel-list__panel">
                        <img class ="c-panel-list__panel__img" :src="'./assets/img/thumb/' + movie_id + '.jpg'" :alt="title">
                        <p class="c-panel-list__panel__title">{{title}}</p>
                        <span class="c-panel-list__panel__fromnow">{{this.fromNow}}</span>
                    </a>
                  `
})

//
// ページネーション（vueコンポーネント）
//
Vue.component('pagenation', {
  props:['pages','keyword','cur_page','category'],
  computed: {
    createPushClass : function () {
      let cur_page = this.cur_page
      self = this;
      return function (page) {
        if(Number(page) === Number(cur_page)){
          return 'c-pagination__list__list-item__button--select'
        }
        return '';
      };
    }
  },
  template: `
                <ul class="c-pagination__list">
                    <li class="c-pagination__list__list-item" v-for="page in pages">
                        <button class="c-pagination__list__list-item__button" :class="createPushClass(page)" v-on:click="$emit('page-change',page,keyword,category)">{{page}}</button>
                    </li>
                </ul>
             `
})

//
// タグパネル（vueコンポーネント）
//
Vue.component('tag-panel', {
  props:['keyword'],
  methods: {
    onTagChange: function (keyword) {

      //カテゴリーを「全て」にセット
      $('select').val('');

      if(keyword === '未指定'){
        keyword = null
      }
      eventHub.$emit('tag-change',1,keyword,null)
    }
  },
  template:
      `
        <button class="p-tag-button " v-on:click="onTagChange(keyword,$event)">{{keyword}}</button>
      `
})
//
// ルートvueインスタンス（動画一覧）
//
new Vue({
  el: '#movie_list',
  data () {
    return {
      info: null,
      isLoaded: false,  //データが読み込まれたか？
      exists:   false   //動画が存在するか？
    }
  },
  methods: {
    onPageChange: function (page,keyword,category) {
      let url = 'api/movies/list.json?page=' + page
      if(keyword !== null){
        url += '&keyword=' + keyword
      }
      if(category !== null){
        url += '&category=' + category
      }
      this.isLoaded = false
      this.exists   = false
      axios
          .get(url)
          .then(response => {
            this.info = response.data
            if(this.info.total_rec_num !== 0){
              this.exists = true //動画が１件以上存在した
            }
            var self = this;
            setTimeout(function () {
              self.isLoaded  = true
            },500)
          })
    }
  },
  created(){
    eventHub.$on('tag-change', this.onPageChange)
    eventHub.$on('category-change',this.onPageChange)
  },
  beforeDestroy() {
    eventHub.$off('tag-change', this.onPageChange)
    eventHub.$off('category-change',this.onPageChange)

  },
  mounted () {
    this.onPageChange(1,null,null)
  }

})

//
// ルートvueインスタンス（タグ一覧）
//
new Vue({
  el: '#tag_list',
  data () {
    return {
      info: null,
      isLoaded: false,  //データが読み込まれたか？
    }
  },
  mounted () {
    axios
        .get('api/tags/list.json')
        .then(response => {
          this.info = response.data
          this.isLoaded  = true
        })
  },
  created(){
  }
})


$(function() {
  //
  // カテゴリーが変更されたら動画リストに通知
  //
  $('select').change(function () {
    var val = $(this).val() || null;
    eventHub.$emit('category-change', 1, null, val)
  });

})