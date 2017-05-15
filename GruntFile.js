module.exports = function(grunt) {
    grunt.initConfig({
        pkg : grunt.file.readJSON('package.json'),

        less : {
            frontend : {
                files   : {
                    'web/assets/frontend/css/main.css' : 'web/assets/frontend/css/_main.less'
                },
                options : {
                    spawn       : false,
                    yuicompress : true,
                    sourceMap   : true
                }

            }
        },

        cssmin : {
            options : {
                rebase     : true,
                relativeTo : './web/assets/frontend/css/'
            },
            min     : {
                files : [{
                    src  : 'web/assets/frontend/css/main.css',
                    dest : 'web/assets/frontend/css/main.min.css'
                }]
            }
        },

        uglify : {
            js_frontend : {
                files   : {
                    'web/assets/frontend/js/main.dist.js' : [
                        'web/assets/frontend/js/jquery/1.8.3/jquery.js',
                        'web/assets/frontend/bootstrap/js/bootstrap.min.js',
                        'web/assets/frontend/js/idangerous.swiper-2.1.min.js',
                        'web/assets/frontend/js/pace.min.js',
                        'web/assets/frontend/js/jquery.cycle2.min.js',
                        'web/assets/frontend/js/jquery.easing.1.3.js',
                        'web/assets/frontend/js/jquery.parallax-1.1.js',
                        'web/assets/frontend/js/helper-plugins/jquery.mousewheel.min.js',
                        'web/assets/frontend/js/jquery.mCustomScrollbar.js',
                        'web/assets/frontend/js/ion-checkRadio/js/ion.checkRadio.min.js',
                        'web/assets/frontend/js/grids.js',
                        'web/assets/frontend/js/owl.carousel.min.js',
                        'web/assets/frontend/js/chosen.jquery.js',
                        'web/assets/frontend/js/bootstrap.touchspin.js',
                        'web/assets/frontend/js/smoothproducts.min.js',
                        'web/assets/frontend/js/nette.ajax.js',
                        'web/assets/frontend/js/netteForms.min.js',
                        'web/assets/frontend/js/prettyPhoto/js/jquery.prettyPhoto.js',
                        'web/assets/frontend/js/gmaps.min.js',
                        'web/assets/frontend/js/home.js',
                        'web/assets/frontend/js/script.js'
                    ]
                },
                options : {
                    // preserveComments: 'some',
                    mangle   : false,
                    compress : false,
                    beautify : true,

                    sourceMap     : true,
                    sourceMapName : 'web/assets/frontend/js/sourcemap.map'
                },
            }
        },

        watch : {
            frontend_css : {
                files : ['web/assets/frontend/css/*.less', 'web/assets/frontend/css/*.css', '!web/assets/frontend/css/main.css'],
                tasks : ['less'],
            },
            frontend_js  : {
                files : ['web/assets/frontend/js/*.js', '!web/assets/frontend/js/main.dist.js'],
                tasks : ['uglify']
            },
        }

    });

    //npm tasks
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-watch');

    //tasks
    grunt.registerTask('default', 'Default Task Alias', ['less']);

}