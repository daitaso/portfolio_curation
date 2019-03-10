<!--動画一覧画面(view)-->
<!--役割：動画一覧画面のview-->
<div class="l-site-980">

    <!--サイドバー-->
    <div class="l-site-980__sidebar">
        <h1 class="u-mbs">条件指定</h1>
        <h2>カテゴリー</h2>
        <div class="p-select_list" class="u-mbs">
            <div class="p-select_list__selectdiv">
                <label>
                    <select class="p-select_list__selectdiv__select">
                        <option value="" selected>全て</option>
                        <option value="XVIDEOS">XVIDEOS</option>
                        <option value="FC2">FC2</option>
                        <option value="TOKYOMOTION">TOKYOMOTION</option>
                    </select>
                </label>
            </div>
        </div>

        <h2>最近のタグ</h2>
        <div id="tag_list" v-if="isLoaded">
            <tag-panel
                v-for="tag in info.tag_list"
                v-bind:keyword="tag.keyword"
            ></tag-panel>
        </div>
    </div>
    <!--メインコンテンツ-->
    <div class="l-site-980__contents">

        <!--動画一覧リスト-->
        <div id="movie_list" v-if="isLoaded">
            <!-- vueコンポーネント（検索結果ヘッダー）       -->
            <search-result-header :exists="exists" :start_idx="info.start_idx" :end_idx="info.end_idx" :total_rec_num="info.total_rec_num" :keyword="info.keyword" :category="info.category" :show_keyword="info.show_keyword" :show_category="info.show_category"></search-result-header>

            <!-- vueコンポーネント（パネルリスト）       -->
            <div class="c-panel-list" v-if="exists">
                <thumb-panel
                    v-for="movie in info.movie_list"
                    v-bind:movie_id="movie.movie_id"
                    v-bind:title="movie.title"
                    v-bind:created_at="movie.created_at"
                ></thumb-panel>
            </div>

            <!-- vueコンポーネント（ページネーション）       -->
            <div class="c-pagination" v-if="exists">
                <pagenation :pages="info.pages" :cur_page="info.cur_page" :keyword="info.keyword" :category="info.category" v-on:page-change="onPageChange"></pagenation>
            </div>
        </div>
    </div>
</div>
