/**
 * @author    Amasty Team
 * @copyright Copyright (c) Amasty Ltd. ( http://www.amasty.com/ )
 * @package   Amasty_Scroll
 */
define([
    "jquery",
    "jquery/ui"
], function ($, ui, fancybox) {

    $.widget('mage.amScrollScript', {
        options: {},
        type: 'auto',
        is_loading: 0,
        next_data_url: "",
        prev_data_url: "",
        next_data_cache: "",
        flag_next_cache: 0,
        prev_data_cache: "",
        flag_prev_cache: 0,
        pagesCount: 1,
        pagesLoaded: [],
        currentPage: 1,
        last_scroll: 0,
        disabled: 0,
        totalAmountSelector: '.toolbar-amount',
        totalNumberSelector: '.toolbar-number',
        toolbarSelector: '.toolbar.toolbar-products',
        amPageCountSelector: '#am-page-count',

        _create: function (options) {
            var self = this;
            $(document).on( "amscroll_refresh", function() {
                self._initialize();//run jQuery(document).trigger('amscroll_refresh');
            });

            this._initialize();
        },

        _initialize: function () {
            this.next_data_cache = "";
            this.pagesLoaded = [];
            this._initPagesCount();
            this.disabled = 1;

            var isValidConfiguration = this._validate();
            if (!isValidConfiguration) {
                return;
            }
            this.disabled = 0;
            this.type = this.options['actionMode'];

            this._preloadPages();
            this._hideToolbars();

            var self = this;
            $(window).scroll(function() {
                self._initPaginator();
            });
            setTimeout(function(){
                self._initPaginator();
            }, 7000);

            this._initProgressBar();
        },

        _validate: function () {
            if(!this.options['product_container']
                || $(this.options['product_container']).length === 0
            ){
                console.log('Please specify "Products Group" Dom selector in module settings.');
                return false;
            }

            if (this.pagesCount <= 1) {
                return false;
            }

            return true;
        },

        _initPagesCount: function () {
            this.pagesLoaded = [];
            var amPager = $(this.amPageCountSelector);
            if(amPager && amPager.length) {
                this.pagesCount = parseInt(amPager.html());
                return;
            }

            var parent = $(this.totalAmountSelector).first();
            if (parent) {
                var childs = parent.find(this.totalNumberSelector);
                if (parent && childs.length >= 3) {
                    var limit = jQuery('#limiter').val();
                    if ($(childs[2]).text() > 0 && limit) {
                        var allProducts = $(childs[2]).text();
                        var result = Math.ceil(
                            parseInt(allProducts) / parseInt(limit)
                        );
                        if (result > 1) {
                            this.pagesCount = result;
                            return;
                        }
                    }
                }
            }
            this.pagesCount = 1;
        },

        _preloadPages: function () {
            var currentPage = parseInt(this.options['current_page']);
            this.currentPage = currentPage;
            var  productContainer = $(this.options['product_container']);
            productContainer.attr('amscroll-page', currentPage);
            productContainer.addClass('amscroll-page');
            if (this.options['pageNumbers'] == '1') {
                var pageNumEl = this._generatePageTitle(currentPage);
                productContainer.before(pageNumEl);
            }
            this.pagesLoaded.push(currentPage);
            this._preloadPageAfter(currentPage);
            this._preloadPageBefore(currentPage);

        },

        _preloadPageAfter: function (page) {
            var nextPage = page + 1;
            if (nextPage && nextPage <= this.pagesCount) {
                this.next_data_url = this._generateUrl(nextPage, 1);
                this.pagesLoaded.push(nextPage);
                var self = this;
                self.flag_next_cache = 1;

                $.getJSON( this.next_data_url, function(data) {
                    if(data.categoryProducts){
                        self.flag_next_cache = 0;
                        self.next_data_cache = data;
                    } else {
                        self._stop();
                    }
                }).fail(function() {
                    self._stop();
                });

                this.next_data_url = '';
            }
        },

        _preloadPageBefore: function (page) {
            var prevPage = page - 1;
            if (prevPage && prevPage >= 1 ) {
                this.prev_data_url = this._generateUrl(prevPage, 1);
                this.pagesLoaded.unshift(prevPage);
                var self = this;
                self.flag_prev_cache = 1;

                $.getJSON( this.prev_data_url, function(data) {
                    if(data.categoryProducts){
                        self.flag_prev_cache = 0;
                        self.prev_data_cache = data;
                    } else {
                       self._stop();
                    }
                }).fail(function() {
                    self._stop();
                });

                this.prev_data_url = '';
            }
        },

        _stop: function(){
            this.disabled = 1;
            this._showToolbars();
            $('.amscroll-loading').hide();
        },

        _initPaginator: function () {
            if (this.disabled) {
               return;
            }
            var scroll_pos = $(window).scrollTop();
            var diff = $(document).height() - $(window).height();

            var blockAfterProducts = $(".main .products ~ .block-static-block");
            if (blockAfterProducts.length) {
                diff = diff - blockAfterProducts.height();
            }
            diff = 0.9 * diff;

            if (scroll_pos >= diff) {
                if (this.is_loading == 0) {
                    this._loadFollowing();
                }
            }

            if (scroll_pos <= this._getTopContainersHeight()) {
                if (this.is_loading == 0) {
                    this._loadPrevious();
                }
            }

            /*find current page and change url and scroll-bar*/
            this._calculateCurrentScrollPage(scroll_pos);

            var self = this;
            $(document).ready(function () {
                // if we have enough room, load the next batch
                var productContainer = $(self.options['product_container']);
                if ($(window).height() > productContainer.height()) {
                    if ("" != self.next_data_url) {
                        self._loadFollowing();
                    }
                }
            });
        },

        _calculateCurrentScrollPage: function (scroll_pos) {
            var self = this;
            if (Math.abs(scroll_pos - self.last_scroll) > $(window).height() * 0.1 ) {
                self.last_scroll = scroll_pos;
                $(self.options['product_container']).each(function(index) {
                    if (self._mostlyVisible(this)) {
                        var page = $(this).attr('amscroll-page');
                        page = parseInt(page);
                        if(page != self.currentPage) {
                            var newUrl = self._generateUrl(page, 0);
                            window.history.pushState({url: newUrl}, '', newUrl);
                            if (page) {
                                self.currentPage = page;
                                $("#amscroll-navbar-current").html(page);
                            }
                        }

                        return false;
                    }
                });
            }
        },

        _loadFollowing: function () {
            var self = this;
            if (this.flag_next_cache) {
                this._createLoading('after');
            }
            if (this.next_data_url != "" || this.next_data_cache) {
                this._createLoading('after');
                if (this.next_data_cache) {
                    this.showFollowing(this.next_data_cache);
                } else {
                    if (!this.flag_next_cache) {
                        this.is_loading = 1; // note: this will break when the server doesn't respond
                        $.getJSON(this.next_data_url, function (data) {
                            self.showFollowing(data);
                        });
                    }
                }
            }
        },

        showFollowing: function (data) {
            this.next_data_cache = false;
            this.next_data_url = '';
            if (data.categoryProducts) {
                if (this.type == 'button') {
                    this._generateButton(data, 'after');
                    this.is_loading = 0;

                    return;
                }
                this._insertNewProductBlock(data, 'after');
                this._afterShowFollowing();
            }
        },

        _afterShowFollowing: function () {
            var nextPage = $(this.pagesLoaded).get(-1) + 1;//last + 1
            if (nextPage && nextPage <= this.pagesCount && $.inArray(nextPage, this.pagesLoaded) == -1) {
                this.next_data_url = this._generateUrl(nextPage, 1);
                this.pagesLoaded.push(nextPage);
                var self = this;
                this.flag_next_cache = 1;
                $.getJSON(this.next_data_url, function(preview_data) {
                    self.flag_next_cache = 0;
                    self.next_data_cache = preview_data;
                    $(window).scroll();
                });
            }

            this.is_loading = 0;
        },

        _loadPrevious: function () {
            var self = this;
            if (this.flag_prev_cache) {
                this._createLoading('before');
            }
            if (this.prev_data_url != "" || this.prev_data_cache) {
                this._createLoading('before');
                if (this.prev_data_cache) {
                    this.showPrevious(this.prev_data_cache);
                } else {
                    if (!this.flag_prev_cache) {
                        this.is_loading = 1; // note: this will break when the server doesn't respond
                        $.getJSON(this.prev_data_url, function (data) {
                            self.showPrevious(data);
                        });
                    }
                }
            }
        },

        showPrevious: function (data) {
            this.prev_data_cache = false;
            this.prev_data_url = '';

            if (data.categoryProducts) {
                if (this.type == 'button') {
                    this._generateButton(data, 'before');
                    this.is_loading = 0;
                    return;
                }

                this._insertNewProductBlock(data, 'before');
                this._afterShowPrevious();
            }
        },

        _afterShowPrevious: function () {
            var prevPage = $(this.pagesLoaded).get(0) - 1;
            if (prevPage && prevPage >= 1  && $.inArray(prevPage, this.pagesLoaded) == -1) {
                this.prev_data_url = this._generateUrl(prevPage, 1);
                this.pagesLoaded.unshift(prevPage);
                var self = this;
                this.flag_prev_cache = 1;

                $.getJSON(this.prev_data_url, function(preview_data) {
                    self.flag_prev_cache = 0;
                    self.prev_data_cache = preview_data;
                    $(window).scroll();
                });
            }
            this.is_loading = 0;
        },

        _createLoading: function (position) {
            var loading = jQuery('<div/>', {
                class: 'amscroll-loading',
                style: 'background-image: url(' + this.options['loadingImage'] + ');',
                text: ' '
            });

            if ('after' == position) {
                if ($('.amscroll-page:last ~ .amscroll-loading').length == 0) {//check duplicating preloader
                    $(this.options['product_container']).last().after(loading);
                }
            } else {
                if ($('.amscroll-loading:not(.amscroll-page:last ~ .amscroll-loading)').length == 0) {//check duplicating preloader
                    var element = $('.amscroll-page-num, ' + this.options['product_container']).first();
                    element.before(loading);
                }
            }

            $('.amscroll-loading + .amscroll-loading').remove();
        },

        _generateButton: function (data, position) {
            var html = data.categoryProducts;
            var tmp = jQuery('<div/>').append(html);
            var onclick = "function(){}";
            var buttonElement = jQuery('<div/>', {
                class: 'amscroll-load-button',
                style: this.options['loadNextStyle'],
                text: this.options['loading' + position + 'TextButton']
            });
            buttonElement.attr('amscroll_page', data.currentPage);
            buttonElement.attr('amscroll_type', position);

            var productContainer =  tmp.find(this.options['product_container']);
            productContainer.hide();
            productContainer.before(buttonElement);

            data.categoryProducts = tmp.html();
            this._insertNewProductBlock(data, position);
        },

        buttonClick: function (event) {
            element = $(event.target);
            var page = element.attr('amscroll_page');
            var block = $('.amscroll-pages[amscroll-page="' + page + '"]');
            var type = element.attr('amscroll_type');
            if (block && block.length) {
                block.show();
                element.remove();
            }

            if (type == 'after') {
                this._afterShowFollowing();
            } else {
                this._afterShowPrevious();
            }
        },

        _insertNewProductBlock: function (data, position) {
            var self = this;
            var html = data.categoryProducts;
            var tmp = jQuery('<div/>').append(html);

            tmp.find(this.toolbarSelector).remove();
            tmp.find('.amasty-catalog-topnav').remove();//remove navigation top block
            var productContainer =  tmp.find(this.options['product_container']);
            productContainer.addClass('amscroll-pages').attr('amscroll-page', data.currentPage);

            if (this.options['pageNumbers'] == '1') {
                var pageNumEl = this._generatePageTitle(data.currentPage);
                productContainer.before(pageNumEl);
            }

            html = tmp.html();

            if ('after' == position) {
                $('.amscroll-page:last ~ .amscroll-loading').remove();
                $(this.options['product_container']).last().after(html);
            } else {
                var element = $('.amscroll-page-num, ' + this.options['product_container']).first();
                $('.amscroll-loading:not(.amscroll-page:last ~ .amscroll-loading)').remove();
                element.before(html);
                var item_height = element.height();

                if (this.type != 'button') {
                    window.scrollTo(0, $(window).scrollTop() + item_height);
                }
            }

            this._addObserverToProductLink($('.amscroll-pages[amscroll-page="' + data.currentPage + '"]'));

            $('.amscroll-load-button').click(function(item){
                self.buttonClick(item);
            });
        },

        _addObserverToProductLink: function (productContainer) {
            var self = this;
            productContainer.find('.item a').on("click", function (event) {
                try{
                    var parent = $(this).parents('.amscroll-pages').first();
                    var page = parent? parent.attr('amscroll-page'): null;
                    if (page) {
                        var newUrl = self._generateUrl(page, 0);
                        window.history.pushState({url: newUrl}, '', newUrl);
                    }
                }catch(e){}
            });

            productContainer.first().trigger('contentUpdated');
        },

        _generateUrl: function (page, addScroll) {
            var url = window.location.href;
            var params = this._getQueryParams(window.location.search);
            if (!params || !Object.keys(params).length) {
                if(page) {
                    var paramString = '?p=' + page;
                    if (addScroll) {
                        paramString += '&is_scroll=1';
                    }

                    if (url.indexOf('#') > 0) {
                        url = url.replace('#', paramString + '#')
                    } else {
                        url += paramString;
                    }
                }
            } else {
                url = url.replace(window.location.search, '');
                if(page) {
                    params['p'] = page;
                } else if(params['p']){
                    delete params['p'];
                }

                if (addScroll) {
                    params['is_scroll'] = 1;
                }

                if(Object.keys(params).length) {
                    params = decodeURIComponent($.param(params));
                    if (url.indexOf('#') > 0) {
                        url = url.replace('#', '?' + params + '#')
                    } else {
                        url += '?' + params;
                    }
                }
            }

            return url;
        },

        _getQueryParams: function (url) {
            url = url.split('+').join(' ');
            var params = {},
                tokens,
                re = /[?&]?([^=]+)=([^&]*)/g;

            while (tokens = re.exec(url)) {
                params[decodeURIComponent(tokens[1])] = decodeURIComponent(tokens[2]);
            }

            return params;
        },

        _hideToolbars: function () {
            $(this.totalAmountSelector).hide();
            $('.products ~ ' + this.toolbarSelector).hide();
        },

        _showToolbars: function () {
            $(this.totalAmountSelector).show();
            $('.products ~ ' + this.toolbarSelector).show();
        },

        _generatePageTitle: function (page) {
            var pageNumEl = jQuery('<div/>', {
                class: 'amscroll-page-num',
                id   : 'amscroll-page-num' + page,
                text: this.options['pageNumberContent'] + page
            });

            return pageNumEl;
        },

        _mostlyVisible: function (element) {
            element = $(element);
            var visible = element.is(":visible");
            var scroll_pos = $(window).scrollTop();
            var window_height = $(window).height();
            var el_top = element.offset().top;
            var el_height = element.height();
            var el_bottom = el_top + el_height;
            var result = (el_bottom - el_height * 0.25 > scroll_pos) &&
                            (el_top < (scroll_pos + 0.5 * window_height)) &&
                             visible;
            return result;
        },

        _getTopContainersHeight: function () {
            var result = $(".page-header").height() + $(".nav-sections").height();
            if ($(".main .block-static-block ~ .products ").length) {
                result += $(".main .block-static-block").height();
            }
            result = 0.9 * result;

            return result;
        },

        _initProgressBar: function () {
            if (this.options['progressbar'] && this.options['progressbar']['enabled'] == '1') {
                var progressbar = jQuery('<div/>', {
                    class: 'amscroll-navbar',
                    id   : 'amscroll-navbar',
                    style: this.options['progressbar']['styles']
                });

                var text = this.options['progressbarText'];
                text = text.replace('%1', '<span id="amscroll-navbar-current">' + this.currentPage + '</span>');
                text = text.replace('%2', this.pagesCount);
                progressbar.append('<span class="amscroll-navbar-text">' + text + '</span>');

                var linkElement = jQuery('<a/>', {
                    class: 'amscroll-navbar-link'
                });

                linkElement.click(function(){
                    $('body, html').animate({
                        scrollTop: 0
                    }, 300);
                });
                progressbar.append(linkElement);

                $("body").append(progressbar);
            }
        }
    });
});
