/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function (document, window, $) {
    'use strict';

    // 进度条动画
    // --------------------------
    $(document).on('click', '#exampleButtonStart', function () {
        $('[data-plugin="progress"]').asProgress('start');
    });
    $(document).on('click', '#exampleButtonFinish', function () {
        $('[data-plugin="progress"]').asProgress('finish');
    });
    $(document).on('click', '#exampleButtonGoto', function () {
        $('[data-plugin="progress"]').asProgress('go', 50);
    });
    $(document).on('click', '#exampleButtonGotoPercentage', function () {
        $('[data-plugin="progress"]').asProgress('go', '50%');
    });
    $(document).on('click', '#exampleButtonStop', function () {
        $('[data-plugin="progress"]').asProgress('stop');
    });
    $(document).on('click', '#exampleButtonReset', function () {
        $('[data-plugin="progress"]').asProgress('reset');
    });
    $(document).on('click', '#exampleButtonRandom', function (e) {
        e.preventDefault();

        $('[data-plugin="progress"]').each(function () {
            var number = Math.round(Math.random(1) * 100) + '%';
            $(this).asProgress('go', number);
        });
    });

})(document, window, jQuery);