<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:89:"Z:\phpStudy\PHPTutorial\WWW\apifootball\public/../application/index\view\index\index.html";i:1523761351;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
    <link rel="stylesheet" type="text/css" href="<?php echo config('site_source_domain'); ?>common/plugins/bootstrap/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo config('site_source_domain'); ?>common/plugins/font_awesome/css/font-awesome.min.css" />
    <script type="text/javascript" src="<?php echo config('site_source_domain'); ?>common/js/jquery-1.12.2.min.js"></script>
    <script type="application/javascript" src="<?php echo config('site_source_domain'); ?>common/js/jquery.json.js"></script>
</head>
<body style="padding-top: 20px;">
    <div class="container-fluid">
        <div id="client_id">
            测试接口
        </div>
        <div class="col-sm-4">
            <form class="form-horizontal" id="form">
                <div class="form-group">
                    <label class="col-sm-4 control-label">Access Token</label>
                    <div class="col-sm-8">
                        <div class="input-group">
                            <input type="text" class="form-control" name="token" id="token" value="<?php echo $access_token; ?>">
                            <span class="input-group-addon" style="padding: 5px;">
                                <button id="reset-token" type="button" class="btn btn-xs" style="margin: 0">重置</button>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label">Api接口地址</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="api" id="api">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label">获取方式</label>
                    <div class="col-sm-8">
                        <label><input type="radio" name="type" value="get" checked /> GET</label>
                        <label><input type="radio" name="type" value="post" /> POST</label>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label">
                        Api参数
                        <button type="button" class="btn btn-xs btn-success" id="btn-add-params"><i class="fa fa-plus"></i></button>
                    </label>
                    <div class="col-sm-8" id="param-list">
                        <div class="input-group" style="margin-bottom: 5px">
                            <input type="text" class="form-control inp-params-key" name="key" placeholder="key" value="" />
                            <span class="input-group-addon">:</span>
                            <input type="text" class="form-control inp-params-value" name="value" placeholder="value" value="" />
                            <div class="input-group-addon"><span class="close remove-params">&times;</span></div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label">&nbsp;</label>
                    <div class="col-sm-8">
                        <button id="btn-submit" type="button" class="btn btn-success">确定</button>
                    </div>
                </div>

            </form>
            <div id="chat_list">
            </div>
        </div>
        <div id="ret-content" class="col-sm-8" style="background: #eee;height: 700px;border:1px solid #ddd;overflow: scroll">
            ...
        </div>
    </div>
</body>
</html>
<script>
    var paramTpl = '<div class="input-group" style="margin-bottom: 5px">' +
            '<input type="text" class="form-control inp-params-key" name="key" placeholder="key" value="" />' +
            '<span class="input-group-addon">:</span>' +
            '<input type="text" class="form-control inp-params-value" name="key" placeholder="value" value="" />' +
            '<div class="input-group-addon"><span class="close remove-params">&times;</span></div>' +
            '</div>';
    $(function () {
        $("#btn-add-params").click(function () {
            $(paramTpl).appendTo($("#param-list"));
        });
        $("#reset-token").click(function () {
            $.get("<?php echo url('index/resetToken'); ?>",{},function (ret) {
                $("#token").val(ret.data.access_token);
            });
        });

        $(document).on('click','.remove-params',function () {
            var rparams = $(".remove-params");
            if(rparams.length == 1){return false;}
            $(this).closest(".input-group").remove();
            return false;
        }).on('click','#btn-submit',function () {
            $("#ret-content").html("加载中...");
            _postData();
        });
        getForum();
    });
    function _postData() {
        var form = $("#form");
        var api = form.find("#api").val();
        var token = form.find("#token").val();
        var type = form.find("input[name=type]:checked").val();
        var url = '//api.pad.com/'+api;
        var data = getInputParamsData();
        data['token'] = token;
        $.ajax({
            'type' : type,
            'data' : data,
            'url' :url,
            dataType : 'JSON',
            error : function (xmlHttpRequest,textStatus,errorThrown) {
                alert(errorThrown+"(+"+xmlHttpRequest.status+"+)\n"+textStatus);
            },
            success:function (ret) {
                var data = JSON.stringify(ret);
                console.log(data);
                $("#ret-content").html('<div class="json-string">'+new JSONFormat(data,4).toString()+'</div>');
            }
        });
    }


    function test() {
        var targetList = ['home_0','home_1','home_2','home_3','home_4','home_5'];
        var url = '//api.pad.com/index/arena/bet';
        for(var i = 0;i < 5;i++){
            var key = parseInt(Math.random() * 6);
            var target = targetList[key];
            $.ajax({
                'type' : 'POST',
                'data' : {
                    'token' : 'd6d5c8497eaec6883eb9405bca074050',
                    'arena_id' : '52',
                    'item' : '',
                    'money' : 100,
                    'target' : target,
                },
                'url' :url,
                dataType : 'JSON',
                error : function (xmlHttpRequest,textStatus,errorThrown) {
                    alert(errorThrown+"(+"+xmlHttpRequest.status+"+)\n"+textStatus);
                },
                success:function (ret) {
                    var data = JSON.stringify(ret);
                    console.log(data);
                    $("#ret-content").html('<div class="json-string">'+new JSONFormat(data,4).toString()+'</div>');
                }
            });
        }
    }


    function getInputParamsData() {
        var data = {};
        var key = $(".inp-params-key");
        var value = $(".inp-params-value");
        for(var i =0 ;i < key.length;i++){
            var $_k = key.eq(i).val();
            var $_v = value.eq(i).val();
            data[$_k] = $_v;
        }
        return data;
    }

    function getForum() {
        $.get('<?php echo url('index/common/forum_list'); ?>',{'token':'<?php echo $access_token; ?>'},function (ret) {
            var data = ret.data;
            var html = '';
            for(var k in data){
                html += '<a class="btn btn-success" href="<?php echo url('/index/index/chat'); ?>?token=<?php echo $access_token; ?>&group_id='+data[k]['id']+'">'+data[k]['name']+'</a>';
            }
            $("#chat_list").html(html);
        });
    }
</script>