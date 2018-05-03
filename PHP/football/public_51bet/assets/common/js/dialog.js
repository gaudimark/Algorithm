;(function ($, window, undefined) {
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
            if(str.substr(0,4) == 'url:'){
                isUrl = true;
                str = str.substr(4);
            }
            if(isUrl){
                self._getContent(str);
            }else{
                this._setContent(str);
            }
        },
        close : function () {
            var self = this,
                fn = self.options.close;
            if($.isFunction(fn) && fn.call(self,window) === false){
                return self;
            }
            /*self.dom.animate({
                'opacity' : 0,
                'top' : -10
            },300,function(){
                $(this).remove();
            });*/
            self.dom.remove();
            self.dom_make.remove();
            /*this.dom_make.fadeOut(300,function () {
                $(this).remove();
            })*/
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
                            msgBox.error(result.msg);
                        }else{
                            msgBox.success(result.msg);
                        }
                    }else{
                        self._setContent(result);
                        if(self.options.complete && $.isFunction(self.options.complete)){
                            self.options.complete.call(self,self);
                        }
                    }
                }
            });
        },
        _setContent : function (content) {
            this.dom.find(".j-dialog-content").html(content);
            this._position();
            this._setBtn();
        },
        _position : function () {
            this.dom.find(".j-dialog-content").height("auto");
            var width = this.dom.outerWidth(true);
            var windowWidth = $(window).width();
            var windowHeight = $(window).height();
            var css = {'left' : (windowWidth / 2) - (width / 2)};
            var domHeight = this.dom.outerHeight(true);
            if(domHeight > windowHeight - 100){
                this.dom.find(".j-dialog-content").css({
                    'overflow-y' : 'auto',
                    'height' : windowHeight - 150
                });
            }

            this.dom.stop().css(css);

        },
        position : function () {
            this._position();
        },
        _setBtn : function(){
            var self = this;
            var btnWrap = self.dom.find(".j-dialog-footer");
            var dialogHandle = self.dom.find(".dialog-handle");
            var btns = dialogHandle.find("button");
            if(btns.length > 0) {
                btns.attr("data-dialog-index", this.options.__ID__);
                dialogHandle.hide();
                var form = btns.closest('form');
                if(btns.attr("type") == 'submit' && form.length > 0){
                    btns.click(function () {
                       form.submit();
                    });
                }
                btns.appendTo(btnWrap);
                btnWrap.show();
            }else{
                btnWrap.hide();
            }

        },
        _listenEvent : function () {
            var self = this;
            $(window).resize(function () {
                self._position();
            });
            this.dom.resize(function () {
                console.log('xxx');
                self._position();
            });

        },
        _create : function () {
            var self = this;
            var dom = $(jDialog.template);
            dom.find(".j-dialog-header span").html(this.options.title);
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
            this.dom_make.appendTo($('body'));
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
    jDialog.currentID = null;
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
        init        : null, //初始化后执行函数
        close       : null, // 关闭时执行的函数
        complete    : null //内容加载完成后调用
    };
    window.jDialog = $.dialog = $.jDialog = jDialog;
}(this.jDialog || this.jQuery && (this.jDialog = jQuery), this));