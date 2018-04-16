// RUN
// starts nginx server to allow multithread php in develpment
module.exports = function(grunt) {

    'use strict';

    grunt.config('run', {
        options: {
            wait: false
            },
        fpm: {
            cmd: 'php-fpm7.0',
            args: ['-p', '<%= goteo.dir %>', '-y', '<%= goteo.dir %>/var/php/php-fpm.conf', '-d', 'upload_tmp_dir=<%= goteo.dir %>/var/php', '-d', 'sys_temp_dir=<%= goteo.dir %>/var/php', '-d', 'session.save_path=<%= goteo.dir %>/var/php/sessions']
        },
        nginx: {
            cmd: 'nginx',
            args: ['-p', '<%= goteo.dir %>', '-c', '<%= goteo.dir %>/var/php/nginx.conf']
        },
        fpmlog: {
            cmd: 'tail',
            args: ['-f', '<%= goteo.dir %>/php.log']
        }
    });
    grunt.loadNpmTasks('grunt-run');
};
