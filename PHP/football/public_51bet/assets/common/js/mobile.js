$(function () {
    $("form.auto-submit-form").submitForm();
    $(document).on("click","*[data-target=pjax]",function () {
        NProgress.start();
        $.pjax({
            'url' : $(this).attr("href"),
            'container' : '#wrapper',
            'fragment' : '#wrapper',
        })
        return false;
    }).on('pjax:complete',function () {
        NProgress.done();
        myScroll.refresh();
    });
});
(function ($) {
    $.fn.submitForm = function (before,after) {
        var form = $(this);
        if(form.length == 0){return false;}
        form.submit(function () {
            form.blur();
            var dataPost = form.attr("data-post");
            if(dataPost == '1'){return false;}
            form.find(":submit").attr('disabled','disabled');
            form.attr("data-post",'1');
            var parsley = null;
            if(before && $.isFunction(before)){
                if(!before(form)){
                    form.find("input,textarea,select").bind("click focus change input",function () {
                        form.find(":submit").removeAttr('disabled');
                        form.removeAttr('data-post');
                    });
                    return false;
                }
            }
            form.find("input,textarea,select").attr("readonly","readonly");
            var mgB = window.top.msgBox.loading("提交中,请稍后...",true);
            $.ajax({
                type : 'post',
                url : form.attr("action"),
                data : form.serialize(),
                dataType : 'JSON',
                error : function (xmlHttpRequest,textStatus,errorThrown) {
                    mgB.close(false);
                    form.find("input,textarea,select").removeAttr("readonly");
                    form.find(":submit").removeAttr('disabled');
                    form.removeAttr('data-post');
                    alert(errorThrown+"(+"+xmlHttpRequest.status+"+)\n"+textStatus);
                },
                success:function (ret) {
                	mgB.close(false);
                    form.find("input,textarea,select").removeAttr("readonly");
                    form.find(":submit").removeAttr('disabled');
                    form.removeAttr('data-post');
                    var dialogIndex = null;
                    try{
                        var dialog = form.closest('.j-dialog');
                        if(dialog.length > 0) {
                            dialogIndex = dialog.find("[name=j-dialog-index]").val();
                        }
                    }catch(e){}
                    if(after && $.isFunction(after)){
                        after(ret,form,dialogIndex,parsley);
                    }else if(ret.code == 1){
                        try{dialogIndex && jDialog.data[dialogIndex].close();}catch (e){}
                        window.top.msgBox.success(ret.msg);
                        window.top.reloadIframe(ret.url);
                    }else{
                        window.top.msgBox.error(ret.msg);
                    }
                }
            });
            return false;
        });
    }

})(jQuery);

