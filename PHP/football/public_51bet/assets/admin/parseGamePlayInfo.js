/*
解析游戏投注、开奖数据
by caojiayin 2015-09-25
*/
;(function ($, w, undefined) {
    var parseGamePlayinfo = function(GameConf){
        return new parseGamePlayinfo.fn._init(GameConf);
    };
    parseGamePlayinfo.fn = parseGamePlayinfo.prototype = {
        _init : function(GameConf){
            var self = this;
            self.GameConf = GameConf;
            self.template = self._template();
            self.YZ = [];
            self.KJ = [];
            return self;
        },
        show : function(kindID,gameKind,dataChip,dataHit,Obj,black,userId,dataRecordTime){
            this.dataRecordTime = dataRecordTime;
            this.YZ = [];
            this.KJ = [];
            this.Obj = Obj;
            this.offset = Obj.offset();
            if(dataChip.indexOf('庄家') != -1){
                this.YZ.push(['<b style="color:#f00">庄家</b>','']);
                dataChip = dataChip.replace('庄家：','');
                dataChip = dataChip.replace('庄家','');
            }
            if(gameKind == 401){//飞禽走兽
                switch(gameKind){
                    case '401':
                        this._parse7(7,dataChip,dataHit);
                        break;
                    default:
                        this._parse(dataChip,dataHit);
                        break;
                }
            }else if(gameKind == 103 || gameKind == 102 || gameKind == 101 || gameKind == 403){//百人牛牛，炸金花
                this._parsePK(gameKind,dataChip,dataHit,userId);
            }else if(gameKind == 404 || gameKind == 402){//奔驰宝马
                this._parse404(gameKind,dataChip,dataHit,userId);
            }else{
                this._parse(dataChip,dataHit);
            }
            this._setTemplate(black);
        },
        hide : function(){
            this.template.hide().remove();
        },
        //其它
        _parse : function(dataChip,dataHit){
            this.YZ.push([dataChip,'']);
            this.KJ.push([dataHit,'']);
        },
        _parse101 : function(gameKind,dataChip,dataHit,userId){
            this.YZ.push([dataChip,'']);
            this.KJ.push([dataHit,'']);
        },
        
        //飞禽走兽
        _parse7 : function(kindID,dataChip,dataHit){
            var gameConf = this.GameConf[kindID];
            //投注
            //console.log(dataChip);
            if(dataChip){
                dataChip = dataChip.split(";");
                for(var k in dataChip){
                    var d = dataChip[k].split(":");
                    if(d && d[1] > 0){
                        this.YZ.push([gameConf['yz'][d[0]],d[1]]);
                    }
                }
            }
            if($.trim(dataHit) == '结算'){
                this.KJ.push(['结算',this.Obj.attr("data-hitscore")]);
            }else{
                dataHit = parseInt(dataHit);
                this.KJ.push([gameConf['kj'][dataHit],'']);
            }
        },
        //水浒传
        _parse9 : function(kindID,dataChip,dataHit){
            var gameConf = this.GameConf[kindID];
            if(dataChip){
                dataChip = dataChip.split(";");
                var game = dataChip[0];
                this.YZ.push([gameConf[dataChip[0]]['game'],dataChip[1]]);
                dataHit = dataHit.trim();
                if(dataHit){
                    if(game==1){
                        dataHit = dataHit.split(" ");
                        for(var k in dataHit){
                            var d = dataHit[k].split(":");
                            if(d){
                                this.KJ.push([gameConf[game]['kj'][d[0]],d[1]]);
                            }
                        }
                    }else if(game==2){
                        dataHit = dataHit.split(" ");
                        for(var k in dataHit){
                            this.KJ.push([gameConf[game]['kj'][dataHit[k]],'']);
                        }
                    }else if(game==3){
                        dataHit = dataHit.split(":");
                        var d = dataHit[0].trim().split(" ");
                        var str = '';
                        for(var j in d){
                            str += ' '+gameConf[game]['kj'][d[j]];
                        }
                        str = str+' : '+gameConf[game]['kj'][dataHit[1]];
                        this.KJ.push([str,'']);
                    }
                }
            }
        },
        _parsePK : function (kindID,dataChip,dataHit,userId) {
            var gameConf = this.GameConf[103];
            this.YZ.push([dataChip,'']);
            dataHit = dataHit.trim();
            dataHit = dataHit.split(";");
            var data = [];
            for(var k in dataHit){
                var pk = dataHit[k].split(",");
                if(dataHit[k]) {
                    if(kindID == 102) { //炸金花
                        for (var idx in pk) {
                            if(idx == 0){
                                if(pk[0] == userId) {
                                    data.push('<font style="color: #f00">' + (pk[0] ? '【'+this._getUrl(pk[0])+'】':'')  + '</font>');
                                }else{
                                    data.push((pk[0] ? '【'+this._getUrl(pk[0])+'】':'') );
                                }
                            }else {
                                if (pk[0] == userId) {
                                    data.push('<font style="color: #f00">' + (idx > 0 ? gameConf[pk[idx]] : '') + '</font>');
                                } else {
                                    data.push(gameConf[pk[idx]]);
                                }
                            }
                        }
                    }else if(kindID == 403 || kindID == 104) { //百人牛牛
                        data.push('【'+this._getUrl(pk[5])+'】');
                        for (var idx in pk) {
                            if(idx < 5){
                                data.push(gameConf[pk[idx]]);
                            }
                        }
                    }else if(kindID == 101) { //斗地主
                        if(k < 3){
                            data.push('【'+this._getUrl(pk[1])+'】'+pk[0]);
                            for (var idx in pk) {
                                if(idx > 1){
                                    data.push(pk[idx]);
                                }
                            }
                        }else{
                            data.push('【叫分】'+pk[0]);
                        }
                    }else if(kindID == 103) { //二人牛牛
                        for (var idx in pk) {
                            if(idx  == 0){
                                if(pk[1] == userId) {
                                    data.push('<font style="color: #f00">' + (pk[1] ? '【'+this._getUrl(pk[1])+'】':'') + pk[0] + '</font>');
                                }else{
                                    data.push((pk[1] ? '【'+this._getUrl(pk[1])+'】':'') + pk[0]);
                                }
                            }else if(idx > 1){
                                data.push(gameConf[pk[idx]]);
                            }
                        }
                    }else{
                        for (var idx in pk) {
                            data.push(gameConf[pk[idx]]);
                        }
                    }
                    data.push("<br/>");
                }
            }
            this.KJ.push([data.join(" "),'']);

        },

        _parse404 : function (kindID,dataChip,dataHit,userId) {
            var gameConf = this.GameConf[kindID];
            dataChip = dataChip.trim();
            dataHit = dataHit.trim();
            dataChip = dataChip.split(";");
            var data = [];
            for(var k in dataChip){
                var pk = dataChip[k].split(":");
                if(pk[1]){
                    this.YZ.push([gameConf[pk[0]],pk[1]]);
                }
            }
            this.KJ.push([gameConf[dataHit],'']);
        },

        _setTemplate : function(black){
            var yzHtml = '';
            var kjHtml = '';
            blackmsg = black == 1 ? '（小黑屋）' : '';
            if(this.YZ.length > 0){
                for(var i = 0;i < this.YZ.length;i++){
                    yzHtml += '<span style="display: inline-table;margin: 2px 10px;"><label style="padding-right: 10px;">'+this.YZ[i][0]+'</label><em>'+this.YZ[i][1]+'</em></span>';
                }
            }
            if(this.KJ.length > 0){
                for(var i = 0;i < this.KJ.length;i++){
                    kjHtml += '<span style="display: inline-table;margin: 2px 10px;"><label style="padding-right: 10px;">'+this.KJ[i][0]+'</label><em>'+this.KJ[i][1]+'</em></span>';
                }
            }
            yzHtml = blackmsg + yzHtml;
            this.template.find(".chip").html(yzHtml);
            this.template.find(".hit").html(kjHtml);
            this.template.appendTo($("body")).hide();
            var top = this.offset.top;
            var salfTop = this.offset.top+this.template.outerHeight(true);
            if(salfTop + 50 >= $(document).height()){
                top = this.offset.top - this.template.outerHeight(true) - 15;
            }
            this.template.css({
                'top' :top+'px',
                'left' : this.offset.left+'px'
            }).show();
        },
        _template : function(){
            var html = '<div class="stat_game_record-box" style="display: none;border:1px solid #ddd;background:#fff;' +
                'padding:0;position: absolute;overflow: hidden;max-width: 400px;min-width: 150px;box-shadow: 0 0 5px 5px rgba(0,0,0,0.2);">' +
                '<p style="background: #f5f5f5;padding-left: 5px;">投注：</p>' +
                '<div class="chip"></div>' +
                '<p style="background: #f5f5f5;padding-left: 5px;">开奖：</p>' +
                '<div class="hit"></div></div>';
            return $(html);
        },
        _getUrl : function(userId){
            return '<a href="'+RecordUrl+'?timeexact=1&user_type=3&user='+userId+'&btime='+this.dataRecordTime+'&etime='+EndTime+'" target="_blank">'+userId+'</a>';
        }

    };
    parseGamePlayinfo.fn._init.prototype = parseGamePlayinfo.fn;
    w.parseGamePlayinfo = parseGamePlayinfo;
}(jQuery,window));

