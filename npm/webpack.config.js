const path = require('path');

module.exports = {
  entry: {
    movieList: [path.join(__dirname, 'src/js/movieList.js'),path.join(__dirname, 'src/js/common/common.js')],
    movieDetail: [path.join(__dirname, 'src/js/movieDetail.js'),path.join(__dirname, 'src/js/common/common.js')],
    favoriteList: [path.join(__dirname, 'src/js/favoriteList.js'),path.join(__dirname, 'src/js/common/common.js')],
    login: path.join(__dirname, 'src/js/common/common.js'),
    signup: path.join(__dirname, 'src/js/common/common.js'),
    passRemindSend: path.join(__dirname, 'src/js/common/common.js'),
    passRemindReceive: path.join(__dirname, 'src/js/common/common.js'),

  },
  output: {
    path: path.join(__dirname, '../public/assets/js'),
    filename: '[name].bundle.js'
  },
  module: {
    loaders: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        loader: 'babel-loader',
        query:{
          presets: ['es2015']
        }
      }
    ]
  },
  resolve: {
    modules: [path.join(__dirname, 'src'), 'node_modules'],
    extensions: ['.js'],
    alias: {
      vue: 'vue/dist/vue.esm.js' // npm install したvueはtemplete機能のないランタイム限定ビルドなので、こっちを使うようエイリアスをはる
    }
  }
};