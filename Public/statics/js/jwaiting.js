var JWaiting = function () {
    var defaultMsg = '请稍候';
    var tpl='<div data-model="jwaiting" style="z-index:1000;background:rgba(0,0,0,0.7);height:100%;width:100%;position:fixed;left:0;top:0;display: block;">'
                +'<div style="z-index:1001;width: 500px;height: 100px;line-height: 50px;border-radius: 5px;background: rgba(225,225,225,1);position: absolute;top: 50%;margin-top: -50px;left: 50%;margin-left: -250px;padding: 0 10px;">'
                    +'<a href="#" style="width: 100%;display: block;font-size: 20px;color: #333333;cursor: context-menu;line-height: 100px;text-align: center;">$MSG$</a>'
                +'</div>'
            +'</div>';
    return {
        start: function (waitingMsg) {
            $('div[data-model="jwaiting"]').remove();
            $("body").append(tpl.replace("$MSG$", waitingMsg && waitingMsg != null && waitingMsg.length > 0 ? waitingMsg : defaultMsg));
            $('div[data-model="jwaiting"]').css("display", "block");
        },
        stop: function () {
            $('div[data-model="jwaiting"]').remove();
        }
    };
}
/**
使用举例：
//需要引入jquery，任意版本均可
//初始化对象
var waiting=new JWaiting();
//启动等待窗口
waiting.start("要显示的消息");//参数是要显示的等待内容，无参或传空值将默认显示 Please waiting...
//关闭等待窗口
waiting.stop();
**/