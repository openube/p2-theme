module.exports = function(grunt) {

	// Project configuration.
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		concat: {
			options: {
				// define a string to put between each file in the concatenated output
				separator: ';'
			},
			theme: {
				options: {
					banner: '/*!\nTheme Name: <%= pkg.name %>\nTheme URI: <%= pkg.homepage %>\nDescription: <%= pkg.description %>\nVersion: <%= pkg.version %>\nAuthor: <%= pkg.author %>\nAuthor URI: <%= pkg.authoruri %>\nLicense: <%= pkg.license %>\nLicense URI: <%= pkg.licenseuri %>\n*/'
				},
				src: 'css/style.min.css',
				dest: 'style.css'
			},
			development: {
				options: {
					banner: '/*!\nTheme Name: <%= pkg.name %>\nTheme URI: <%= pkg.homepage %>\nDescription: <%= pkg.description %>\nVersion: <%= pkg.version %>\nAuthor: <%= pkg.author %>\nAuthor URI: <%= pkg.authoruri %>\nLicense: <%= pkg.license %>\nLicense URI: <%= pkg.licenseuri %>\n*/'
				},
				src: 'css/style.dev.css',
				dest: 'style.css'
			},
			js: {
				// the files to concatenate
				src: [
					//'bootstrap/js/affix.js', 
					//'bootstrap/js/alert.js', 
					//'bootstrap/js/button.js',
					//'bootstrap/js/carousel.js', 
					//'bootstrap/js/collapse.js', 
					//'bootstrap/js/dropdown.js', 
					//'bootstrap/js/modal.js', 
					//'bootstrap/js/popover.js', 
					//'bootstrap/js/scrollspy.js', 
					//'bootstrap/js/tab.js', 
					//'bootstrap/js/tooltip.js', 
					'bxslider-4/jquery.bxslider.js',
					'jquery-backstretch/jquery.backstretch.js',
					'js/theme.scripts.js'
				],
				// the location of the resulting JS file
				dest: 'js/p2.js'
			}
		},
		uglify: {
			theme: {
				options: {
					// the banner is inserted at the top of the output
					banner: '/*!\n * <%= pkg.name %>\n * jQuery Plugins and theme scripts\n * generated <%= grunt.template.today("dd-mm-yyyy") %>\n */\n',
					mangle: false
				},
				files: {
					'js/p2.min.js': ['<%= concat.js.dest %>']
				}
			},
			development: {
				options: {
					// the banner is inserted at the top of the output
					banner: '/*!\n *<%= pkg.name %>\njQuery Plugins and theme scripts\n * generated <%= grunt.template.today("dd-mm-yyyy") %>\n */\n',
					mangle: false,
					compress: false,
					beautify: true
				},
				files: {
					'js/p2.min.js': ['<%= concat.js.dest %>']
				}
			}
		},
		less: {
			tinymce: {
				options: {
					paths: ["css/less"],
					cleancss:true
				},
				files: {
					"css/editor-style.css": "css/less/editor-style.less"
				}
			},
			theme: {
				options: {
					paths: ["css/less"],
					cleancss:true
				},
				files: {
					"css/style.min.css": "css/less/style.less"
				}
			},
			admin: {
				options: {
					paths: ["css/less"],
					cleancss:true
				},
				files: {
					"css/admin.css": "css/less/admin.less"
				}
			},
			development: {
				options: {
					paths: ["css/less"],
					cleancss:false
				},
				files: {
					"css/style.dev.css": "css/less/style.less"
				}
			}
		},
	});

	// Load the plugin that provides the "uglify" task.
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-less');
	grunt.loadNpmTasks('grunt-banner');

	// Default task(s).
	grunt.registerTask('default', ['less:theme', 'less:tinymce', 'concat:theme', 'concat:js', 'uglify:theme']);
	grunt.registerTask('dev', ['less:development', 'concat:development', 'concat:js', 'uglify:development']);

};