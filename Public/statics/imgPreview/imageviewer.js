window.innerHeight = window.innerHeight || document.documentElement.clientHeight;
window.innerWidth = window.innerWidth || document.documentElement.clientWidth;
window.onload = function () {
    var DEFAULT_URL = "";
    var queryString = document.location.search.substring(1);
    var params = parseQueryString(queryString);
    var file = 'file' in params ? params.file : DEFAULT_URL;
    var winheight = window.innerHeight - 10;
    var winwidth = window.innerWidth - 10;
    var img = new Image();
    img.src = file;
    if (window.ActiveXObject) {
        img.onreadystatechange = function () {//为了避免图片尚未加载完全导致图片原始宽高读取错误
            if (img.readyState == "loaded" || img.readyState == "complete") {
                img.onreadystatechange = null;
                var imgObj = document.getElementById("imgPreview");
                imgObj.src = file;
                imgResize(winwidth, winheight, img.width, img.height, "imgPreview");
            }
        }
    } else {
        img.onload = function () {
            img.onload = null;
            var imgObj = document.getElementById("imgPreview");
            imgObj.src = file;
            imgResize(winwidth, winheight, img.width, img.height, "imgPreview");
        }
    }
};
function loadImage() {

}
function parseQueryString(query) {
    var parts = query.split('&');
    var params = {};
    for (var i = 0, ii = parts.length; i < ii; ++i) {
        var param = parts[i].split('=');
        var key = param[0].toLowerCase();
        var value = param.length > 1 ? param[1] : null;
        params[decodeURIComponent(key)] = decodeURIComponent(value);
    }
    return params;
}

///重置图片对象为指定的maxWidth和maxHeight大小
function imgResize(maxWidth, maxHeight, imgWidth, imgHeight, imgId) {
    var imgObj = document.getElementById(imgId);
    if (imgWidth > 0 && imgHeight > 0) {
        if (imgWidth / imgHeight >= maxWidth / maxHeight) {
            if (imgWidth > maxWidth) {
                //imgObj.width(maxWidth);
                //imgObj.height((imgHeight * maxWidth) / imgWidth);
                imgObj.width = maxWidth;
                imgObj.height = (imgHeight * maxWidth) / imgWidth;
            } else {
                imgObj.width = imgWidth;
                imgObj.height = imgHeight;
                //imgObj.width(imgWidth);
                //imgObj.height(imgHeight);
            }
        } else {
            if (imgHeight > maxHeight) {
                imgObj.height = maxHeight;
                imgObj.width = (imgWidth * maxHeight) / imgHeight;
                //imgObj.height(maxHeight);
                //imgObj.width((imgWidth * maxHeight) / imgHeight);
            } else {
                imgObj.width = imgWidth;
                imgObj.height = imgHeight;
                //imgObj.width(imgWidth);
                //imgObj.height(imgHeight);
            }
        }
    }
    else {
        imgObj.width = maxWidth;
        imgObj.height = maxHeight;
        //imgObj.width(maxWidth);
        //imgObj.height(maxHeight);
    }
    hidheight.value = imgObj.height;
    hidwidth.value = imgObj.width;
    block1.style.left = (window.innerWidth - imgObj.width) / 2 + 'px';
    block1.style.top = 0 + 'px';
    operate.style.left = (window.innerWidth - 135) / 2 + 'px';
    operate.style.bottom = 10 + 'px';
    operate.style.display = "block";
}

drag = 0
move = 0

// 拖拽对象
var ie = document.all;
var nn6 = document.getElementById && !document.all;
var isdrag = false;
var y, x;
var oDragObj;

function moveMouse(e) {
    if (isdrag) {
        oDragObj.style.top = (nn6 ? nTY + e.clientY - y : nTY + event.clientY - y) + "px";
        oDragObj.style.left = (nn6 ? nTX + e.clientX - x : nTX + event.clientX - x) + "px";
        return false;
    }
}

function initDrag(e) {
    var oDragHandle = nn6 ? e.target : event.srcElement;
    var topElement = "HTML";
    while (oDragHandle.tagName != topElement && oDragHandle.className != "dragAble") {
        oDragHandle = nn6 ? oDragHandle.parentNode : oDragHandle.parentElement;
    }
    if (oDragHandle.className == "dragAble") {
        isdrag = true;
        oDragObj = oDragHandle;
        nTY = parseInt(oDragObj.style.top + 0);
        y = nn6 ? e.clientY : event.clientY;
        nTX = parseInt(oDragObj.style.left + 0);
        x = nn6 ? e.clientX : event.clientX;
        document.onmousemove = moveMouse;
        return false;
    }
}
document.onmousedown = initDrag;
document.onmouseup = new Function("isdrag=false");

function clickMove(s) {
    if (s == "up") {
        dragObj.style.top = (parseInt(dragObj.style.top) + 100) + 'px';
    } else if (s == "down") {
        dragObj.style.top = (parseInt(dragObj.style.top) - 100)+'px';
    } else if (s == "left") {
        dragObj.style.left = (parseInt(dragObj.style.left) + 100)+'px';
    } else if (s == "right") {
        dragObj.style.left = (parseInt(dragObj.style.left) - 100)+'px';
    }

}

function smallit() {
    var height1 = imgPreview.height;
    var width1 = imgPreview.width;
    imgPreview.height = height1 / 1.2;
    imgPreview.width = width1 / 1.2;
}

function bigit() {
    var height1 = imgPreview.height;
    var width1 = imgPreview.width;
    imgPreview.height = height1 * 1.2;
    imgPreview.width = width1 * 1.2;
}
function realsize() {
    imgPreview.height = hidheight.value;
    imgPreview.width = hidwidth.value;
    var left = (window.innerWidth - imgPreview.width) / 2;
    block1.style.left = (window.innerWidth - imgPreview.width) / 2 + 'px';
    block1.style.top = 0 + 'px';
}
function featsize() {
    var width1 = hidwidth.value;
    var height1 = hidheight.value;
    var width2 = 360;
    var height2 = 200;
    var h = height1 / height2;
    var w = width1 / width2;
    if (height1 < height2 && width1 < width2) {
        imgPreview.height = height1;
        imgPreview.width = width1;
    }
    else {
        if (h > w) {
            imgPreview.height = height2;
            imgPreview.width = width1 * height2 / height1;
        }
        else {
            imgPreview.width = width2;
            imgPreview.height = height1 * width2 / width1;
        }
    }
    block1.style.left = (window.innerWidth - imgPreview.width) / 2 + 'px';
    block1.style.top = 0 + 'px';
}

