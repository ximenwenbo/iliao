/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function (window, document) {
    'use strict';

    var btn;

    if (typeof $ === 'undefined') {
        btn = document.getElementById('closeTab');
        if(btn != undefined){
            btn.innerText = '退回上一页';
            btn.onclick = function () {
                history.go(-1);
            };
        }

    } else if (typeof $.site.contentTabs !== 'undefined') {
        $(document).on('click', '#closeTab', function () {
            $.site.contentTabs.closeTab();
        });
    }

})(window, document);