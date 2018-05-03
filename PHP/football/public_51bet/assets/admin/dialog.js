;(function ($, window, undefined) {
    function findAll(elems, selector) {
        return elems.filter(selector).add(elems.find(selector));
    }

    function parseHTML(html) {
        return $.parseHTML(html, document, true)
    }

    function extractContainer(data, _fragment) {
        var fullDocument = /<html/i.test(data);
        if (fullDocument) {
            var $body = $(parseHTML(data.match(/<body[^>]*>([\s\S.]*)<\/body>/i)[0]));
        } else {
            var $body = $(parseHTML(data));
        }

        if ($body.length === 0)
            return '';

        if (_fragment) {
            if (_fragment === 'body') {
                var $fragment = $body;
            } else {
                var $fragment = findAll($body, _fragment).first();
            }

            if ($fragment.length) {
                return _fragment === 'body' ? $fragment : $fragment.contents();
            }
            return data;

        } else if (!fullDocument) {
            return $body;
        }
    }

    var jDialog = function (options) {
        options = options || {};
        if(typeof options === 'object'){
            for (var i in jDialog.defaults) {
                if (options[i] === undefined) options[i] = jDialog.defaults[i];
            }
        }
        var selector = null;
        try{
            selector = $(this).attr("id");
        }catch(e){}
        if(!selector){
            selector = "_"+Math.random(99,9999)+'_'+new Date().getTime();
        }
        if(jDialog.data[selector]){
            return jDialog.data[selector];
        }
        jDialog.currentID = selector;
        options['__ID__'] = selector;
        return jDialog.data[selector] = new jDialog.fn.init(options);
    };
    jDialog.fn = jDialog.prototype = {
        init : function (options) {
            this.targetElement = null;
            this.options = options;
            this.dom = null;
            this._create();
            this._listenEvent();
            this.content(options.content);
            return this;
        },
        content : function (str) {
            if(!str || str == ''){return;}
            var self = this;
            var regx = /^(http|https):\/\/(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z_!~*'()-]+\.)*([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\.[a-z]{2,6})(:[0-9]{1,4})?((\/?)|(\/[0-9a-z_!~*'().;?:@&=+$,%#-]+)+\/?)$/ig;
            var isUrl = !!regx.test(str);
            if($.type(str) == 'string' && str.substr(0,4) == 'url:'){
                isUrl = true;
                str = str.substr(4);
            }
            if(isUrl){
                self._getContent(str);
            }else{
                this._setContent(str);
            }
            var fn = this.options.complete;
            $.isFunction(fn) && fn.call(self,window);
        },
        reloadContent : function (content,title) {
            content = content != '' && content != null && content != undefined ? content : this.options.content;
            this.content(content);
            if(title){
                this.dom.find(".j-dialog-header span").html(title);
            }
        },
        close : function () {
            var self = this,
                fn = this.options.close;
            if($.isFunction(fn) && fn.call(self,window) === false){
                return self;
            }
            self.dom.animate({
                'opacity' : 0,
                'top' : -10
            },200,function(){
                $(this).remove();
            });
            this.dom_make.fadeOut(200,function () {
                $(this).remove();
            })
            var id = this.options['__ID__'];
            this.options = {};
            for (var ii in self) {
                if (self.hasOwnProperty(ii) && ii !== 'Dom') delete self[ii];
            }
            for(var d in jDialog.data){
                if(d == id){
                    delete jDialog.data[d];
                    break;
                }
            }
            $('body,html').css({
                'overflow-y' : 'auto',
            });
            jDialog.zIndex = Math.max(9,jDialog.zIndex - 2);
            try{
                if(intervalTimer){
                    clearInterval(intervalTimer);
                }
            }catch (e){}
        },
        hideClose : function () {
            this.dom.find(".j-dialog-header").find(".close").hide();
        },
        showClose : function () {
            this.dom.find(".j-dialog-header").find(".close").show();
        },
        _getContent : function (url) {
            var self = this;
            var data = {};
            data['dialog_index'] = this.options.__ID__;
            data['__'] = new Date().getTime();
            this._setContent('<div style="width: 140px;text-align: center"><i class="icon-refresh icon-spin"></i> 加载中，请稍后...</div>');
            $.ajax({
                //'async' : false, //同步请求
                'data' : data,
                'type' : 'GET',
                'url' : url,
                'error':function(XMLHttpRequest, textStatus, errorThrown){
                    self.close();
                    if(textStatus == 'abort'){return false;}
                    alert(textStatus+errorThrown);

                },
                'success' : function(result){
                    if(typeof result == 'object'){
                        self.close();
                        if(result.code == 0){
                            Tips.error(result.msg);
                        }else{
                            Tips.success(result.msg);
                        }
                    }else{
                        var content = extractContainer(result,self.options.fragment);
                        self._setContent(content);
                    }
                }
            });
        },
        _setContent : function (content) {
            this.dom.find(".j-dialog-content").html('');
            if($.type(content) == 'object'){
                this.targetElement = content;
                content.show().appendTo(this.dom.find(".j-dialog-content"));
            }else {
                this.dom.find(".j-dialog-content").html(content);
            }
            this.dom.find(".j-dialog-content").find(".auto-submit-form").each(function () {
                $(this).submitForm();
            });
            var pp = this.dom.find(".j-dialog-content .panel");
            var pd = this.dom.find(".j-dialog-content .panel-body");
            var width = this.dom.find(".j-dialog-content").outerWidth(true);
            var wWidth = $(window).width();
            var height = this.dom.find(".j-dialog-content").outerHeight(true);
            var hHeight = $(window).height();
            if(width > wWidth){
                var tw = pd.width();
                pd.attr("data-width-value",tw).width(wWidth - 100);
            }
            if(height > hHeight - 100){
                pd.attr("data-height-value",height).css({
                    'height' : hHeight - 200 - (pp.outerHeight(true) - pd.outerHeight(true)),
                    'overflow-y' : 'scroll',
                    'overflow-x' : 'hidden'
                });
            }else{
                pd.css({
                    'height' : 'auto'
                });
            }
            $('[data-toggle="datetime"]').datetime({});
            this._position();
            this._setBtn();
        },
        _position : function () {
            var offset = this.dom.offset();
            var width = this.dom.outerWidth(true);
            var height = this.dom.outerHeight(true);
            var wHeight = $(window).height();
            var windowWidth = $(window).width();
            var css = {'left' : (windowWidth / 2) - (width / 2)};
            if(height + offset.top > wHeight - 100){
                css['top'] = Math.max(1,wHeight - 100 - height - offset.top);
            }
            this.dom.stop().animate(css,200);
        },
        _setBtn : function(){
            var self = this;
            var btnWrap = self.dom.find(".j-dialog-footer");
            var dialogHandle = self.dom.find(".dialog-handle");
            var btns = dialogHandle.find("button");
            btnWrap.html('');
            if(btns.length > 0) {
                var temp = btns.clone();
                temp.appendTo(btnWrap).attr("data-dialog-index", this.options.__ID__).each(function (index) {
                    $(this).click(function () {
                        btns.eq(index).trigger('click');
                    });
                });
                dialogHandle.hide();
                btnWrap.show();
            }else{
                btnWrap.hide();
            }

        },
        _listenEvent : function () {
            var self = this;
            $(window).resize(function () {
                try {
                    var pb = self.dom.find(".j-dialog-content .panel-body");
                    var tw = pb.attr("data-width-value");
                    var th = pb.attr("data-height-value");
                    var wWidth = $(window).width();
                    var wHeight = $(window).height();
                    if(!th){
                        th = pb.outerHeight(true);
                        pb.attr("data-height-value",th);
                    }
                    if (tw) {
                        if (tw < wWidth) {
                            self.dom.find(".j-dialog-content .panel-body").width(tw);
                        } else {
                            self.dom.find(".j-dialog-content .panel-body").width(wWidth - 100);
                        }
                    }
                    if (th) {
                        if (th < wHeight - 200) {
                            self.dom.find(".j-dialog-content .panel-body").height(th);
                        } else {
                            self.dom.find(".j-dialog-content .panel-body").height(wHeight - 200).css({
                                'overflow-y': 'auto',
                                'overflow-x': 'hidden'
                            });
                        }
                    }
                }catch (e){}
                self._position();
            });

        },
        _create : function () {
            var self = this;
            var dom = $(jDialog.template);
            var zIndex = jDialog.zIndex;
            dom.find(".j-dialog-header span").html(this.options.title);
            dom.css("z-index",zIndex+1);
            dom.appendTo($('body'));
            dom.find(".j-dialog-header .close").one("click",function () {
                self.close();
            });
            this.dom = dom;
            this.dom.attr("id",this.options.__ID__);
            var content = this.dom.find(".j-dialog-content");
            var ipt = $('<input type="hidden" name="j-dialog-index" value="'+this.options.__ID__+'" />');
            ipt.appendTo(this.dom);
            this.dom_make = $('<div class="j-dialog-make"></div>');
            this.dom_make.css("z-index",zIndex);
            this.dom_make.appendTo($('body'));
            $('body,html').css({
                'overflow-y' : 'hidden',
            });
            jDialog.zIndex = jDialog.zIndex + 2;
        }

    };
    jDialog.fn.init.prototype = jDialog.fn;
    $.fn.jDialog = function () {
        var config = arguments;
        var selector = $(this).attr("id");
        if (jDialog.data[selector]) {
            var method = jDialog.data[selector];
            var c = config[0];
            if(c == 'close'){
                method.close();
            }
        }else {
            this['bind']('click', function () {
                jDialog.apply(this, config);
                return false;
            });
        }
        return this;
    };
    jDialog.zIndex = 10;
    jDialog.data = [];
    jDialog.template = '<div class="j-dialog">' +
        '<div class="j-dialog-header"><span></span>' +
        '<a href="javascript:;" class="close pull-right">&times;</a></div>' +
        '<div class="j-dialog-content"></div>' +
        '<div class="j-dialog-footer">' +
        '<div class="pull-left"></div>' +
        '</div></div>';
    jDialog.defaults = {
        title		: '系统提示',
        content		: '', //内容(可选内容为){ text |  url };
        width		: 0,
        height		: 0,
        zindex      : 10,
        fragment    : '#pjax-container',
        init        : null, //初始化后执行函数
        close       : null, // 关闭时执行的函数
        complete    : null //内容加载完成后调用
    };
    window.jDialog = $.dialog = $.jDialog = jDialog;
}(this.jDialog || this.jQuery && (this.jDialog = jQuery), this));