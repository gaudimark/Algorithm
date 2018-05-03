$(function () {
    /*页面pjax加载*/
    $(document).on('click',"*[data-target=pjax]",function () {
        /*NProgress.start();
        $.pjax({
            'url' : $(this).attr("href")
            ,'container' : '#pjax-container'
            ,'fragment' : '#pjax-container'
            ,'timeout':8000
        });
        try{
            if(intervalTimer){
                clearInterval(intervalTimer);
            }
        }catch (e){}
        return false;*/
    }).on('pjax:complete',function () {
        $('[data-toggle="datetime"]').datetime({});
        $('[data-toggle="tooltip"]').tooltip();
        NProgress.done();
    }).on("click",".j-dialog-handle",function () {
        var self = $(this);
        var title = self.attr("title");
        var href = self.attr("href");
        var iframe = self.attr("data-iframe");
        if(!title){
            title = $(this).text();
        }
        if(!iframe) {
            new window.top.jDialog({
                'content': 'url:' + href,
                'title': title
            });
        }else{
            new jDialog({
                'content': 'url:' + href,
                'title': title
            });
        }
        return false;
    }).on('click','.ajax-delete',function () {
        $(this).delete();
        return false;
    }).on("click",".ajax-confirm",function () {
        var href = $(this).attr("href");
        var title = $(this).attr("title");
        var callback = $(this).attr("data-callback");
        Tips.warn(title,function () {
            Ajax.post(href,{},function (ret) {
                if(callback){
                    var obj = eval(''+callback+'');
                    obj(ret);
                }else {
                    if (ret.code) {
                        Tips.success("操作成功", function () {
                            location.reload()
                        });
                    } else {
                        Tips.error(ret.msg);
                    }
                }
            });
        },true);
        return false;
    }).on('click','.ajax',function () {
        Loading.show();
        var tipsC = $(this).attr("data-tips");
        Ajax.post($(this).attr("href"),{},function (ret) {
            Loading.done();
            if(ret.code == 1){
                if(tipsC && tipsC == 0 && ret.url){
                    if (ret.url) {
                        location.href = ret.url;
                    }
                }else {
                    Tips.success(ret.msg, function () {
                        if (ret.url) {
                            location.href = ret.url;
                        }
                    });
                }
            }else{
                Tips.error(ret.msg);
            }
        });
        return false;
    }).on('click',".checkall",function () {
        var self = $(this);
        var toggle = self.attr("data-toggle");
        if(self.attr("checked") == 'checked'){
            $(toggle).prop("checked",false).removeAttr('checked');
            self.removeClass('active').removeAttr('checked');
            $(".checkbox-linkage").attr('disabled','disabled');
            self.find("i").removeClass('icon-check').addClass('icon-check-empty');
        }else{
            $(toggle).prop("checked",true);
            self.addClass('active').attr("checked",'checked');
            $(".checkbox-linkage").removeAttr('disabled');
            self.find("i").addClass('icon-check').removeClass('icon-check-empty');
        }
    }).on('click',".checkbox-linkage-lists",function () {
        var self = $(this);
        var toggle = self.attr("data-toggle");
        if(self.prop("checked")){
            $(".checkbox-linkage").removeAttr('disabled');
        }else if($(".checkbox-linkage-lists:checked").length == 0){
            $(".checkbox-linkage").attr('disabled','disabled');
        }
    }).on('click','.st-select',function () {
        $(this).stSelect();
    }).on('click','.st-input',function () {
        $(this).stInput();
    });
    $(".auto-submit-form").each(function () {
       $(this).submitForm();
    });
    $('[data-toggle="datetime"]').each(function () {
        $(this).datetime();
    });
    $('[data-toggle="tooltip"]').tooltip();
    $('.dropdown-box-dropdown').dropDirection();
    /*$('.btn-opt').on("show.bs.dropdown",function (relatedTarget) {
        //console.log(relatedTarget);
        var target = $(relatedTarget.target);
        var offset = target.offset();
        var top = offset.top;
        var drop = target.find(".dropdown-menu");
        var height = drop.outerHeight();
        var dHeight = $(document).height();
        var dropMarginTop = parseInt(drop.css('margin-top'));
        var dropDefaultMarginTop =parseInt(drop.attr("data-margin-top"));
        if(!dropDefaultMarginTop){
            dropDefaultMarginTop = dropMarginTop;
            drop.attr("data-margin-top",dropMarginTop);
        }
        if(height + top > dHeight - 10){
            drop.css({
                'margin-top' : dHeight - height - top - 10 + dropDefaultMarginTop
            });
        }
    }) ;
*/
    //如果表格最后一项是空则移除最后一列
    /*$("table").each(function () {
       var tbody  = $(this).find("tbody");
        if(tbody.length > 0){
            var value = $.trim(tbody.find("tr").eq(0).find("td").last().html());
            if(value == ''){
                $(this).find("thead").find("tr").find("td,th").last().remove();
                tbody.find("tr").each(function () {
                    $(this).find("td").last().remove();
                });
            }
        }
    });*/
});




