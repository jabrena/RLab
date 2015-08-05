'use strict';

module.exports = function (grunt) {

  require('time-grunt')(grunt);
  require('load-grunt-tasks')(grunt);

  grunt.initConfig({

    php: {
        dist: {
            options: {
                port: 10000,
                base: './www/',
                open: true,
                keepalive: true
            }
        }
    }

  });

    grunt.registerTask('serverPHP', 'start the PHP server and preview your app', function () {
        grunt.task.run(['php']);
    });
};