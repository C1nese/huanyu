/**
 * Created by Administrator on 2016/5/1.
 */
/************************************************
 * 计算文档的可见高度
 * @return	Object de 	像素单位
 */
function windowHeight() {
    var de = document.documentElement;
    return self.innerHeight||(de && de.clientHeight)||document.body.clientHeight;
}
/************************************************
 * 计算文档的可见宽度
 * @return	Object de 	像素单位
 */
function windowWidth() {
    var de = document.documentElement;
    return self.innerWidth||(de && de.clientWidth)||document.body.clientWidth;
}
/************************************************
 *
 * window.onresize是窗口改变大小的时候，因为窗口改变大小，文档的可见高度或宽度会变化。
 */
window.onload = window.onresize=function(){
    var win = windowWidth();
    var hei = windowHeight();


    //公告标题长度
    var gonggao = $('.list-inline-notice').innerWidth() - 205;
    $('.gg_title').css('width',gonggao);

    $('#zbmukf').text();

    $('#zbx').text(win);
    $('#zby').text(hei);
}


/* 页面加载完毕 */
$(document).ready(function(){
    /* P元素被点击 */
    $('.x-navigation-minimize').click(function(){
        /* 隐藏掉被点元素自身 */
        $('.profile-nirv-title').toggle();
    });
});