function pjax(href,caller,container,fragment) {
    container = container || '#pjax-container';
    fragment = fragment || '#pjax-container';
    NProgress.start();
    $.pjax({
        'url' : href
        ,'container' : container
        ,'fragment' : fragment
        ,'timeout':8000
        ,'callback':function () {
            if(caller && $.isFunction(caller)){
                caller();
            }
        }
    });
    return false;
}

/*页面提示框*/
var Tips = {
    success : function (message,caller,time,title) {
        title = title ? title : '成功';
        timer = time ? parseInt(time) : null;
        swal({
            'title' : title
            ,'text' : message
            ,'timer' : timer
            ,'html' : true
            ,'showCancelButton': false
            ,'showLoaderOnConfirm': true
            ,'type' : 'success'
        },function () {
            if(caller && $.isFunction(caller)){
                caller();
            }
        });

    },
    error : function (message,caller,time,title) {
        title = title ? title : '失败';
        timer = time ? parseInt(time) : null;
        swal({
            'title' : title
            ,'text' : message
            ,'timer' : timer
            ,'html' : true
            ,'showCancelButton': false
            ,'showLoaderOnConfirm': true
            ,'type' : 'error'
        },function () {
            if(caller && $.isFunction(caller)){
                caller();
            }
            swal.close();
        });

    },
    warn : function (message,title,okCaller,cancelCaller) {
        title = title ? title : '你确定？';
        if($.isFunction(title)){
            okCaller = title;
            title = '你确定？';
        }
        swal({
            'title' : title
            ,'text' : message
            ,'html' : true
            ,'type' : 'warning'
            ,'showCancelButton': true
            ,'closeOnConfirm': true
            ,'showLoaderOnConfirm': true
            ,'confirmButtonText': "确定"
            ,'cancelButtonText': "取消"
        },function (isConfirm) {
            if (isConfirm) {
                swal.close();
                if(okCaller && $.isFunction(okCaller)){
                    okCaller();
                }
            }else{
                if(cancelCaller && $.isFunction(cancelCaller)){
                    cancelCaller();
                }
                swal.close();
            }
        });

    },
};
/*页面加载提示效果*/
var Loading = {
    _dom : null,
    show : function () {
        this._dom = $('<div class="loading"><img src="/assets/common/images/loading-0.gif" /><div class="loading-mark"></div></div>');
        this._dom.appendTo($('body'));
    },
    done : function () {
        this._dom.remove();
    }
};
/*Ajax操作封装*/
var Ajax = {
    get : function (url,data,success,error,errorAlert) {
        data = data ? data : {};
        data._ajax = 1;
        $.ajax({
            'type' : 'get'
            ,'url'   : url
            ,'data'  : data
            ,'dataType'  : 'JSON'
            ,'error' : function (xh,ts,et) {
                if (errorAlert == undefined || errorAlert !== false){
                    if (error && $.isFunction(error)) {
                        error(xh, ts, et)
                    } else {}
                }
            }
            ,'success' : function (ret) {
                if(success && $.isFunction(success)){
                    success(ret);
                }else{
                    if(ret.code == 1){
                        Tips.success(ret.msg,function () {
                            if(ret.url){
                                location.href = ret.url;
                            }
                        })
                    }else{
                        Tips.error(ret.msg);
                    }
                }
            }
        });
    },
    post : function (url,data,success,error) {
        data = data ? data : {};
        data._ajax = 1;
        $.ajax({
            'type' : 'post'
            ,'url'   : url
            ,'data'  : data
            ,'dataType'  : 'JSON'
            ,'error' : function (xh,ts,et) {
                if(error && $.isFunction(error)){
                    error(xh,ts,et)
                }else{}
            }
            ,'success' : function (ret) {
                if(success && $.isFunction(success)){
                    success(ret);
                }else{
                    if(ret.code == 1){
                        Tips.success(ret.msg,function () {
                            if(ret.url){
                                location.href = ret.url;
                            }
                        })
                    }else{
                        Tips.error(ret.msg);
                    }
                }
            }
        });
    },
    jsonp : function (url,data,success,error) {
        data = data ? data : {};
        data._ajax = 1;
        if(url.indexOf('?') == -1){
            url += '?callback=?';
        }else{
            url += '&callback=?';
        }
        $.ajax({
            'type' : 'post'
            ,'url'   : url
            ,'data'  : data
            ,'dataType'  : 'jsonp'
            ,'error' : function (xh,ts,et) {
                if(error && $.isFunction(error)){
                    error(xh,ts,et)
                }else{}
            }
            ,'success' : function (ret) {
                if(success && $.isFunction(success)){
                    success(ret);
                }else{
                    if(ret.code == 1){
                        Tips.success(ret.msg,function () {
                            if(ret.url){
                                location.href = ret.url;
                            }
                        })
                    }else{
                        Tips.error(ret.msg);
                    }
                }
            }
        });
    }
};

