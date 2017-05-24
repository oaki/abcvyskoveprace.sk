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
                    src  : 'web/assets/frontend/css/main-reduced.css',
                    dest : 'web/assets/frontend/css/main-reduced.min.css'
                }]
            }
        },

        uglify : {
            js_frontend : {
                files   : {
                    'web/assets/frontend/js/main.dist.js' : [
                        'web/assets/frontend/js/jquery.blockUI.min',
                        'web/assets/frontend/js/js.cookie.min.js',
                        'web/assets/frontend/js/bootstrap.min.js',
                        'web/assets/frontend/js/jquery.appear.js',
                        'web/assets/frontend/js/jquery-countTo.js',
                        'web/assets/frontend/js/jquery.parallax-1.1.3.js',
                        'web/assets/frontend/js/owl.carousel.min.js',
                        'web/assets/frontend/js/jquery.mixitup.min.js',
                        'web/assets/frontend/js/jquery.fancybox.js',
                        'web/assets/frontend/js/jquery.cubeportfolio.min.js',
                        'web/assets/frontend/js/gmap3.min.js',
                        'web/assets/frontend/js/custom.js',
                        'web/assets/frontend/js/ajax.js',
                        'web/assets/frontend/js/js_composer_front.min.js'
                    ]
                },
                options : {
                    // preserveComments: 'some',
                    // mangle   : false,
                    compress : {
                        dead_code : true
                    },
                    // beautify : true,

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
        },

        uncss : {
            dist : {
                files : [{
                    options:{
                        ignore: ['.open','.dropdown-menu']
                    },
                    nonull : true,
                    src    : [
                        'http://abcvyskovepracesk:8888/',
                        'http://abcvyskovepracesk:8888/referencie.html',
                        'http://abcvyskovepracesk:8888/kontakt.html',
                        'http://abcvyskovepracesk:8888/vyskove-prace-horolezeckou-technikou.html',
                        'http://abcvyskovepracesk:8888/realizacia-a-rekonstrukcia-plochych-striech.html',
                        'http://abcvyskovepracesk:8888/zateplenie-budov.html',
                    ],
                    dest   : './web/assets/frontend/css/main-reduced.css'
                }]
            }
        }

    });



    //npm tasks
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-uncss');

    //tasks
    grunt.registerTask('default', 'Default Task Alias', ['less']);

}