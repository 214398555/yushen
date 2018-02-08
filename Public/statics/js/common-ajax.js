/*===== Ajax请求 =====*/
;(function($){
    var ajax = $.ajax;
    // 用于存储ajax的请求
    var ajaxState = {};

    var kinhomAjax = function () {
        var args = Array.prototype.slice.call(arguments, 0);
        // url data 一致，
        // 应该将 url取出，data按键值排序，后将值拼接在一起，进行sha1得到的值作为指纹
        // 累先用 url作为指纹吧
        var hash = hex_sha1(typeof args[0] === 'string'?args[0]:args[0].url+(args[0].data?JSON.stringify(args[0].data):''));
        if (typeof ajaxState[hash] !== 'undefined') {
            if (ajaxState[hash] > 1) {
                console.log(ajaxState[hash]+'\u91cd\u590d\u6267\u884c'+hash);
            }
            ++ajaxState[hash];
            return $.Deferred();
        }
        ajaxState[hash] = 1;
        var def = ajax.apply($,args);
        def.done(function () {
            delete ajaxState[hash];
        });
        return def;
    };

    $.ajax = kinhomAjax;
})(jQuery);
function AjaxRequest(url, data, callBack) {
    $.ajax({
        url: url,
        type: 'POST',
        timeout: 200000,
        data: data,
        success: function (result) {
            callBack(result);
        }
    });
}
function AjaxRequest_GET(url, callBack) {
    $.ajax({
        url: url,
        type: 'GET',
        timeout: 200000,
        success: function (result) {
            callBack(result);
        }
    });
}
function AjaxRequestAll(url, data, before, callBack) {
    $.ajax({
        url: url,
        type: 'POST',
        data: data,
        beforeSend: function (result) {
            before(result);
        },
        success: function (result) {
            callBack(result);
        }
    });
}

function AjaxRequestParseJson(url, data, callBack) {
    $.ajax({
        url: url,
        type: 'POST',
        data: data,
        success: function (result) {
            var obj = jQuery.parseJSON(result);
            callBack(obj);
        }
    });
}

function AjaxRequestParseJson(url, data, before, callBack) {
    $.ajax({
        url: url,
        type: 'POST',
        data: data,
        success: function (result) {
            var obj = jQuery.parseJSON(result);
            callBack(obj);
        },
        beforeSend: function (result) {
            var obj = jQuery.parseJSON(result);
            before(obj);
        }
    });
}

/*同步Ajax请求*/
function AjaxRequestSync(url, data, callBack) {
    $.ajax({
        async: false,
        type: 'POST',
        url: url,
        data: data,
        success: function (result) {
            callBack(result);
        }
    });
}

function AjaxAsyncRequest(url, data, callBack) {
    $.ajax({
        async: false,
        url: url,
        data: data,
        success: function (result) {
            callBack(result);
        }
    });
}

function getQueryStringByName(name) {

    var result = location.search.match(new RegExp("[\?\&]" + name + "=([^\&]+)", "i"));
    if (result == null || result.length < 1) {
        return "";
    }
    return result[1];
}