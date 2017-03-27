var gulp = require('gulp'),
	cssnano = require('gulp-cssnano'),
	uglify = require('gulp-uglify'),
	imagemin = require('gulp-imagemin'),
	rename = require('gulp-rename'),
	concat = require('gulp-concat'),
	notify = require('gulp-notify');


var styleSRC = [ './assets/css/sqms-product-selector.css', './assets/css/vex.css', './assets/css/vex-theme-default.css' ];
var styleDestination     = './assets/css/';


var jsSRC          	= [ './assets/js/jquery.validate.min.js', './assets/js/vex.combined.min.js', './assets/js/sqms-product-selector.js' ];
var zipSRC         	 = [ './assets/js/load-zip-form.js' ];
var reportSRC          = [ './assets/js/load-report-form.js' ];

var jsDestination  = './assets/js/';

var imgSRC            = './assets/img/*.{png,jpg,gif,svg}';
var imgDestination    = './assets/img/';

gulp.task('styles', function() {

	gulp.src( styleSRC )
			  .pipe(concat('hiq-styles.css'))
			  .pipe(gulp.dest(styleDestination))
			  .pipe(rename({suffix: '.min'}))
			  .pipe(cssnano( {zindex: false} ))
			  .pipe(gulp.dest(styleDestination))
			  .pipe( notify( { message: 'TASK: "Styles" Completed! 💯', onLast: true } ) );
});

gulp.task('scripts', function() {

	gulp.src( jsSRC )
			.pipe(concat('hiq-scripts.js'))
			.pipe(gulp.dest(jsDestination))
			.pipe(rename({suffix: '.min'}))
			.pipe(uglify())
			.pipe(gulp.dest(jsDestination))
			.pipe( notify( { message: 'TASK: "scripts" Completed! 💯', onLast: true } ) );

	gulp.src( zipSRC )
			.pipe(concat('load-zip-form.js'))
			.pipe(gulp.dest(jsDestination))
			.pipe(rename({suffix: '.min'}))
			.pipe(uglify())
			.pipe(gulp.dest(jsDestination))
			.pipe( notify( { message: 'TASK: "Zip Scripts" Completed! 💯', onLast: true } ) );

	gulp.src( reportSRC )
			.pipe(concat('load-report-form.js'))
			.pipe(gulp.dest(jsDestination))
			.pipe(rename({suffix: '.min'}))
			.pipe(uglify())
			.pipe(gulp.dest(jsDestination))
			.pipe( notify( { message: 'TASK: "Report Scripts" Completed! 💯', onLast: true } ) );
});

gulp.task( 'images', function() {
	gulp.src( imgSRC )
		.pipe(imagemin({ optimizationLevel: 1, progressive: true, interlaced: true }))
		.pipe(gulp.dest( imgDestination ))
		.pipe( notify( { message: 'TASK: "images" Completed! 💯', onLast: true } ) );
});

gulp.task('default', ['styles', 'scripts', 'images']);
