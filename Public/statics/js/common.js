/**
* 判断字符的长度
* @return 超出 false 
*/
function getLen(target,len){  
    var strlen=0; //初始定义长度为0  
    var txtval = $.trim(target);  
    for(var i=0;i<txtval.length;i++){  
     if(isChinese(txtval.charAt(i))==true){  
      strlen=strlen+2;//中文为2个字符  
     }else{  
      strlen=strlen+1;//英文一个字符  
     }  
    }  
    strlen=Math.ceil(strlen/2);//中英文相加除2取整数  
    if(strlen > len)
      return false;
    else 
      return true;  
} 
String.prototype.utflen = function() {  //计算字符串长度(英文占1个字符，中文汉字占3个字符)
    var len = 0; 
    for (var i=0; i<this.length; i++) { 
        if (this.charCodeAt(i)>127 || this.charCodeAt(i)==94) { 
            len += 3; 
        } else { 
            len ++; 
        } 
    } 
    return len; 
}
function isChinese(str){  //判断是不是中文  
    var reCh=/[u00-uff]/;  
    return !reCh.test(str);  
}   

/**
* 返回页码列表的JSON对象
* @param pageIndex：当前页码，从1开始 
* @param rowCount: 数据总行数
* @param pageSize: 每页显示条数，不传则默认20条
**/
function getPageList(pageIndex,rowCount,pageSize){
    pageSize=pageSize?pageSize:20;
    var count=rowCount==0?0:(parseInt(rowCount/pageSize)+(rowCount%pageSize?1:0));
    var _page=[];
    var currentPage=pageIndex;
    if(currentPage>6){
        _page.push({p:1,type:'page',text:1});
        _page.push({p:currentPage-1,type:'prev',text:'<<'});

        var lastIndex=count>currentPage+5?currentPage+5:count;
        for(var i=currentPage-5;i<=lastIndex;i++){
            _page.push({p:i,type:'page',text:i});
        }
        if(currentPage<count){
            _page.push({p:currentPage+1,type:'next',text:'>>'});
        }
        if(count>lastIndex){
            _page.push({p:count,type:'last',text:count});
        }
    }
    else{
        if(currentPage>1){
            _page.push({p:currentPage-1,type:'prev',text:'<<'});
        }
        var lastIndex=count>12?12:count;
        for(var i=1;i<=lastIndex;i++){
            _page.push({p:i,type:'page',text:i});
        }
        if(currentPage<count){
            _page.push({p:currentPage+1,type:'next',text:'>>'});
        }
        if(count>lastIndex){
            _page.push({p:count,type:'last',text:count});
        }
    }
    return _page;
}
/**
* 去除数组中重复的值
*/
function uniqueArray(arr){
  var str = [];
  for(var i = 0,len = arr.length;i < len;i++){
  ! RegExp(arr[i],"g").test(str.join(",")) && (str.push(arr[i]));
  } 
  return str;
}
/**
* 删除数组中指定的值
*/
function removeByValue(arr, val) {
    for(var i=0; i<arr.length; i++) {
      if(arr[i] == val) {
        arr.splice(i, 1);
        break;
      }
    }
}
Date.prototype.Format = function (fmt) { //author: meizz 
    var o = {
        "M+": this.getMonth() + 1, //月份 
        "d+": this.getDate(), //日 
        "H+": this.getHours(), //小时 
        "m+": this.getMinutes(), //分 
        "s+": this.getSeconds(), //秒 
        "q+": Math.floor((this.getMonth() + 3) / 3), //季度 
        "S": this.getMilliseconds() //毫秒 
    };
    if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    for (var k in o)
    if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
    return fmt;
}
function format_money(money){
    if(!money || parseFloat(money)==0)
        return 0;
    var str = money.toString().replace(/(?=\d)(?!\.\d*)(\d)(?=(?:\d{3})+($|\.\d*))/g,"$1,");
    return str.indexOf('.')<0?str:str.replace(/\.?0*$/g,'');
}
/**
* 一维数组排序
**/
function sortNumber(a,b)
{
    return a - b
}
/**
 * 获取Url全称
 */
function getHostUrl($pageUrl){
    var $host=window.location.protocol+"//"+window.location.host+'/';
    if($pageUrl&&$pageUrl.length>0&&$pageUrl.substr(0,1)=='/')
        $pageUrl=$pageUrl.substr(1);
    if($pageUrl&&$pageUrl.length>0){
        if($pageUrl.substr(0,4).toLowerCase()=='http')
            return $pageUrl;
        else if($pageUrl.substr(0,3).toLowerCase()=='www')
            return window.location.protocol+"//"+$pageUrl;
        return $host+$pageUrl;
    }
    else
        return $host;
}