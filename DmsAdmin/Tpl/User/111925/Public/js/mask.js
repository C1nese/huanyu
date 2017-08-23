$(function () {
    //弹出公告
    var hideshow = 'hide' + $('#maskinfo').val();
    if (getCookie('mask') && getCookie('mask') == hideshow) {
        $('.mask').hide(0);
    } 
    $('.mask .mask-close').click(function () {
        $('.mask').hide(0);
        setCookie('mask',hideshow);
    });
});
//设置cookie
function setCookie(name, value) {
    var cookieText = encodeURIComponent(name) + '=' + encodeURIComponent(value);
    document.cookie = cookieText;
}
function getCookie(name) {
    var cookieName = encodeURIComponent(name) + '=';
    var cookieStart = document.cookie.indexOf(cookieName);
    var cookieValue = null;
    if (cookieStart > -1) {
        cookieEnd = document.cookie.indexOf(';', cookieStart);
        if (cookieEnd == -1) {
            cookieEnd = document.cookie.length;
        }
        cookieValue = decodeURIComponent(document.cookie.substring(cookieStart + cookieName.length, cookieEnd));
    }
    return cookieValue;
}
