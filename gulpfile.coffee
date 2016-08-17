
coffee = require 'gulp-coffee'
concat = require 'gulp-concat'
sass = require 'gulp-sass'
gutil = require 'gulp-util'
gulp = require 'gulp'
cssmin = require 'gulp-cssnano'
rename = require 'gulp-rename'
fs = require 'fs'
path = require 'path'
rev = do ->
  JSON.parse(
    fs.readFileSync path.join(
      __dirname,
      './config.json'
    ), 'utf8'
  )[0].rev


gulp.task 'coffee', () ->
  gulp.src('src/coffee/*.coffee')
    .pipe(coffee({bare:true}).on('error', gutil.log))
    .pipe(concat(rev + '.js'))
    .pipe(gulp.dest('assets/javascripts'))

gulp.task 'sass', () ->
  gulp.src('src/sass/main.scss')
    .pipe(sass({outputStyle: 'nested'}).on('error', gutil.log))
    .pipe(rename(rev + '.css'))
    .pipe(gulp.dest('assets/stylesheets'))

gulp.task 'watch', () ->
  gulp.watch 'src/coffee/*.coffee', ['coffee']
  gulp.watch 'src/sass/*.scss', ['sass']


gulp.task 'default', ['watch'], ()->