var msgBox = {
    _tpl : null,
    _tplMake : null,
    _template : '<div class="artBox"><span></span></div>',
    _templatemake : '<div class="artBoxMake"></div>',
    _show : function (obj) {
        this._tpl = obj;
        obj.appendTo($("body"));
        var windowHeight = $(window).height();
        var windowWidth = $(window).width();
        var top = windowHeight / 4;
        var width = obj.outerWidth(true);
        obj.css({
            'top':top - 100,
            'margin-left' : (windowWidth / 2 - width/2)
        });
        obj.animate({
            'opacity' : 'show',
            'top' :top
        },300);
    },
    _close : function (obj,caller,time) {
        var self = this;
        time = parseInt(time);
        if(!time && time != 0){
            time = 3;
        }
        if(!obj){obj = this._tpl;}
        setTimeout(function () {
            var offset = obj.offset();
            obj.animate({
                'opacity' : 0,
                'top' : offset.top-100
            },300,function () {
                if(caller && $.isFunction(caller)){
                    caller(self);
                }
                obj.remove();
                try{self._tplMake.remove();}catch (e){}
            });
        },time * 1000);
    },
    close : function (animate) {
        this._tpl.remove();
        try{this._tplMake.remove();}catch (e){}
        if(animate === false){
            this._tpl.remove();
            try{this._tplMake.remove();}catch (e){}
        }else{
            this._close(null,null,0);
        }
    },
    success : function (cnt,caller,time) {
        //time = time || 2;
        if(time === false){
            time = 0;
        }else{
            time = time || 2;
        }
        var temp = $(this._template);
        temp.addClass('successBox');
        //temp.find("i").addClass("fa-check-circle");
        if(!cnt || $.trim(cnt) == ''){
            temp.find("span").remove();
        }else{
            temp.find("span").html(cnt);
        }
        this._close(temp,caller,time);
        this._show(temp);
        return this;
    },
    error : function (cnt,caller,time) {
        time = time || 3;
        cnt = cnt || '操作失败';
        var temp = $(this._template);
        temp.addClass('errorBox');
        //temp.find("i").addClass("fa-times-circle");
        temp.find("span").html(cnt);
        this._close(temp,caller,time);
        this._show(temp);
        return this;
    },
    confirm : function (cnt,caller,make,textrea,placeholder) {
        var self = this;
        cnt = cnt || '你确定要执行删除操作吗？<br/>此操作后数据不可恢复！<Br/>';
        var temp = $(this._template);
        temp.addClass('confirmBox');
        //temp.find("i").addClass("fa-question-circle");
        temp.find("span").html(cnt);
        if(textrea){
            placeholder = placeholder ? placeholder : '';
            $('<textarea id="confirm-textarea" class="form-control mb10 mt10" placeholder="'+placeholder+'"></textarea>').appendTo(temp.find("span"));
        }
        var btn = $('<div class="box-footer">' +
            '<button type="button" class="btn btn-danger btn-sm btn-enter">确定</button>&nbsp;&nbsp;' +
            '<button type="button" class="btn btn-default btn-sm btn-cancel">取消</button></div>');
        btn.appendTo(temp);
        this._show(temp);
        if(make){this._make();}
        temp.find(".btn-cancel").click(function () {
            self._close(temp,null,0.1);
        });
        temp.find(".btn-enter").click(function(){
            self._close(temp,caller,0.1);
        });
        return this;
    },
    loading : function (cnt,make) {
        if(cnt == null || cnt == undefined){
            cnt = '数据提交中...';
        }
        var temp = $(this._template);
        temp.addClass('loadingBox');
        //temp.find("i").addClass("icon-spinner icon-spin icon-2x").removeClass('icon-4x');
        if(cnt != null) {
            temp.find("span").html(cnt);
        }else{
            temp.find("span").hide();
        }
        this._show(temp);
        if(make){this._make();}
        return this;
    },
    _make : function () {
        this._tplMake = $(this._templatemake);
        this._tplMake.appendTo($("body"));
    }
};
var Ajax = {
    get : function (url,data,okCaller,errCaller) {
        if(data && typeof data == 'object'){
            data['ajax'] = 1;
        }
        $.ajax({
            type : 'get',
            url : url,
            data : data,
            dataType : 'JSON',
            error : function (xmlHttpRequest,textStatus,errorThrown) {
                if(errCaller && $.isFunction(errCaller)){
                    errCaller(xmlHttpRequest,textStatus,errorThrown)
                }else{
                    $(".artBox").remove();
                    alert(errorThrown+"(+"+xmlHttpRequest.status+"+)\n"+textStatus);
                }
            },
            success:function (ret) {
                if(okCaller && $.isFunction(okCaller)){
                    okCaller(ret)
                }
            }
        });
    },
    post : function (url,data,okCaller,errCaller) {
        if(data && typeof data == 'object'){
            data['ajax'] = 1;
        }
        $.ajax({
            type : 'post',
            url : url,
            data : data,
            dataType : 'JSON',
            error : function (xmlHttpRequest,textStatus,errorThrown) {
                if(errCaller && $.isFunction(errCaller)){
                    errCaller(xmlHttpRequest,textStatus,errorThrown)
                }else{
                    $(".artBox").remove();
                    alert(errorThrown+"(+"+xmlHttpRequest.status+"+)\n"+textStatus);
                }
            },
            success:function (ret) {
                if(okCaller && $.isFunction(okCaller)){
                    okCaller(ret)
                }
            }
        });
    },
    jsonp : function (url,data,okCaller,errCaller) {
        data['ajax'] = 1;
        $.ajax({
            type: 'get',
            url: url,
            data: data,
            dataType: 'jsonp',
            error: function (xmlHttpRequest, textStatus, errorThrown) {
                if (errCaller && $.isFunction(errCaller)) {
                    errCaller(xmlHttpRequest, textStatus, errorThrown)
                } else {
                    $(".artBox").remove();
                    alert(errorThrown + "(+" + xmlHttpRequest.status + "+)\n" + textStatus);
                }
            },
            success: function (ret) {
                if (okCaller && $.isFunction(okCaller)) {
                    okCaller(ret)
                }
            }
        });
    }
};