function parseHTML(html) {
    return $.parseHTML(html, document, true)
}
function extractContainer(data,fragment) {
    var fullDocument = /<html/i.test(data);
    if(fullDocument){
        data = $(parseHTML(data.match(/<body[^>]*>([\s\S.]*)<\/body>/i)[0],document,true));
        return data.find(fragment);
    }else{
        return $(data).find(fragment);
    }
}
(function ($) {
    $.fn.submitForm = function (before,after) {
        var form = $(this);
        if(form.length == 0){return false;}
        form.submit(function () {
            var dataPost = form.attr("data-post");
            //if(dataPost == '1'){return false;}
            //form.find(":submit").attr('disabled','disabled');
            form.attr("data-post",'1');
            //var parsley = null;
            if(before && $.isFunction(before)){
                if(!before(form)){
                    form.find(":submit").removeAttr('disabled');
                    form.removeAttr('data-post');
                    return false;
                }
            }else{
                if(formParsley(form).validate() == false){
                    form.find(":submit").removeAttr('disabled');
                    form.removeAttr('data-post');
                    return false;
                }
            }
            form.find("input,textarea,select").attr("readonly","readonly");
            var mgB = Loading.show();
            $.ajax({
                type : 'post',
                url : form.attr("action"),
                data : form.serialize(),
                dataType : 'JSON',
                error : function (xmlHttpRequest,textStatus,errorThrown) {
                    Loading.done();
                    form.find("input,textarea,select").removeAttr("readonly");
                    form.find(":submit").removeAttr('disabled');
                    form.removeAttr('data-post');
                    alert(errorThrown+"(+"+xmlHttpRequest.status+"+)\n"+textStatus);
                },
                success:function (ret) {
                    Loading.done();
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
                        Tips.success(ret.msg,function () {
                            if(ret.url){
                                location.href = ret.url;
                            }
                        });
                    }else{
                        Tips.error(ret.msg);
                    }
                }
            });
            return false;
        });
    };

    $.fn.delete = function (data) {
        var self = $(this);
        var dis = self.attr("disabled");
        if(dis){return false;}
        Tips.warn("你确定要执行删除操作吗？<br/>此操作后数据无法恢复!","删除确认",function () {
            //Loading.show();
            data = {};
            data.value = self.attr("data-value");
            Ajax.post(self.attr("href"),data,function (ret) {
                if(ret.code == 1){
                    Tips.success(ret.msg,function () {
                        if(ret.url){
                            location.href = ret.url;
                        }
                    });
                }else{
                    Tips.error(ret.msg);
                }

            },function (xmlHttpRequest,textStatus,errorThrown) {
                alert(errorThrown+"("+xmlHttpRequest.status+")\n"+textStatus);
            });
        });
    }

    $.fn.datetime = function(options) {
        var self = $(this);
        var startDate = moment();
        var timePicker = self.attr("data-timepicker") ? true : false;
        var minDate = self.attr("data-min-date");
        var maxDate = self.attr("data-max-date");
        var format = "YYYY-MM-DD";
        if(timePicker){format = "YYYY-MM-DD HH:mm:ss";}
        if(!minDate){minDate = '2000-01-01';}
        if(self.val()){startDate = self.val();}
        var dateRex = /^(\d{4}\-\d{1,2}\-\d{1,2}(\s\d{1,2}:\d{1,2}:\d{1,2})?)$/;
        var options = $.extend({
            startDate : startDate,
            minDate : minDate,
            maxDate : maxDate,
            timePicker : timePicker,
            timePickerSeconds : true,
            timePicker24Hour : true,
            timePickerIncrement: 1,
            showWeekNumbers : true,
            singleDatePicker : true,
            autoUpdateInput : false,
            showDropdowns : true,
            locale :{
                format : format,
                'applyLabel' : '确定',
                'cancelLabel' : '取消',
                'clearLabel' : '清除',
                'fromLabel' : '开始时间',
                'toLabel' : '结束时间',
                'customRangeLabel' : '自定义',
                'daysOfWeek' : ['日','一','二','三','四','五','六'],
                'monthNames' : ['一月','二月','三月','四月','五月','六月','七月','八月','九月','十月','十一月','十二月'],
                firstDay : 1
            }
        },options);
        self.daterangepicker(options,function (start,end,label) {
        });
        self.on("apply.daterangepicker",function (ev,picker) {
            var thisDate = picker.startDate.format(format);
            $(this).val(thisDate);
            var endInput = $($(this).attr("data-end-date"));
            if(endInput.length > 0){
                endInput.data("daterangepicker").minDate = picker.startDate;
                var endDate = endInput.val();
                var d1 = new Date(endDate.replace(/\-/g,"\/"));
                var d2 = new Date(thisDate.replace(/\-/g,"\/"));
                if(d1 < d2){
                    endInput.val(thisDate);
                    endInput.data("daterangepicker").setStartDate(thisDate);
                }
            }
            var startInput = $($(this).attr("data-start-date"));
            if(startInput.length > 0){
                startInput.data("daterangepicker").maxDate = picker.startDate;
                var startDate = startInput.val();
                var d1 = new Date(startDate.replace(/\-/g,"\/"));
                var d2 = new Date(thisDate.replace(/\-/g,"\/"));
                if(d1 > d2){
                    startInput.val(thisDate);
                    startInput.data("daterangepicker").setStartDate(thisDate);
                }
            }
        })
    }

    $.fn.dropDirection = function () {
        $(this).each(function () {
            var documentHeight = $(document).height();
            var self = $(this);
            var offset = self.offset();
            if((offset.top + self.parent().outerHeight(true) + self.find('.dropdown-menu').outerHeight(true) + 100) >= documentHeight ){
                self.removeClass('dropdown').addClass('dropup');
            }

            if(self.hasClass('dropup')){
                if(offset.top < self.find('.dropdown-menu').outerHeight(true)){
                    self.find('.dropdown-menu').css({
                        'bottom' : 'auto',
                        'top' : -(offset.top)
                    })
                }
            }

        })

    }

    $.fn.stSelect = function () {
        var self = $(this);
        var data = self.attr("data-enum");
        if(!data){
            alert('无效数据');
            return false;
        }
        var tempData = [];
        data = data.split(",");
        var id = "_"+(new Date()).getTime()+'_'+(Math.random());
        var select = '<select class="form-control" id="'+id+'">';
        for(var k in data){
            var d = data[k].split(':');
            tempData[d[0]] = d[1];
            select += '<option value="'+d[0]+'" '+(self.attr("data-value") == d[0] ? 'selected' : '')+'>'+d[1]+'</option>';
        }
        select += '</select>';
        select = $(select);
        select.appendTo(self.parent());
        var selectedValue = select.val();
        self.hide();
        var _handle = function (event) {
            var eventId = $(event.target).attr("id");
            if(eventId != id){
                $(document).unbind('click',_handle);
                var val = select.val();
                console.log(selectedValue,val);
                if(selectedValue != val) {
                    Loading.show();
                    Ajax.post(self.attr("data-action"), {'value': val}, function (ret) {
                        select.remove();
                        Loading.done();
                        self.show();
                        if (ret.code == 1) {
                            self.attr("data-value", val);
                            self.text(tempData[val]).show();
                        } else {
                            Tips.error(ret.msg);
                        }
                    });
                }else{
                    select.remove();
                    self.show();
                }
            }
        };
        $(document).bind("click",_handle);

    }

    $.fn.stInput = function () {
        var self = $(this);
        var id = "_"+(new Date()).getTime()+'_'+(Math.random());
        var input = '<input type="text" class="form-control" id="'+id+'" value="'+self.text()+'" />';
        input = $(input);
        input.appendTo(self.parent());
        var selectedValue = self.text();
        self.hide();
        var _handle = function (event) {
            var eventId = $(event.target).attr("id");
            if(eventId != id){
                $(document).unbind('click',_handle);
                var val = input.val();
                if(selectedValue != val) {
                    Loading.show();
                    Ajax.post(self.attr("data-action"), {'value': val}, function (ret) {
                        input.remove();
                        Loading.done();
                        self.show();
                        if (ret.code == 1) {
                            self.text(val).show();
                        } else {
                            Tips.error(ret.msg);
                        }
                    });
                }else{
                    input.remove();
                    self.show();
                }
            }
        };
        $(document).bind("click",_handle);

    }
})(jQuery);

