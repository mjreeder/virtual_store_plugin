'use strict';

var gulp = require('gulp');
var sass = require('gulp-sass');

gulp.task('styles', function() {
    gulp.src('assets/sass/**/*.scss')
        .pipe(sass().on('error', sass.logError))
        .pipe(gulp.dest('./assets/css/'))
});

//Watch task
gulp.task('watch', function() {
    gulp.watch('assets/sass/**/*.scss', ['styles']);
});

gulp.task('default', ['styles', 'watch']);
