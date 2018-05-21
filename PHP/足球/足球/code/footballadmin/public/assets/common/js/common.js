/**
 *verify email format
 */
function isEmail(email) {
    var rex = /^[0-9a-zA-Z_][_\.0-9A-Za-z\-]{0,31}@([0-9A-Za-z][0-9A-Za-z\-]{0,30}\.){1,4}[A-Za-z]{2,4}$/;
    //var rex = /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))$/i;
    return rex.test(email);
}

function getStringLen(str) {
    //return str.replace(/[^\x00-\xff]/g,"aa").length;
    var myLen = 0;
    for (var i = 0; (i < str.length); i++) {
        if (str.charCodeAt(i) > 0 && str.charCodeAt(i) < 128)
            myLen++;
        else
            myLen += 2;
    }
    return myLen;
};

function countdown(obj,callback) {
    var time = parseInt(obj.attr("data-time"));
    var sendTime = parseInt(obj.attr("data-send-time"));
    var max = parseInt(obj.attr("data-max"));
    var func = function () {
        if(callback && $.isFunction(callback)){
            callback();
        }
    }
    max  = max ? max : 60;
    var sur = max - (time - sendTime);
    var st = null;
    try{
        var countdownTime = function(){
            if(sur < 1){
                func();
                obj.text(max);
                clearTimeout(st);
                return;
            }
            st = setTimeout(function () {
                sur--;
                obj.text(sur);
                countdownTime();
            },1000);
        }
    }catch (e){}
    countdownTime();
}

function countdownTime(obj,func,max,sur,st) {

}


function countdownAll(time,callback) {
    var si = setInterval(function () {
        var now = parseInt(new Date().getTime() / 1000);
        var diff = time - now;
        if(diff <= 0){
            clearInterval(si);
            callback(false);
            return;
        }
        var day = parseInt(diff / (24 * 60 * 60));
        var hour = parseInt((diff - (day * 24 * 60 * 60)) / 3600);
        var minute = parseInt((diff - (day * 24 * 60 * 60) - (hour * 3600)) / 60);
        var second = parseInt(diff - (day * 24 * 60 * 60) - (hour * 3600) - minute * 60);
        callback(day,hour,minute,second);
    },1000);
}

// Extend the default Number object with a formatMoney() method:
// usage: someVar.formatMoney(decimalPlaces, symbol, thousandsSeparator, decimalSeparator)
// defaults: (2, "$", ",", ".")
Number.prototype.formatMoney = function (places, symbol, thousand, decimal) {
    places = !isNaN(places = Math.abs(places)) ? places : 2;
    symbol = symbol !== undefined ? symbol : "$";
    thousand = thousand || ",";
    decimal = decimal || ".";
    var number = this,
        negative = number < 0 ? "-" : "",
        i = parseInt(number = Math.abs(+number || 0).toFixed(places), 10) + "",
        j = (j = i.length) > 3 ? j % 3 : 0;
    return symbol + negative + (j ? i.substr(0, j) + thousand : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousand) + (places ? decimal + Math.abs(number - i).toFixed(places).slice(2) : "");
}