function formParsley(form,wrapper) {
    wrapper = wrapper ? wrapper : '.form-group';
    return form.parsley({
        inputWrapper : wrapper,
        showErrors : false,
        errorsWrapper : ' <div class="help-block help-block-error"></div>',
        errorTemplate : '<span></span>',
        successClass : '',
        errorClass : 'parsley-error'
    }).on("field:success",function () {
        var ele = this.$element;
        ele.closest(wrapper).removeClass('has-error').addClass('has-success');
        ele.closest(wrapper).find(".help-block-error").slideUp(100);
    }).on("field:error",function () {
        var ele = this.$element;
        ele.closest(wrapper).addClass("has-error").removeClass('has-success');
        if(this.getErrorsMessages() == '' || !this.getErrorsMessages()){
            ele.closest(wrapper).find(".help-block-error").remove();
        }
        return ele.closest(wrapper);

    });
}

function formatSize(size){
    var a = ["B", "KB", "MB", "GB", "TB", "PB"];
    var pos = 0;
    while (size >= 1024) {
        size /= 1024;
        pos++;
    }
    var num = new Number(size);
    return num.toFixed(2)+" "+a[pos];
}
Date.prototype.Format = function (fmt) { //author: meizz
    var o = {
        "M+": this.getMonth() + 1, //月份
        "d+": this.getDate(), //日
        "h+": this.getHours(), //小时
        "m+": this.getMinutes(), //分
        "s+": this.getSeconds(), //秒
        "q+": Math.floor((this.getMonth() + 3) / 3), //季度
        "S": this.getMilliseconds() //毫秒
    };
    if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    for (var k in o)
        if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
    return fmt;
};
function number_format(number, decimals, dec_point, thousands_sep,roundtag) {
    /*
    * 参数说明：
    * number：要格式化的数字
    * decimals：保留几位小数
    * dec_point：小数点符号
    * thousands_sep：千分位符号
    * roundtag:舍入参数，默认 "ceil" 向上取,"floor"向下取,"round" 四舍五入
    * */
    number = (number + '').replace(/[^0-9+-Ee.]/g, '');
    roundtag = roundtag || "ceil"; //"ceil","floor","round"
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function (n, prec) {

            var k = Math.pow(10, prec);
            console.log();

            return '' + parseFloat(Math[roundtag](parseFloat((n * k).toFixed(prec*2))).toFixed(prec*2)) / k;
        };
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    var re = /(-?\d+)(\d{3})/;
    while (re.test(s[0])) {
        s[0] = s[0].replace(re, "$1" + sep + "$2");
    }

    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}