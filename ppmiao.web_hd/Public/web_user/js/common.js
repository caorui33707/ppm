$(function(){

    /*tab标签切换*/
    function tabs(tabTit,foot,tabCon){
	$(tabCon).each(function(){
	  $(this).children().eq(0).show();

	  });
	$(tabTit).each(function(){
	  $(this).children().eq(0).addClass(foot);
	  });
     $(tabTit).children().click(function(){
        $(this).addClass(foot).siblings().removeClass(foot);
         var index = $(tabTit).children().index(this);
         $(tabCon).children().eq(index).show().siblings().hide();
    });
     }
  tabs(".investment_title","foot",".investment_con");

 })
