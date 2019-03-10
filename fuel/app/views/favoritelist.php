<!--お気に入り一覧画面(view)-->
<!--役割：お気に入り一覧画面のview-->
<div class="l-site-980">

    <h1>お気に入り一覧</h1>

    <!--　動画一覧リスト（お気に入り）    -->
    <div id="favorite_list" v-if="isLoaded">

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