function pjax(url,succCaller,container,fragment) {
    NProgress.start();
    container = container || '#wrapper';
    fragment = fragment || '#wrapper';
    succCaller = succCaller && $.isFunction(succCaller) ? succCaller : function () {}
    $.pjax({
        'url' : url,
        'container' : container,
        'fragment' : fragment,
        'success' : succCaller
    });
}

function retJumpByCode(ret) {
    try{
        if(ret.data.code == -9999 || ret.data.code == -9998){
            window.location.href = ret.url+'?ref='+encodeURIComponent(document.URL);
            return false;
        }else if(ret.url.substr(0,4) == 'http'){
            window.location.href = ret.url;
        }
    }catch(e){
    }
    return true;
}

function arenaPrivate(url,id,callback) {
    Ajax.jsonp(url,{'id' : id},function (ret) {
        if(ret.code == 1){
            if(callback && $.isFunction(callback)){
                callback(ret);
            }
        }else{
            if(ret.data && ret.data.code == -9997){
                msgBox.error("该擂台设置公好友可投注");
                return false;
            }else if(ret.data && ret.data.code == -9996){
                inviteCode(url,id,callback);
            }else if(ret.data && ret.data.code){
                retJumpByCode(ret);
            }else{
                msgBox.error(ret.msg);
            }
        }
    });
}

function inviteCode(url,id,callback) {
    var html = $('<div class="popup"><div class="popupbg close"></div>' +
        '<div class="popup-cont"><div class="yqminput">输入邀请码：' +
        '<input type="text" value="" name="code" placeholder="请输入邀请码" /></div>' +
        '<div class="btnbox"><button class="popup-btn">确定</button></div>' +
        '<a href="javascript:;" class="close close-icon"></a></div></div>');
    html.appendTo($('body')).find(".close").click(function () {
        html.remove();
    });
    html.find(".popup-btn").click(function () {
        var code = html.find("input[name=code]").val();
        if(!code){
            msgBox.error("请输入邀请码");
            return false;
        }
        Ajax.jsonp(url,{'id' : id,'code' : code},function (ret) {
            try{
                if(ret.code != 1){
                    msgBox.error(ret.msg);
                }else{
                    msgBox.success("验证成功");
                    html.remove();
                    if(callback && $.isFunction(callback)){
                        callback(ret);
                    }
                }
            }catch (e){

            }
        });
    });
}

function getArenaUserInfo(arenaId) {
    Ajax.jsonp(DOMAIN +'arena/getLeizhu',{'arena_id':arenaId,'ajax' : 1},function (ret) {
        if(ret.code != 1){
            msgBox.error(ret.msg);
        }else{
            var cnt = $(ret.data.content);
            cnt.appendTo($("body")).show();
            cnt.find(".close").click(function () {
               cnt.remove();
            });
        }
    })
}


function ForecastIncome(arena_id,value,odds,rule,agentMark) {
    $.getJSON(DOMAIN +'arena/forecastIncome?callback=?',{'arena_id':arena_id,'value':value,'odds' : odds,'rule':rule,'ajax':1,'agent_mark':agentMark},function (ret) {
        $("#betting-forecast-income").text(ret.data.income);
        $("#sys_player_brok").text(ret.data.brok);
    });
}

