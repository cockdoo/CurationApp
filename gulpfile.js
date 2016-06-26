var gulp = require("gulp");
var browserSync = require('browser-sync');
var connect = require("gulp-connect-php");

gulp.task('connect-sync', function() {
  connect.server({
    port:3000,
    base:'api/'
  }, function (){
    browserSync({
      proxy: 'localhost:3000'
    });
  });
});

gulp.task("default",['connect-sync']);

