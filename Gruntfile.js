module.exports = function(grunt) {

  // Project configuration.
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    concat: {
      options: {
        // define a string to put between each file in the concatenated output
        separator: ';'
      },
      dist: {
        // the files to concatenate
        src: ['bootstrap/js/affix.js', 'bootstrap/js/alert.js', 'bootstrap/js/button.js', 'bootstrap/js/carousel.js', 'bootstrap/js/collapse.js', 'bootstrap/js/dropdown.js', 'bootstrap/js/modal.js', 'bootstrap/js/popover.js', 'bootstrap/js/scrollspy.js', 'bootstrap/js/tab.js', 'bootstrap/js/tooltip.js', 'bootstrap/js/transition.js'],
        // the location of the resulting JS file
        dest: 'js/bootstrap.plugins.js'
      }
    },
    uglify: {
      jqplugins: {
        options: {
          // the banner is inserted at the top of the output
          banner: '/*!\n *<%= pkg.name %>\nTwitter Bootstrap jQuery Plugins\n * generated <%= grunt.template.today("dd-mm-yyyy") %>\n */\n',
          mangle: false
        },
        files: {
          'js/bootstrap.plugins.min.js': ['<%= concat.dist.dest %>']
        }
      },
      themejs: {
        options: {
          // the banner is inserted at the top of the output
          banner: '/*!\n * p2 wordpress theme javascript\n * @author <%= pkg.author %>\n * @version <%= pkg.version %>\n * generated: <%= grunt.template.today("dd-mm-yyyy") %>\n */\n',
          mangle: true
        },
        files: {
          'js/scripts.min.js': ['js/scripts.js']
        }
      }
    },
    less: {
      production: {
        options: {
          paths: ["css/less"],
          cleancss:true
        },
        files: {
          "css/style.min.css": "css/less/style.less"
        }
      },
      tinmymce: {
        options: {
          paths: ["css/less"],
          cleancss:true
        },
        files: {
          "css/editor-style.css": "css/less/editor-style.less"
        }
      },
      development: {
        options: {
          paths: ["css/less"],
          cleancss:false
        },
        files: {
          "css/style.css": "css/less/style.less"
        }
      }
    }
  });

  // Load the plugin that provides the "uglify" task.
  grunt.loadNpmTasks('grunt-contrib-concat');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-less');

  // Default task(s).
  grunt.registerTask('default', ['less']);

};