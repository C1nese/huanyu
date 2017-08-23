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
    var gmbnt, jbnt;
    var win = windowWidth();
    var zbnt = $('.product_add2nirvana');
    var lbwin = $('.nirvana_shop_item').innerWidth();

    $('#zbmukf').text(win);
    $('#zbcsd').text(lbwin);
    gmbnt = lbwin / 2 - 44;
    zbnt.innerWidth(gmbnt);
    if(lbwin < 302){
        zbnt.css({"padding-left":"10px","padding-right":"10px"})
        zbnt.text({:GET_F_L('购买')});
    }
    if(lbwin > 301){
        zbnt.css({"padding-left":"20px","padding-right":"20px"})
        zbnt.text({:GET_F_L('购买商品')});
    }


    //var intw = $('.shop_list_item').innerWidth()  * 0.217;
   // $('.n4').innerWidth(intw);

}

$(document).ready(function(){
    //$("span").parent().css({"color":"red","border":"2px solid red"});
    var btnrh = $("button.shop-btnrh");
    var btnle = $('button.shop-btnle');
    btnrh.click(function(){
        var mb = $(this).parent().siblings("input");
        var mbval = parseInt(mb.val());
        mb.val(mbval + 1);
       // $(this).css({"color":"#fff","border-left-color":"#484343"});
    });

    btnrh.mousedown(function(){
        $(this).css({"color":"Azure","border":"1px solid Azure"});
    });
    btnrh.mouseup(function(){
        $(this).css({"color":"#fff","border-left-color":"#484343"});
    });

    btnle.click(function(){
        var mb = $(this).parent().siblings("input");
        var mbval = parseInt(mb.val());
        if(mbval > 1){
            mb.val(mbval - 1);
        }else{
            mb.val(1);
        }
    });

    btnle.mousedown(function(){
        $(this).css({"color":"Azure","border":"1px solid Azure"});
    });
    btnle.mouseup(function(){
        $(this).css({"color":"#fff","border-left-color":"#484343"});
    });



});

