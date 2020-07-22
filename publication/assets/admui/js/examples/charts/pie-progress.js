/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function (document, window, $) {
    'use strict';

    // API方法示例
    // -------------------
    var $example = $('#examplePieApi');

    $(document).on('click', '.pie-api-start', function () {
        $example.asPieProgress('start');
    });
    $(document).on('click', '.pie-api-finish', function () {
        $example.asPieProgress('finish');
    });
    $(document).on('click', '.pie-api-go', function () {
        $example.asPieProgress('go', 200);
    });
    $(document).on('click', '.pie-api-go_percentage', function () {
        $example.asPieProgress('go', '50%');
    });
    $(document).on('click', '.pie-api-stop', function () {
        $example.asPieProgress('stop');
    });
    $(document).on('click', '.pie-api-reset', function () {
        $example.asPieProgress('reset');
    });

})(document, window, jQuery);