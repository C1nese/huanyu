/**Magin 2016/8/28 */

$(function(){
    function fontLeft(e){
        $(e).children('span').css('magin-left','0px')
         $(e).children('span').animate({
                marginLeft:parseFloat($(e).css('width'))-parseFloat($(e).children('span').css('width'))-12
            },2000,"linear",function(){
                fontRight(e);
            })
    }
    function fontRight(e){
        $(e).children('span').css('magin-left',parseFloat($(e).css('width'))-parseFloat($(e).children('span').css('width')));
        $(e).children('span').animate({
                marginLeft:'12px'
            },2000,"linear",function(){
                fontLeft(e);
            })
    }
    $('.font-scroll').each(function(){if($(this).children('span').css('width')>$(this).css('width')){
        fontLeft(this)
        }
    })
})