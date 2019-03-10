import gulp from 'gulp';
import webpackConfig from './webpack.config.js';
import webpack from 'webpack-stream';
import notify from 'gulp-notify';
import plumber from 'gulp-plumber';
import eslint from 'gulp-eslint';
import sass from 'gulp-sass';

// gulpタスクの作成
gulp.task('build', function(){
  gulp.src('src/js/*.js')
    .pipe(plumber({
      errorHandler: notify.onError("Error: <%= error.message %>")
    }))
    .pipe(webpack(webpackConfig))
    .pipe(gulp.dest('../public/assets/js/'));
});

gulp.task('eslint', function() {
  return gulp.src(['src/**/*.js']) // lint のチェック先を指定
    .pipe(plumber({
      // エラーをハンドル
      errorHandler: function(error) {
        const taskName = 'eslint';
        const title = '[task]' + taskName + ' ' + error.plugin;
        const errorMsg = 'error: ' + error.message;
        // ターミナルにエラーを出力
        console.error(title + '\n' + errorMsg);
        // エラーを通知
        notify.onError({
          title: title,
          message: errorMsg,
          time: 3000
        });
      }
    }))
    .pipe(eslint({ useEslintrc: true })) // .eslintrc を参照
    .pipe(eslint.format())
    .pipe(eslint.failOnError())
    .pipe(plumber.stop());
});

// sassをコンパイル
gulp.task('sass', function(){
  gulp.src('src/css/style.scss')
      .pipe(sass())
      .pipe(gulp.dest('../public/assets/css'));
});

// Gulpを使ったファイルの監視
gulp.task('default', ['eslint', 'build','sass'], function(){
  gulp.watch('./src/**/*.js', ['build']);
  gulp.watch("./src/**/*.js", ['eslint']);
  gulp.watch("./src/**/*.scss", ['sass']);
});
