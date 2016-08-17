
coffee = require 'gulp-coffee'
concat = require 'gulp-concat'
sass = require 'gulp-sass'
gutil = require 'gulp-util'
gulp = require 'gulp'
cssmin = require 'gulp-cssnano'
rename = require 'gulp-rename'
fs = require 'fs'
path = require 'path'
del = require 'del'
rev = do ->
  JSON.parse(
    fs.readFileSync path.join(
      __dirname,
      './config.json'
    ), 'utf8'
  )[0].rev


gulp.task 'clean', () ->
  del([
    './assets/javascripts/*.js'
    './assets/stylesheets/*.css'
  ])

gulp.task 'coffee', ['clean'], () ->
  gulp.src('src/coffee/*.coffee')
    .pipe(coffee({bare:true}).on('error', gutil.log))
    .pipe(concat(rev + '.js'))
    .pipe(gulp.dest('assets/javascripts'))

gulp.task 'sass', ['clean'], () ->
  gulp.src('src/sass/main.scss')
    .pipe(sass({outputStyle: 'nested'}).on('error', gutil.log))
    .pipe(rename(rev + '.css'))
    .pipe(gulp.dest('assets/stylesheets'))

gulp.task 'watch', ['clean', 'coffee', 'sass'], () ->
  gulp.watch 'src/coffee/*.coffee', ['coffee']
  gulp.watch 'src/sass/*.scss', ['sass']

guid = ->
  s4 = ->
    Math.floor((1 + Math.random()) * 0x10000).toString(16).substring 1
  "#{s4()}#{s4()}#{s4()}#{s4()}#{s4()}#{s4()}#{s4()}#{s4()}"

gulp.task 'rev', () ->
  fs.readFile path.join(__dirname, './config.json'), 'utf8', (err, data) ->
    if err then throw err
    next = guid()
    res = data.replace rev, next
    fs.writeFile path.join(__dirname, './config.json'), res, (err) ->
      if err then throw err
      console.log 'Success! New version is ' + next

gulp.task 'default', ['watch'], ()->
