/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  'use strict';

  $.components.register('iconpicker', {
    mode: 'default',
    defaults: {
      fullClassFormatter: function(value) {
        return 'icon ' + value;
      },
      templates: {
        buttons:
          '<button class="iconpicker-btn iconpicker-btn-cancel btn btn-default btn-sm">取消</button>' +
          ' <button class="iconpicker-btn iconpicker-btn-accept btn btn-primary btn-sm">确认</button>',
        search:
          '<input type="search" class="form-control form-control-sm iconpicker-search" placeholder="查找图标">'
      },
      icons: [
        {
          title: 'icon fa-adjust',
          searchTerms: []
        },
        {
          title: 'icon fa-anchor',
          searchTerms: []
        },
        {
          title: 'icon fa-archive',
          searchTerms: []
        },
        {
          title: 'icon fa-area-chart',
          searchTerms: []
        },
        {
          title: 'icon fa-arrows',
          searchTerms: []
        },
        {
          title: 'icon fa-arrows-h',
          searchTerms: []
        },
        {
          title: 'icon fa-arrows-v',
          searchTerms: []
        },
        {
          title: 'icon fa-asterisk',
          searchTerms: []
        },
        {
          title: 'icon fa-at',
          searchTerms: []
        },
        {
          title: 'icon fa-automobile',
          searchTerms: []
        },
        {
          title: 'icon fa-ban',
          searchTerms: []
        },
        {
          title: 'icon fa-bank',
          searchTerms: []
        },
        {
          title: 'icon fa-bar-chart',
          searchTerms: []
        },
        {
          title: 'icon fa-bar-chart-o',
          searchTerms: []
        },
        {
          title: 'icon fa-barcode',
          searchTerms: []
        },
        {
          title: 'icon fa-bars',
          searchTerms: []
        },
        {
          title: 'icon fa-bed',
          searchTerms: []
        },
        {
          title: 'icon fa-beer',
          searchTerms: []
        },
        {
          title: 'icon fa-bell',
          searchTerms: []
        },
        {
          title: 'icon fa-bell-o',
          searchTerms: []
        },
        {
          title: 'icon fa-bell-slash',
          searchTerms: []
        },
        {
          title: 'icon fa-bell-slash-o',
          searchTerms: []
        },
        {
          title: 'icon fa-bicycle',
          searchTerms: []
        },
        {
          title: 'icon fa-binoculars',
          searchTerms: []
        },
        {
          title: 'icon fa-birthday-cake',
          searchTerms: []
        },
        {
          title: 'icon fa-bolt',
          searchTerms: []
        },
        {
          title: 'icon fa-bomb',
          searchTerms: []
        },
        {
          title: 'icon fa-book',
          searchTerms: []
        },
        {
          title: 'icon fa-bookmark',
          searchTerms: []
        },
        {
          title: 'icon fa-bookmark-o',
          searchTerms: []
        },
        {
          title: 'icon fa-briefcase',
          searchTerms: []
        },
        {
          title: 'icon fa-bug',
          searchTerms: []
        },
        {
          title: 'icon fa-building',
          searchTerms: []
        },
        {
          title: 'icon fa-building-o',
          searchTerms: []
        },
        {
          title: 'icon fa-bullhorn',
          searchTerms: []
        },
        {
          title: 'icon fa-bullseye',
          searchTerms: []
        },
        {
          title: 'icon fa-bus',
          searchTerms: []
        },
        {
          title: 'icon fa-cab',
          searchTerms: []
        },
        {
          title: 'icon fa-calculator',
          searchTerms: []
        },
        {
          title: 'icon fa-calendar',
          searchTerms: []
        },
        {
          title: 'icon fa-calendar-o',
          searchTerms: []
        },
        {
          title: 'icon fa-camera',
          searchTerms: []
        },
        {
          title: 'icon fa-camera-retro',
          searchTerms: []
        },
        {
          title: 'icon fa-car',
          searchTerms: []
        },
        {
          title: 'icon fa-caret-square-o-down',
          searchTerms: []
        },
        {
          title: 'icon fa-caret-square-o-left',
          searchTerms: []
        },
        {
          title: 'icon fa-caret-square-o-right',
          searchTerms: []
        },
        {
          title: 'icon fa-caret-square-o-up',
          searchTerms: []
        },
        {
          title: 'icon fa-cart-arrow-down',
          searchTerms: []
        },
        {
          title: 'icon fa-cart-plus',
          searchTerms: []
        },
        {
          title: 'icon fa-cc',
          searchTerms: []
        },
        {
          title: 'icon fa-certificate',
          searchTerms: []
        },
        {
          title: 'icon fa-check',
          searchTerms: []
        },
        {
          title: 'icon fa-check-circle',
          searchTerms: []
        },
        {
          title: 'icon fa-check-circle-o',
          searchTerms: []
        },
        {
          title: 'icon fa-check-square',
          searchTerms: []
        },
        {
          title: 'icon fa-check-square-o',
          searchTerms: []
        },
        {
          title: 'icon fa-child',
          searchTerms: []
        },
        {
          title: 'icon fa-circle',
          searchTerms: []
        },
        {
          title: 'icon fa-circle-o',
          searchTerms: []
        },
        {
          title: 'icon fa-circle-o-notch',
          searchTerms: []
        },
        {
          title: 'icon fa-circle-thin',
          searchTerms: []
        },
        {
          title: 'icon fa-clock-o',
          searchTerms: []
        },
        {
          title: 'icon fa-close',
          searchTerms: []
        },
        {
          title: 'icon fa-cloud',
          searchTerms: []
        },
        {
          title: 'icon fa-cloud-download',
          searchTerms: []
        },
        {
          title: 'icon fa-cloud-upload',
          searchTerms: []
        },
        {
          title: 'icon fa-code',
          searchTerms: []
        },
        {
          title: 'icon fa-code-fork',
          searchTerms: []
        },
        {
          title: 'icon fa-coffee',
          searchTerms: []
        },
        {
          title: 'icon fa-cog',
          searchTerms: []
        },
        {
          title: 'icon fa-cogs',
          searchTerms: []
        },
        {
          title: 'icon fa-comment',
          searchTerms: []
        },
        {
          title: 'icon fa-comment-o',
          searchTerms: []
        },
        {
          title: 'icon fa-comments',
          searchTerms: []
        },
        {
          title: 'icon fa-comments-o',
          searchTerms: []
        },
        {
          title: 'icon fa-compass',
          searchTerms: []
        },
        {
          title: 'icon fa-copyright',
          searchTerms: []
        },
        {
          title: 'icon fa-credit-card',
          searchTerms: []
        },
        {
          title: 'icon fa-crop',
          searchTerms: []
        },
        {
          title: 'icon fa-crosshairs',
          searchTerms: []
        },
        {
          title: 'icon fa-cube',
          searchTerms: []
        },
        {
          title: 'icon fa-cubes',
          searchTerms: []
        },
        {
          title: 'icon fa-cutlery',
          searchTerms: []
        },
        {
          title: 'icon fa-dashboard',
          searchTerms: []
        },
        {
          title: 'icon fa-database',
          searchTerms: []
        },
        {
          title: 'icon fa-desktop',
          searchTerms: []
        },
        {
          title: 'icon fa-diamond',
          searchTerms: []
        },
        {
          title: 'icon fa-dot-circle-o',
          searchTerms: []
        },
        {
          title: 'icon fa-download',
          searchTerms: []
        },
        {
          title: 'icon fa-edit',
          searchTerms: []
        },
        {
          title: 'icon fa-ellipsis-h',
          searchTerms: []
        },
        {
          title: 'icon fa-ellipsis-v',
          searchTerms: []
        },
        {
          title: 'icon fa-envelope',
          searchTerms: []
        },
        {
          title: 'icon fa-envelope-o',
          searchTerms: []
        },
        {
          title: 'icon fa-envelope-square',
          searchTerms: []
        },
        {
          title: 'icon fa-eraser',
          searchTerms: []
        },
        {
          title: 'icon fa-exchange',
          searchTerms: []
        },
        {
          title: 'icon fa-exclamation',
          searchTerms: []
        },
        {
          title: 'icon fa-exclamation-circle',
          searchTerms: []
        },
        {
          title: 'icon fa-exclamation-triangle',
          searchTerms: []
        },
        {
          title: 'icon fa-external-link',
          searchTerms: []
        },
        {
          title: 'icon fa-external-link-square',
          searchTerms: []
        },
        {
          title: 'icon fa-eye',
          searchTerms: []
        },
        {
          title: 'icon fa-eye-slash',
          searchTerms: []
        },
        {
          title: 'icon fa-eyedropper',
          searchTerms: []
        },
        {
          title: 'icon fa-fax',
          searchTerms: []
        },
        {
          title: 'icon fa-female',
          searchTerms: []
        },
        {
          title: 'icon fa-fighter-jet',
          searchTerms: []
        },
        {
          title: 'icon fa-file-archive-o',
          searchTerms: []
        },
        {
          title: 'icon fa-file-audio-o',
          searchTerms: []
        },
        {
          title: 'icon fa-file-code-o',
          searchTerms: []
        },
        {
          title: 'icon fa-file-excel-o',
          searchTerms: []
        },
        {
          title: 'icon fa-file-image-o',
          searchTerms: []
        },
        {
          title: 'icon fa-file-movie-o',
          searchTerms: []
        },
        {
          title: 'icon fa-file-pdf-o',
          searchTerms: []
        },
        {
          title: 'icon fa-file-photo-o',
          searchTerms: []
        },
        {
          title: 'icon fa-file-picture-o',
          searchTerms: []
        },
        {
          title: 'icon fa-file-powerpoint-o',
          searchTerms: []
        },
        {
          title: 'icon fa-file-sound-o',
          searchTerms: []
        },
        {
          title: 'icon fa-file-video-o',
          searchTerms: []
        },
        {
          title: 'icon fa-file-word-o',
          searchTerms: []
        },
        {
          title: 'icon fa-file-zip-o',
          searchTerms: []
        },
        {
          title: 'icon fa-film',
          searchTerms: []
        },
        {
          title: 'icon fa-filter',
          searchTerms: []
        },
        {
          title: 'icon fa-fire',
          searchTerms: []
        },
        {
          title: 'icon fa-fire-extinguisher',
          searchTerms: []
        },
        {
          title: 'icon fa-flag',
          searchTerms: []
        },
        {
          title: 'icon fa-flag-checkered',
          searchTerms: []
        },
        {
          title: 'icon fa-flag-o',
          searchTerms: []
        },
        {
          title: 'icon fa-flash',
          searchTerms: []
        },
        {
          title: 'icon fa-flask',
          searchTerms: []
        },
        {
          title: 'icon fa-folder',
          searchTerms: []
        },
        {
          title: 'icon fa-folder-o',
          searchTerms: []
        },
        {
          title: 'icon fa-folder-open',
          searchTerms: []
        },
        {
          title: 'icon fa-folder-open-o',
          searchTerms: []
        },
        {
          title: 'icon fa-frown-o',
          searchTerms: []
        },
        {
          title: 'icon fa-futbol-o',
          searchTerms: []
        },
        {
          title: 'icon fa-gamepad',
          searchTerms: []
        },
        {
          title: 'icon fa-gavel',
          searchTerms: []
        },
        {
          title: 'icon fa-gear',
          searchTerms: []
        },
        {
          title: 'icon fa-gears',
          searchTerms: []
        },
        {
          title: 'icon fa-genderless',
          searchTerms: []
        },
        {
          title: 'icon fa-gift',
          searchTerms: []
        },
        {
          title: 'icon fa-glass',
          searchTerms: []
        },
        {
          title: 'icon fa-globe',
          searchTerms: []
        },
        {
          title: 'icon fa-graduation-cap',
          searchTerms: []
        },
        {
          title: 'icon fa-group',
          searchTerms: []
        },
        {
          title: 'icon fa-hdd-o',
          searchTerms: []
        },
        {
          title: 'icon fa-headphones',
          searchTerms: []
        },
        {
          title: 'icon fa-heart',
          searchTerms: []
        },
        {
          title: 'icon fa-heart-o',
          searchTerms: []
        },
        {
          title: 'icon fa-heartbeat',
          searchTerms: []
        },
        {
          title: 'icon fa-history',
          searchTerms: []
        },
        {
          title: 'icon fa-home',
          searchTerms: []
        },
        {
          title: 'icon fa-hotel',
          searchTerms: []
        },
        {
          title: 'icon fa-image',
          searchTerms: []
        },
        {
          title: 'icon fa-inbox',
          searchTerms: []
        },
        {
          title: 'icon fa-info',
          searchTerms: []
        },
        {
          title: 'icon fa-info-circle',
          searchTerms: []
        },
        {
          title: 'icon fa-institution',
          searchTerms: []
        },
        {
          title: 'icon fa-key',
          searchTerms: []
        },
        {
          title: 'icon fa-keyboard-o',
          searchTerms: []
        },
        {
          title: 'icon fa-language',
          searchTerms: []
        },
        {
          title: 'icon fa-laptop',
          searchTerms: []
        },
        {
          title: 'icon fa-leaf',
          searchTerms: []
        },
        {
          title: 'icon fa-legal',
          searchTerms: []
        },
        {
          title: 'icon fa-lemon-o',
          searchTerms: []
        },
        {
          title: 'icon fa-level-down',
          searchTerms: []
        },
        {
          title: 'icon fa-level-up',
          searchTerms: []
        },
        {
          title: 'icon fa-life-bouy',
          searchTerms: []
        },
        {
          title: 'icon fa-life-buoy',
          searchTerms: []
        },
        {
          title: 'icon fa-life-ring',
          searchTerms: []
        },
        {
          title: 'icon fa-life-saver',
          searchTerms: []
        },
        {
          title: 'icon fa-lightbulb-o',
          searchTerms: []
        },
        {
          title: 'icon fa-line-chart',
          searchTerms: []
        },
        {
          title: 'icon fa-location-arrow',
          searchTerms: []
        },
        {
          title: 'icon fa-lock',
          searchTerms: []
        },
        {
          title: 'icon fa-magic',
          searchTerms: []
        },
        {
          title: 'icon fa-magnet',
          searchTerms: []
        },
        {
          title: 'icon fa-mail-forward',
          searchTerms: []
        },
        {
          title: 'icon fa-mail-reply',
          searchTerms: []
        },
        {
          title: 'icon fa-mail-reply-all',
          searchTerms: []
        },
        {
          title: 'icon fa-male',
          searchTerms: []
        },
        {
          title: 'icon fa-map-marker',
          searchTerms: []
        },
        {
          title: 'icon fa-meh-o',
          searchTerms: []
        },
        {
          title: 'icon fa-microphone',
          searchTerms: []
        },
        {
          title: 'icon fa-microphone-slash',
          searchTerms: []
        },
        {
          title: 'icon fa-minus',
          searchTerms: []
        },
        {
          title: 'icon fa-minus-circle',
          searchTerms: []
        },
        {
          title: 'icon fa-minus-square',
          searchTerms: []
        },
        {
          title: 'icon fa-minus-square-o',
          searchTerms: []
        },
        {
          title: 'icon fa-mobile',
          searchTerms: []
        },
        {
          title: 'icon fa-mobile-phone',
          searchTerms: []
        },
        {
          title: 'icon fa-money',
          searchTerms: []
        },
        {
          title: 'icon fa-moon-o',
          searchTerms: []
        },
        {
          title: 'icon fa-mortar-board',
          searchTerms: []
        },
        {
          title: 'icon fa-motorcycle',
          searchTerms: []
        },
        {
          title: 'icon fa-music',
          searchTerms: []
        },
        {
          title: 'icon fa-navicon',
          searchTerms: []
        },
        {
          title: 'icon fa-newspaper-o',
          searchTerms: []
        },
        {
          title: 'icon fa-paint-brush',
          searchTerms: []
        },
        {
          title: 'icon fa-paper-plane',
          searchTerms: []
        },
        {
          title: 'icon fa-paper-plane-o',
          searchTerms: []
        },
        {
          title: 'icon fa-paw',
          searchTerms: []
        },
        {
          title: 'icon fa-pencil',
          searchTerms: []
        },
        {
          title: 'icon fa-pencil-square',
          searchTerms: []
        },
        {
          title: 'icon fa-pencil-square-o',
          searchTerms: []
        },
        {
          title: 'icon fa-phone',
          searchTerms: []
        },
        {
          title: 'icon fa-phone-square',
          searchTerms: []
        },
        {
          title: 'icon fa-photo',
          searchTerms: []
        },
        {
          title: 'icon fa-picture-o',
          searchTerms: []
        },
        {
          title: 'icon fa-pie-chart',
          searchTerms: []
        },
        {
          title: 'icon fa-plane',
          searchTerms: []
        },
        {
          title: 'icon fa-plug',
          searchTerms: []
        },
        {
          title: 'icon fa-plus',
          searchTerms: []
        },
        {
          title: 'icon fa-plus-circle',
          searchTerms: []
        },
        {
          title: 'icon fa-plus-square',
          searchTerms: []
        },
        {
          title: 'icon fa-plus-square-o',
          searchTerms: []
        },
        {
          title: 'icon fa-power-off',
          searchTerms: []
        },
        {
          title: 'icon fa-print',
          searchTerms: []
        },
        {
          title: 'icon fa-puzzle-piece',
          searchTerms: []
        },
        {
          title: 'icon fa-qrcode',
          searchTerms: []
        },
        {
          title: 'icon fa-question',
          searchTerms: []
        },
        {
          title: 'icon fa-question-circle',
          searchTerms: []
        },
        {
          title: 'icon fa-quote-left',
          searchTerms: []
        },
        {
          title: 'icon fa-quote-right',
          searchTerms: []
        },
        {
          title: 'icon fa-random',
          searchTerms: []
        },
        {
          title: 'icon fa-recycle',
          searchTerms: []
        },
        {
          title: 'icon fa-refresh',
          searchTerms: []
        },
        {
          title: 'icon fa-remove',
          searchTerms: []
        },
        {
          title: 'icon fa-reorder',
          searchTerms: []
        },
        {
          title: 'icon fa-reply',
          searchTerms: []
        },
        {
          title: 'icon fa-reply-all',
          searchTerms: []
        },
        {
          title: 'icon fa-retweet',
          searchTerms: []
        },
        {
          title: 'icon fa-road',
          searchTerms: []
        },
        {
          title: 'icon fa-rocket',
          searchTerms: []
        },
        {
          title: 'icon fa-rss',
          searchTerms: []
        },
        {
          title: 'icon fa-rss-square',
          searchTerms: []
        },
        {
          title: 'icon fa-search',
          searchTerms: []
        },
        {
          title: 'icon fa-search-minus',
          searchTerms: []
        },
        {
          title: 'icon fa-search-plus',
          searchTerms: []
        },
        {
          title: 'icon fa-send',
          searchTerms: []
        },
        {
          title: 'icon fa-send-o',
          searchTerms: []
        },
        {
          title: 'icon fa-server',
          searchTerms: []
        },
        {
          title: 'icon fa-share',
          searchTerms: []
        },
        {
          title: 'icon fa-share-alt',
          searchTerms: []
        },
        {
          title: 'icon fa-share-alt-square',
          searchTerms: []
        },
        {
          title: 'icon fa-share-square',
          searchTerms: []
        },
        {
          title: 'icon fa-share-square-o',
          searchTerms: []
        },
        {
          title: 'icon fa-shield',
          searchTerms: []
        },
        {
          title: 'icon fa-ship',
          searchTerms: []
        },
        {
          title: 'icon fa-shopping-cart',
          searchTerms: []
        },
        {
          title: 'icon fa-sign-in',
          searchTerms: []
        },
        {
          title: 'icon fa-sign-out',
          searchTerms: []
        },
        {
          title: 'icon fa-signal',
          searchTerms: []
        },
        {
          title: 'icon fa-sitemap',
          searchTerms: []
        },
        {
          title: 'icon fa-sliders',
          searchTerms: []
        },
        {
          title: 'icon fa-smile-o',
          searchTerms: []
        },
        {
          title: 'icon fa-soccer-ball-o',
          searchTerms: []
        },
        {
          title: 'icon fa-sort',
          searchTerms: []
        },
        {
          title: 'icon fa-sort-alpha-asc',
          searchTerms: []
        },
        {
          title: 'icon fa-sort-alpha-desc',
          searchTerms: []
        },
        {
          title: 'icon fa-sort-amount-asc',
          searchTerms: []
        },
        {
          title: 'icon fa-sort-amount-desc',
          searchTerms: []
        },
        {
          title: 'icon fa-sort-asc',
          searchTerms: []
        },
        {
          title: 'icon fa-sort-desc',
          searchTerms: []
        },
        {
          title: 'icon fa-sort-down',
          searchTerms: []
        },
        {
          title: 'icon fa-sort-numeric-asc',
          searchTerms: []
        },
        {
          title: 'icon fa-sort-numeric-desc',
          searchTerms: []
        },
        {
          title: 'icon fa-sort-up',
          searchTerms: []
        },
        {
          title: 'icon fa-space-shuttle',
          searchTerms: []
        },
        {
          title: 'icon fa-spinner',
          searchTerms: []
        },
        {
          title: 'icon fa-spoon',
          searchTerms: []
        },
        {
          title: 'icon fa-square',
          searchTerms: []
        },
        {
          title: 'icon fa-square-o',
          searchTerms: []
        },
        {
          title: 'icon fa-star',
          searchTerms: []
        },
        {
          title: 'icon fa-star-half',
          searchTerms: []
        },
        {
          title: 'icon fa-star-half-empty',
          searchTerms: []
        },
        {
          title: 'icon fa-star-half-full',
          searchTerms: []
        },
        {
          title: 'icon fa-star-half-o',
          searchTerms: []
        },
        {
          title: 'icon fa-star-o',
          searchTerms: []
        },
        {
          title: 'icon fa-street-view',
          searchTerms: []
        },
        {
          title: 'icon fa-suitcase',
          searchTerms: []
        },
        {
          title: 'icon fa-sun-o',
          searchTerms: []
        },
        {
          title: 'icon fa-support',
          searchTerms: []
        },
        {
          title: 'icon fa-tablet',
          searchTerms: []
        },
        {
          title: 'icon fa-tachometer',
          searchTerms: []
        },
        {
          title: 'icon fa-tag',
          searchTerms: []
        },
        {
          title: 'icon fa-tags',
          searchTerms: []
        },
        {
          title: 'icon fa-tasks',
          searchTerms: []
        },
        {
          title: 'icon fa-taxi',
          searchTerms: []
        },
        {
          title: 'icon fa-terminal',
          searchTerms: []
        },
        {
          title: 'icon fa-thumb-tack',
          searchTerms: []
        },
        {
          title: 'icon fa-thumbs-down',
          searchTerms: []
        },
        {
          title: 'icon fa-thumbs-o-down',
          searchTerms: []
        },
        {
          title: 'icon fa-thumbs-o-up',
          searchTerms: []
        },
        {
          title: 'icon fa-thumbs-up',
          searchTerms: []
        },
        {
          title: 'icon fa-ticket',
          searchTerms: []
        },
        {
          title: 'icon fa-times',
          searchTerms: []
        },
        {
          title: 'icon fa-times-circle',
          searchTerms: []
        },
        {
          title: 'icon fa-times-circle-o',
          searchTerms: []
        },
        {
          title: 'icon fa-tint',
          searchTerms: []
        },
        {
          title: 'icon fa-toggle-down',
          searchTerms: []
        },
        {
          title: 'icon fa-toggle-left',
          searchTerms: []
        },
        {
          title: 'icon fa-toggle-off',
          searchTerms: []
        },
        {
          title: 'icon fa-toggle-on',
          searchTerms: []
        },
        {
          title: 'icon fa-toggle-right',
          searchTerms: []
        },
        {
          title: 'icon fa-toggle-up',
          searchTerms: []
        },
        {
          title: 'icon fa-trash',
          searchTerms: []
        },
        {
          title: 'icon fa-trash-o',
          searchTerms: []
        },
        {
          title: 'icon fa-tree',
          searchTerms: []
        },
        {
          title: 'icon fa-trophy',
          searchTerms: []
        },
        {
          title: 'icon fa-truck',
          searchTerms: []
        },
        {
          title: 'icon fa-tty',
          searchTerms: []
        },
        {
          title: 'icon fa-umbrella',
          searchTerms: []
        },
        {
          title: 'icon fa-university',
          searchTerms: []
        },
        {
          title: 'icon fa-unlock',
          searchTerms: []
        },
        {
          title: 'icon fa-unlock-alt',
          searchTerms: []
        },
        {
          title: 'icon fa-unsorted',
          searchTerms: []
        },
        {
          title: 'icon fa-upload',
          searchTerms: []
        },
        {
          title: 'icon fa-user',
          searchTerms: []
        },
        {
          title: 'icon fa-user-plus',
          searchTerms: []
        },
        {
          title: 'icon fa-user-secret',
          searchTerms: []
        },
        {
          title: 'icon fa-user-times',
          searchTerms: []
        },
        {
          title: 'icon fa-users',
          searchTerms: []
        },
        {
          title: 'icon fa-video-camera',
          searchTerms: []
        },
        {
          title: 'icon fa-volume-down',
          searchTerms: []
        },
        {
          title: 'icon fa-volume-off',
          searchTerms: []
        },
        {
          title: 'icon fa-volume-up',
          searchTerms: []
        },
        {
          title: 'icon fa-warning',
          searchTerms: []
        },
        {
          title: 'icon fa-wheelchair',
          searchTerms: []
        },
        {
          title: 'icon fa-wifi',
          searchTerms: []
        },
        {
          title: 'icon fa-wrench',
          searchTerms: []
        },
        {
          title: 'icon fa-ambulance',
          searchTerms: []
        },
        {
          title: 'icon fa-subway',
          searchTerms: []
        },
        {
          title: 'icon fa-train',
          searchTerms: []
        },
        {
          title: 'icon fa-mars',
          searchTerms: []
        },
        {
          title: 'icon fa-mars-double',
          searchTerms: []
        },
        {
          title: 'icon fa-mars-stroke',
          searchTerms: []
        },
        {
          title: 'icon fa-mars-stroke-h',
          searchTerms: []
        },
        {
          title: 'icon fa-mars-stroke-v',
          searchTerms: []
        },
        {
          title: 'icon fa-mercury',
          searchTerms: []
        },
        {
          title: 'icon fa-neuter',
          searchTerms: []
        },
        {
          title: 'icon fa-transgender',
          searchTerms: []
        },
        {
          title: 'icon fa-transgender-alt',
          searchTerms: []
        },
        {
          title: 'icon fa-venus',
          searchTerms: []
        },
        {
          title: 'icon fa-venus-double',
          searchTerms: []
        },
        {
          title: 'icon fa-venus-mars',
          searchTerms: []
        },
        {
          title: 'icon fa-file',
          searchTerms: []
        },
        {
          title: 'icon fa-file-o',
          searchTerms: []
        },
        {
          title: 'icon fa-file-text',
          searchTerms: []
        },
        {
          title: 'icon fa-file-text-o',
          searchTerms: []
        },
        {
          title: 'icon fa-cc-amex',
          searchTerms: []
        },
        {
          title: 'icon fa-cc-discover',
          searchTerms: []
        },
        {
          title: 'icon fa-cc-mastercard',
          searchTerms: []
        },
        {
          title: 'icon fa-cc-paypal',
          searchTerms: []
        },
        {
          title: 'icon fa-cc-stripe',
          searchTerms: []
        },
        {
          title: 'icon fa-cc-visa',
          searchTerms: []
        },
        {
          title: 'icon fa-google-wallet',
          searchTerms: []
        },
        {
          title: 'icon fa-paypal',
          searchTerms: []
        },
        {
          title: 'icon fa-bitcoin',
          searchTerms: []
        },
        {
          title: 'icon fa-btc',
          searchTerms: []
        },
        {
          title: 'icon fa-cny',
          searchTerms: []
        },
        {
          title: 'icon fa-dollar',
          searchTerms: []
        },
        {
          title: 'icon fa-eur',
          searchTerms: []
        },
        {
          title: 'icon fa-euro',
          searchTerms: []
        },
        {
          title: 'icon fa-gbp',
          searchTerms: []
        },
        {
          title: 'icon fa-ils',
          searchTerms: []
        },
        {
          title: 'icon fa-inr',
          searchTerms: []
        },
        {
          title: 'icon fa-jpy',
          searchTerms: []
        },
        {
          title: 'icon fa-krw',
          searchTerms: []
        },
        {
          title: 'icon fa-rmb',
          searchTerms: []
        },
        {
          title: 'icon fa-rouble',
          searchTerms: []
        },
        {
          title: 'icon fa-rub',
          searchTerms: []
        },
        {
          title: 'icon fa-ruble',
          searchTerms: []
        },
        {
          title: 'icon fa-rupee',
          searchTerms: []
        },
        {
          title: 'icon fa-shekel',
          searchTerms: []
        },
        {
          title: 'icon fa-sheqel',
          searchTerms: []
        },
        {
          title: 'icon fa-try',
          searchTerms: []
        },
        {
          title: 'icon fa-turkish-lira',
          searchTerms: []
        },
        {
          title: 'icon fa-usd',
          searchTerms: []
        },
        {
          title: 'icon fa-won',
          searchTerms: []
        },
        {
          title: 'icon fa-yen',
          searchTerms: []
        },
        {
          title: 'icon fa-align-center',
          searchTerms: []
        },
        {
          title: 'icon fa-align-justify',
          searchTerms: []
        },
        {
          title: 'icon fa-align-left',
          searchTerms: []
        },
        {
          title: 'icon fa-align-right',
          searchTerms: []
        },
        {
          title: 'icon fa-bold',
          searchTerms: []
        },
        {
          title: 'icon fa-chain',
          searchTerms: []
        },
        {
          title: 'icon fa-chain-broken',
          searchTerms: []
        },
        {
          title: 'icon fa-clipboard',
          searchTerms: []
        },
        {
          title: 'icon fa-columns',
          searchTerms: []
        },
        {
          title: 'icon fa-copy',
          searchTerms: []
        },
        {
          title: 'icon fa-cut',
          searchTerms: []
        },
        {
          title: 'icon fa-dedent',
          searchTerms: []
        },
        {
          title: 'icon fa-files-o',
          searchTerms: []
        },
        {
          title: 'icon fa-floppy-o',
          searchTerms: []
        },
        {
          title: 'icon fa-font',
          searchTerms: []
        },
        {
          title: 'icon fa-header',
          searchTerms: []
        },
        {
          title: 'icon fa-indent',
          searchTerms: []
        },
        {
          title: 'icon fa-italic',
          searchTerms: []
        },
        {
          title: 'icon fa-link',
          searchTerms: []
        },
        {
          title: 'icon fa-list',
          searchTerms: []
        },
        {
          title: 'icon fa-list-alt',
          searchTerms: []
        },
        {
          title: 'icon fa-list-ol',
          searchTerms: []
        },
        {
          title: 'icon fa-list-ul',
          searchTerms: []
        },
        {
          title: 'icon fa-outdent',
          searchTerms: []
        },
        {
          title: 'icon fa-paperclip',
          searchTerms: []
        },
        {
          title: 'icon fa-paragraph',
          searchTerms: []
        },
        {
          title: 'icon fa-paste',
          searchTerms: []
        },
        {
          title: 'icon fa-repeat',
          searchTerms: []
        },
        {
          title: 'icon fa-rotate-left',
          searchTerms: []
        },
        {
          title: 'icon fa-rotate-right',
          searchTerms: []
        },
        {
          title: 'icon fa-save',
          searchTerms: []
        },
        {
          title: 'icon fa-scissors',
          searchTerms: []
        },
        {
          title: 'icon fa-strikethrough',
          searchTerms: []
        },
        {
          title: 'icon fa-subscript',
          searchTerms: []
        },
        {
          title: 'icon fa-superscript',
          searchTerms: []
        },
        {
          title: 'icon fa-table',
          searchTerms: []
        },
        {
          title: 'icon fa-text-height',
          searchTerms: []
        },
        {
          title: 'icon fa-text-width',
          searchTerms: []
        },
        {
          title: 'icon fa-th',
          searchTerms: []
        },
        {
          title: 'icon fa-th-large',
          searchTerms: []
        },
        {
          title: 'icon fa-th-list',
          searchTerms: []
        },
        {
          title: 'icon fa-underline',
          searchTerms: []
        },
        {
          title: 'icon fa-undo',
          searchTerms: []
        },
        {
          title: 'icon fa-unlink',
          searchTerms: []
        },
        {
          title: 'icon fa-angle-double-down',
          searchTerms: []
        },
        {
          title: 'icon fa-angle-double-left',
          searchTerms: []
        },
        {
          title: 'icon fa-angle-double-right',
          searchTerms: []
        },
        {
          title: 'icon fa-angle-double-up',
          searchTerms: []
        },
        {
          title: 'icon fa-angle-down',
          searchTerms: []
        },
        {
          title: 'icon fa-angle-left',
          searchTerms: []
        },
        {
          title: 'icon fa-angle-right',
          searchTerms: []
        },
        {
          title: 'icon fa-angle-up',
          searchTerms: []
        },
        {
          title: 'icon fa-arrow-circle-down',
          searchTerms: []
        },
        {
          title: 'icon fa-arrow-circle-left',
          searchTerms: []
        },
        {
          title: 'icon fa-arrow-circle-o-down',
          searchTerms: []
        },
        {
          title: 'icon fa-arrow-circle-o-left',
          searchTerms: []
        },
        {
          title: 'icon fa-arrow-circle-o-right',
          searchTerms: []
        },
        {
          title: 'icon fa-arrow-circle-o-up',
          searchTerms: []
        },
        {
          title: 'icon fa-arrow-circle-right',
          searchTerms: []
        },
        {
          title: 'icon fa-arrow-circle-up',
          searchTerms: []
        },
        {
          title: 'icon fa-arrow-down',
          searchTerms: []
        },
        {
          title: 'icon fa-arrow-left',
          searchTerms: []
        },
        {
          title: 'icon fa-arrow-right',
          searchTerms: []
        },
        {
          title: 'icon fa-arrow-up',
          searchTerms: []
        },
        {
          title: 'icon fa-arrows-alt',
          searchTerms: []
        },
        {
          title: 'icon fa-caret-down',
          searchTerms: []
        },
        {
          title: 'icon fa-caret-left',
          searchTerms: []
        },
        {
          title: 'icon fa-caret-right',
          searchTerms: []
        },
        {
          title: 'icon fa-caret-up',
          searchTerms: []
        },
        {
          title: 'icon fa-chevron-circle-down',
          searchTerms: []
        },
        {
          title: 'icon fa-chevron-circle-left',
          searchTerms: []
        },
        {
          title: 'icon fa-chevron-circle-right',
          searchTerms: []
        },
        {
          title: 'icon fa-chevron-circle-up',
          searchTerms: []
        },
        {
          title: 'icon fa-chevron-down',
          searchTerms: []
        },
        {
          title: 'icon fa-chevron-left',
          searchTerms: []
        },
        {
          title: 'icon fa-chevron-right',
          searchTerms: []
        },
        {
          title: 'icon fa-chevron-up',
          searchTerms: []
        },
        {
          title: 'icon fa-hand-o-down',
          searchTerms: []
        },
        {
          title: 'icon fa-hand-o-left',
          searchTerms: []
        },
        {
          title: 'icon fa-hand-o-right',
          searchTerms: []
        },
        {
          title: 'icon fa-hand-o-up',
          searchTerms: []
        },
        {
          title: 'icon fa-long-arrow-down',
          searchTerms: []
        },
        {
          title: 'icon fa-long-arrow-left',
          searchTerms: []
        },
        {
          title: 'icon fa-long-arrow-right',
          searchTerms: []
        },
        {
          title: 'icon fa-long-arrow-up',
          searchTerms: []
        },
        {
          title: 'icon fa-backward',
          searchTerms: []
        },
        {
          title: 'icon fa-compress',
          searchTerms: []
        },
        {
          title: 'icon fa-eject',
          searchTerms: []
        },
        {
          title: 'icon fa-expand',
          searchTerms: []
        },
        {
          title: 'icon fa-fast-backward',
          searchTerms: []
        },
        {
          title: 'icon fa-fast-forward',
          searchTerms: []
        },
        {
          title: 'icon fa-forward',
          searchTerms: []
        },
        {
          title: 'icon fa-pause',
          searchTerms: []
        },
        {
          title: 'icon fa-play',
          searchTerms: []
        },
        {
          title: 'icon fa-play-circle',
          searchTerms: []
        },
        {
          title: 'icon fa-play-circle-o',
          searchTerms: []
        },
        {
          title: 'icon fa-step-backward',
          searchTerms: []
        },
        {
          title: 'icon fa-step-forward',
          searchTerms: []
        },
        {
          title: 'icon fa-stop',
          searchTerms: []
        },
        {
          title: 'icon fa-youtube-play',
          searchTerms: []
        },
        {
          title: 'icon fa-adn',
          searchTerms: []
        },
        {
          title: 'icon fa-android',
          searchTerms: []
        },
        {
          title: 'icon fa-angellist',
          searchTerms: []
        },
        {
          title: 'icon fa-apple',
          searchTerms: []
        },
        {
          title: 'icon fa-behance',
          searchTerms: []
        },
        {
          title: 'icon fa-behance-square',
          searchTerms: []
        },
        {
          title: 'icon fa-bitbucket',
          searchTerms: []
        },
        {
          title: 'icon fa-bitbucket-square',
          searchTerms: []
        },
        {
          title: 'icon fa-buysellads',
          searchTerms: []
        },
        {
          title: 'icon fa-codepen',
          searchTerms: []
        },
        {
          title: 'icon fa-connectdevelop',
          searchTerms: []
        },
        {
          title: 'icon fa-css3',
          searchTerms: []
        },
        {
          title: 'icon fa-dashcube',
          searchTerms: []
        },
        {
          title: 'icon fa-delicious',
          searchTerms: []
        },
        {
          title: 'icon fa-deviantart',
          searchTerms: []
        },
        {
          title: 'icon fa-digg',
          searchTerms: []
        },
        {
          title: 'icon fa-dribbble',
          searchTerms: []
        },
        {
          title: 'icon fa-dropbox',
          searchTerms: []
        },
        {
          title: 'icon fa-drupal',
          searchTerms: []
        },
        {
          title: 'icon fa-empire',
          searchTerms: []
        },
        {
          title: 'icon fa-facebook',
          searchTerms: []
        },
        {
          title: 'icon fa-facebook-f',
          searchTerms: []
        },
        {
          title: 'icon fa-facebook-official',
          searchTerms: []
        },
        {
          title: 'icon fa-facebook-square',
          searchTerms: []
        },
        {
          title: 'icon fa-flickr',
          searchTerms: []
        },
        {
          title: 'icon fa-forumbee',
          searchTerms: []
        },
        {
          title: 'icon fa-foursquare',
          searchTerms: []
        },
        {
          title: 'icon fa-ge',
          searchTerms: []
        },
        {
          title: 'icon fa-git',
          searchTerms: []
        },
        {
          title: 'icon fa-git-square',
          searchTerms: []
        },
        {
          title: 'icon fa-github',
          searchTerms: []
        },
        {
          title: 'icon fa-github-alt',
          searchTerms: []
        },
        {
          title: 'icon fa-github-square',
          searchTerms: []
        },
        {
          title: 'icon fa-gittip',
          searchTerms: []
        },
        {
          title: 'icon fa-google',
          searchTerms: []
        },
        {
          title: 'icon fa-google-plus',
          searchTerms: []
        },
        {
          title: 'icon fa-google-plus-square',
          searchTerms: []
        },
        {
          title: 'icon fa-gratipay',
          searchTerms: []
        },
        {
          title: 'icon fa-hacker-news',
          searchTerms: []
        },
        {
          title: 'icon fa-html5',
          searchTerms: []
        },
        {
          title: 'icon fa-instagram',
          searchTerms: []
        },
        {
          title: 'icon fa-ioxhost',
          searchTerms: []
        },
        {
          title: 'icon fa-joomla',
          searchTerms: []
        },
        {
          title: 'icon fa-jsfiddle',
          searchTerms: []
        },
        {
          title: 'icon fa-lastfm',
          searchTerms: []
        },
        {
          title: 'icon fa-lastfm-square',
          searchTerms: []
        },
        {
          title: 'icon fa-leanpub',
          searchTerms: []
        },
        {
          title: 'icon fa-linkedin',
          searchTerms: []
        },
        {
          title: 'icon fa-linkedin-square',
          searchTerms: []
        },
        {
          title: 'icon fa-linux',
          searchTerms: []
        },
        {
          title: 'icon fa-maxcdn',
          searchTerms: []
        },
        {
          title: 'icon fa-meanpath',
          searchTerms: []
        },
        {
          title: 'icon fa-medium',
          searchTerms: []
        },
        {
          title: 'icon fa-openid',
          searchTerms: []
        },
        {
          title: 'icon fa-pagelines',
          searchTerms: []
        },
        {
          title: 'icon fa-pied-piper',
          searchTerms: []
        },
        {
          title: 'icon fa-pied-piper-alt',
          searchTerms: []
        },
        {
          title: 'icon fa-pinterest',
          searchTerms: []
        },
        {
          title: 'icon fa-pinterest-p',
          searchTerms: []
        },
        {
          title: 'icon fa-pinterest-square',
          searchTerms: []
        },
        {
          title: 'icon fa-qq',
          searchTerms: []
        },
        {
          title: 'icon fa-ra',
          searchTerms: []
        },
        {
          title: 'icon fa-rebel',
          searchTerms: []
        },
        {
          title: 'icon fa-reddit',
          searchTerms: []
        },
        {
          title: 'icon fa-reddit-square',
          searchTerms: []
        },
        {
          title: 'icon fa-renren',
          searchTerms: []
        },
        {
          title: 'icon fa-sellsy',
          searchTerms: []
        },
        {
          title: 'icon fa-shirtsinbulk',
          searchTerms: []
        },
        {
          title: 'icon fa-simplybuilt',
          searchTerms: []
        },
        {
          title: 'icon fa-skyatlas',
          searchTerms: []
        },
        {
          title: 'icon fa-skype',
          searchTerms: []
        },
        {
          title: 'icon fa-slack',
          searchTerms: []
        },
        {
          title: 'icon fa-slideshare',
          searchTerms: []
        },
        {
          title: 'icon fa-soundcloud',
          searchTerms: []
        },
        {
          title: 'icon fa-spotify',
          searchTerms: []
        },
        {
          title: 'icon fa-stack-exchange',
          searchTerms: []
        },
        {
          title: 'icon fa-stack-overflow',
          searchTerms: []
        },
        {
          title: 'icon fa-steam',
          searchTerms: []
        },
        {
          title: 'icon fa-steam-square',
          searchTerms: []
        },
        {
          title: 'icon fa-stumbleupon',
          searchTerms: []
        },
        {
          title: 'icon fa-stumbleupon-circle',
          searchTerms: []
        },
        {
          title: 'icon fa-tencent-weibo',
          searchTerms: []
        },
        {
          title: 'icon fa-trello',
          searchTerms: []
        },
        {
          title: 'icon fa-tumblr',
          searchTerms: []
        },
        {
          title: 'icon fa-tumblr-square',
          searchTerms: []
        },
        {
          title: 'icon fa-twitch',
          searchTerms: []
        },
        {
          title: 'icon fa-twitter',
          searchTerms: []
        },
        {
          title: 'icon fa-twitter-square',
          searchTerms: []
        },
        {
          title: 'icon fa-viacoin',
          searchTerms: []
        },
        {
          title: 'icon fa-vimeo-square',
          searchTerms: []
        },
        {
          title: 'icon fa-vine',
          searchTerms: []
        },
        {
          title: 'icon fa-vk',
          searchTerms: []
        },
        {
          title: 'icon fa-wechat',
          searchTerms: []
        },
        {
          title: 'icon fa-weibo',
          searchTerms: []
        },
        {
          title: 'icon fa-weixin',
          searchTerms: []
        },
        {
          title: 'icon fa-whatsapp',
          searchTerms: []
        },
        {
          title: 'icon fa-windows',
          searchTerms: []
        },
        {
          title: 'icon fa-wordpress',
          searchTerms: []
        },
        {
          title: 'icon fa-xing',
          searchTerms: []
        },
        {
          title: 'icon fa-xing-square',
          searchTerms: []
        },
        {
          title: 'icon fa-yahoo',
          searchTerms: []
        },
        {
          title: 'icon fa-yelp',
          searchTerms: []
        },
        {
          title: 'icon fa-youtube',
          searchTerms: []
        },
        {
          title: 'icon fa-youtube-square',
          searchTerms: []
        },
        {
          title: 'icon fa-h-square',
          searchTerms: []
        },
        {
          title: 'icon fa-hospital-o',
          searchTerms: []
        },
        {
          title: 'icon fa-medkit',
          searchTerms: []
        },
        {
          title: 'icon fa-stethoscope',
          searchTerms: []
        },
        {title: 'icon fa-user-md', searchTerms: []}
      ]
    }
  });

  $.components.register('iconpickerWb', {
    mode: 'default',
    defaults: {
      fullClassFormatter: function(value) {
        return 'icon ' + value;
      },
      templates: {
        buttons:
          '<button class="iconpicker-btn iconpicker-btn-cancel btn btn-default btn-sm">取消</button>' +
          ' <button class="iconpicker-btn iconpicker-btn-accept btn btn-primary btn-sm">确认</button>',
        search:
          '<input type="search" class="form-control iconpicker-search" placeholder="查找图标">'
      },
      icons: [
        {
          title: 'icon wb-dashboard',
          searchTerms: []
        },
        {
          title: 'icon wb-inbox',
          searchTerms: []
        },
        {
          title: 'icon wb-cloud',
          searchTerms: []
        },
        {
          title: 'icon wb-bell',
          searchTerms: []
        },
        {
          title: 'icon wb-book',
          searchTerms: []
        },
        {
          title: 'icon wb-bookmark',
          searchTerms: []
        },
        {
          title: 'icon wb-tag',
          searchTerms: []
        },
        {
          title: 'icon wb-library',
          searchTerms: []
        },
        {
          title: 'icon wb-share',
          searchTerms: []
        },
        {
          title: 'icon wb-reply',
          searchTerms: []
        },
        {
          title: 'icon wb-refresh',
          searchTerms: []
        },
        {
          title: 'icon wb-move',
          searchTerms: []
        },
        {
          title: 'icon wb-chat',
          searchTerms: []
        },
        {
          title: 'icon wb-chat-working',
          searchTerms: []
        },
        {
          title: 'icon wb-chat-text',
          searchTerms: []
        },
        {
          title: 'icon wb-chat-group',
          searchTerms: []
        },
        {
          title: 'icon wb-envelope',
          searchTerms: []
        },
        {
          title: 'icon wb-envelope-open',
          searchTerms: []
        },
        {
          title: 'icon wb-user',
          searchTerms: []
        },
        {
          title: 'icon wb-user-circle',
          searchTerms: []
        },
        {
          title: 'icon wb-users',
          searchTerms: []
        },
        {
          title: 'icon wb-user-add',
          searchTerms: []
        },
        {
          title: 'icon wb-grid-9',
          searchTerms: []
        },
        {
          title: 'icon wb-grid-4',
          searchTerms: []
        },
        {
          title: 'icon wb-menu',
          searchTerms: []
        },
        {
          title: 'icon wb-layout',
          searchTerms: []
        },
        {
          title: 'icon wb-fullscreen',
          searchTerms: []
        },
        {
          title: 'icon wb-fullscreen-exit',
          searchTerms: []
        },
        {
          title: 'icon wb-expand',
          searchTerms: []
        },
        {
          title: 'icon wb-contract',
          searchTerms: []
        },
        {
          title: 'icon wb-arrow-expand',
          searchTerms: []
        },
        {
          title: 'icon wb-arrow-shrink',
          searchTerms: []
        },
        {
          title: 'icon wb-desktop',
          searchTerms: []
        },
        {
          title: 'icon wb-mobile',
          searchTerms: []
        },
        {
          title: 'icon wb-signal',
          searchTerms: []
        },
        {
          title: 'icon wb-power',
          searchTerms: []
        },
        {
          title: 'icon wb-more-horizontal',
          searchTerms: []
        },
        {
          title: 'icon wb-more-vertical',
          searchTerms: []
        },
        {
          title: 'icon wb-globe',
          searchTerms: []
        },
        {
          title: 'icon wb-map',
          searchTerms: []
        },
        {
          title: 'icon wb-flag',
          searchTerms: []
        },
        {
          title: 'icon wb-pie-chart',
          searchTerms: []
        },
        {
          title: 'icon wb-stats-bars',
          searchTerms: []
        },
        {
          title: 'icon wb-pluse',
          searchTerms: []
        },
        {
          title: 'icon wb-home',
          searchTerms: []
        },
        {
          title: 'icon wb-shopping-cart',
          searchTerms: []
        },
        {
          title: 'icon wb-payment',
          searchTerms: []
        },
        {
          title: 'icon wb-briefcase',
          searchTerms: []
        },
        {
          title: 'icon wb-search',
          searchTerms: []
        },
        {
          title: 'icon wb-zoom-in',
          searchTerms: []
        },
        {
          title: 'icon wb-zoom-out',
          searchTerms: []
        },
        {
          title: 'icon wb-download',
          searchTerms: []
        },
        {
          title: 'icon wb-upload',
          searchTerms: []
        },
        {
          title: 'icon wb-sort-asc',
          searchTerms: []
        },
        {
          title: 'icon wb-sort-des',
          searchTerms: []
        },
        {
          title: 'icon wb-graph-up',
          searchTerms: []
        },
        {
          title: 'icon wb-graph-down',
          searchTerms: []
        },
        {
          title: 'icon wb-replay',
          searchTerms: []
        },
        {
          title: 'icon wb-edit',
          searchTerms: []
        },
        {
          title: 'icon wb-pencil',
          searchTerms: []
        },
        {
          title: 'icon wb-rubber',
          searchTerms: []
        },
        {
          title: 'icon wb-crop',
          searchTerms: []
        },
        {
          title: 'icon wb-eye',
          searchTerms: []
        },
        {
          title: 'icon wb-eye-close',
          searchTerms: []
        },
        {
          title: 'icon wb-image',
          searchTerms: []
        },
        {
          title: 'icon wb-gallery',
          searchTerms: []
        },
        {
          title: 'icon wb-video',
          searchTerms: []
        },
        {
          title: 'icon wb-camera',
          searchTerms: []
        },
        {
          title: 'icon wb-folder',
          searchTerms: []
        },
        {
          title: 'icon wb-clipboard',
          searchTerms: []
        },
        {
          title: 'icon wb-order',
          searchTerms: []
        },
        {
          title: 'icon wb-file',
          searchTerms: []
        },
        {
          title: 'icon wb-copy',
          searchTerms: []
        },
        {
          title: 'icon wb-add-file',
          searchTerms: []
        },
        {
          title: 'icon wb-print',
          searchTerms: []
        },
        {
          title: 'icon wb-calendar',
          searchTerms: []
        },
        {
          title: 'icon wb-time',
          searchTerms: []
        },
        {
          title: 'icon wb-trash',
          searchTerms: []
        },
        {
          title: 'icon wb-plugin',
          searchTerms: []
        },
        {
          title: 'icon wb-extension',
          searchTerms: []
        },
        {
          title: 'icon wb-memory',
          searchTerms: []
        },
        {
          title: 'icon wb-settings',
          searchTerms: []
        },
        {
          title: 'icon wb-scissor',
          searchTerms: []
        },
        {
          title: 'icon wb-wrench',
          searchTerms: []
        },
        {
          title: 'icon wb-hammer',
          searchTerms: []
        },
        {
          title: 'icon wb-lock',
          searchTerms: []
        },
        {
          title: 'icon wb-unlock',
          searchTerms: []
        },
        {
          title: 'icon wb-volume-low',
          searchTerms: []
        },
        {
          title: 'icon wb-volume-high',
          searchTerms: []
        },
        {
          title: 'icon wb-volume-off',
          searchTerms: []
        },
        {
          title: 'icon wb-pause',
          searchTerms: []
        },
        {
          title: 'icon wb-play',
          searchTerms: []
        },
        {
          title: 'icon wb-stop',
          searchTerms: []
        },
        {
          title: 'icon wb-musical',
          searchTerms: []
        },
        {
          title: 'icon wb-random',
          searchTerms: []
        },
        {
          title: 'icon wb-reload',
          searchTerms: []
        },
        {
          title: 'icon wb-loop',
          searchTerms: []
        },
        {
          title: 'icon wb-text',
          searchTerms: []
        },
        {
          title: 'icon wb-bold',
          searchTerms: []
        },
        {
          title: 'icon wb-italic',
          searchTerms: []
        },
        {
          title: 'icon wb-underline',
          searchTerms: []
        },
        {
          title: 'icon wb-format-clear',
          searchTerms: []
        },
        {
          title: 'icon wb-text-type',
          searchTerms: []
        },
        {
          title: 'icon wb-table',
          searchTerms: []
        },
        {
          title: 'icon wb-attach-file',
          searchTerms: []
        },
        {
          title: 'icon wb-paperclip',
          searchTerms: []
        },
        {
          title: 'icon wb-link-intact',
          searchTerms: []
        },
        {
          title: 'icon wb-link',
          searchTerms: []
        },
        {
          title: 'icon wb-link-broken',
          searchTerms: []
        },
        {
          title: 'icon wb-indent-increase',
          searchTerms: []
        },
        {
          title: 'icon wb-indent-decrease',
          searchTerms: []
        },
        {
          title: 'icon wb-align-justify',
          searchTerms: []
        },
        {
          title: 'icon wb-align-left',
          searchTerms: []
        },
        {
          title: 'icon wb-align-center',
          searchTerms: []
        },
        {
          title: 'icon wb-align-right',
          searchTerms: []
        },
        {
          title: 'icon wb-list-numbered',
          searchTerms: []
        },
        {
          title: 'icon wb-list-bulleted',
          searchTerms: []
        },
        {
          title: 'icon wb-list',
          searchTerms: []
        },
        {
          title: 'icon wb-emoticon',
          searchTerms: []
        },
        {
          title: 'icon wb-quote-right',
          searchTerms: []
        },
        {
          title: 'icon wb-code',
          searchTerms: []
        },
        {
          title: 'icon wb-code-working',
          searchTerms: []
        },
        {
          title: 'icon wb-code-unfold',
          searchTerms: []
        },
        {
          title: 'icon wb-chevron-right',
          searchTerms: []
        },
        {
          title: 'icon wb-chevron-left',
          searchTerms: []
        },
        {
          title: 'icon wb-chevron-left-mini',
          searchTerms: []
        },
        {
          title: 'icon wb-chevron-right-mini',
          searchTerms: []
        },
        {
          title: 'icon wb-chevron-up',
          searchTerms: []
        },
        {
          title: 'icon wb-chevron-down',
          searchTerms: []
        },
        {
          title: 'icon wb-chevron-up-mini',
          searchTerms: []
        },
        {
          title: 'icon wb-chevron-down-mini',
          searchTerms: []
        },
        {
          title: 'icon wb-arrow-left',
          searchTerms: []
        },
        {
          title: 'icon wb-arrow-right',
          searchTerms: []
        },
        {
          title: 'icon wb-arrow-up',
          searchTerms: []
        },
        {
          title: 'icon wb-arrow-down',
          searchTerms: []
        },
        {
          title: 'icon wb-dropdown',
          searchTerms: []
        },
        {
          title: 'icon wb-dropup',
          searchTerms: []
        },
        {
          title: 'icon wb-dropright',
          searchTerms: []
        },
        {
          title: 'icon wb-dropleft',
          searchTerms: []
        },
        {
          title: 'icon wb-sort-vertical',
          searchTerms: []
        },
        {
          title: 'icon wb-triangle-left',
          searchTerms: []
        },
        {
          title: 'icon wb-triangle-right',
          searchTerms: []
        },
        {
          title: 'icon wb-triangle-down',
          searchTerms: []
        },
        {
          title: 'icon wb-triangle-up',
          searchTerms: []
        },
        {
          title: 'icon wb-check-circle',
          searchTerms: []
        },
        {
          title: 'icon wb-check',
          searchTerms: []
        },
        {
          title: 'icon wb-check-mini',
          searchTerms: []
        },
        {
          title: 'icon wb-close',
          searchTerms: []
        },
        {
          title: 'icon wb-close-mini',
          searchTerms: []
        },
        {
          title: 'icon wb-plus-circle',
          searchTerms: []
        },
        {
          title: 'icon wb-plus',
          searchTerms: []
        },
        {
          title: 'icon wb-minus-circle',
          searchTerms: []
        },
        {
          title: 'icon wb-minus',
          searchTerms: []
        },
        {
          title: 'icon wb-alert-circle',
          searchTerms: []
        },
        {
          title: 'icon wb-alert',
          searchTerms: []
        },
        {
          title: 'icon wb-help-circle',
          searchTerms: []
        },
        {
          title: 'icon wb-help',
          searchTerms: []
        },
        {
          title: 'icon wb-info-circle',
          searchTerms: []
        },
        {
          title: 'icon wb-info',
          searchTerms: []
        },
        {
          title: 'icon wb-warning',
          searchTerms: []
        },
        {
          title: 'icon wb-heart',
          searchTerms: []
        },
        {
          title: 'icon wb-heart-outline',
          searchTerms: []
        },
        {
          title: 'icon wb-star',
          searchTerms: []
        },
        {
          title: 'icon wb-star-half',
          searchTerms: []
        },
        {
          title: 'icon wb-star-outline',
          searchTerms: []
        },
        {
          title: 'icon wb-thumb-up',
          searchTerms: []
        },
        {
          title: 'icon wb-thumb-down',
          searchTerms: []
        },
        {
          title: 'icon wb-small-point',
          searchTerms: []
        },
        {
          title: 'icon wb-medium-point',
          searchTerms: []
        },
        {title: 'icon wb-large-point', searchTerms: []}
      ]
    }
  });
})(window, document, jQuery);
