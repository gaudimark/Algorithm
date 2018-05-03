	var picset=[];		
 function slidebox(){
	var pi=0;
	$(".slidebox").each(function(){
		var _this=$(this);
			pi=pi+1;
			var T=0;
			
			if(_this.children().width()>_this.width()){
				if(_this.children().find("b").length<1){
				_this.children().append("<b>"+$(this).children().text()+"</b>");
				}
				
			if(_this.children().find("b").width()>_this.width()){
				var tw=_this.children().outerWidth(true);
				var twb=_this.children().find("b").width();	
				picset[pi]=setInterval(function(){
					T++;
					if(T>=(tw-30)/2+30){
					T=0;
					};
					_this.children().css("transform","translate(-"+T+"px,0px)"); 
				},40);
			}
		} else{_this.children().css("position","relative")}; 
	})
} 
function clear(){
	for(var dd=0;dd<=$(".slidebox").length;dd++){
			 clearInterval(picset[dd]); 
		}
}
$(document).ready(function(){
	scorelist();
	slidebox();
	var i=0;
$(".gundo ul").html($(".gundo ul").html()+$(".gundo ul").html());
setInterval(function(){
	/* i++;
	if(i>=parseInt(W/2)){
		i=0;
	} */
	i++;
	 if(i>=$(".gundo").find("li").length/2+1){
		 $(".gundo").css("top","0")
		i=0;
	} 
	gundo();
},3000)
function gundo(){
$(".gundo").animate({top:-i*34+"px"},200)}
/* 排行榜Tab */
$(".tab-cont").eq(1).hide();
$(".tab-nav a").click(function(){
	$(".tab-nav .active").removeClass("active");
	$(this).parents("li").addClass("active");
	$(this).parents(".tab-nav").siblings(".tab-cont").hide();
	$(this).parents(".tab-nav").siblings(".tab-cont").eq($(".tab-nav a").index(this)).show();
})
$(".screen-icon").on("touchend",function(){
	$(".date-select").css({"bottom":"0"})
});
/* 从左滑入效果 */
var trade=0;
var puttime= setInterval(function(){
	if(trade<=$(".trade").length){
	$(".trade").eq(trade).css("animation","trade 0.4s linear 1 forwards");trade++;}else{clearInterval(puttime)}
},100) ;
var grow=0;
var puttimeb= setInterval(function(){
	if(grow<=$(".grow").length){
	$(".grow").eq(grow).css("opacity","1");grow++;}else{clearInterval(puttimeb)}
},100);
if($(".footer").length<1 && $(".bottom").length<1 && $(".chipin-fixed-calculate").length<1&&$(".collect").length<1 &&$(".forum-review").length<1){
	$(".container").css({"padding-bottom":"0","min-height":"100%"})
};
/* 充值按钮效果 */
$(".full-price-sum li").on("click",function(){
	$(this).siblings(".select").removeClass("select")
	$(this).addClass("select");
	$(".sum-input").find("input").val($(this).text())
})
$(".full-price-input a").on("click",function(){
	$(this).siblings(".select").removeClass("select")
	$(this).addClass("select");
});
/* 选择赔率导航效果 */
 var a_width=0;
$(".select-menu").css("overflow","visible")
$(".menu-list a").each(function(){
	a_width+=$(this).outerWidth(true);
})
$(".menu-list").width(a_width+20);
$(".arena-menu .menu-list a").click(function(){
	$(".menu-list .select").removeClass("select");
	$(this).addClass("select");
	var aleft= $(this).offset().left;
	if(aleft+$(this).outerWidth(true)>$(window).width()){
		$(".menu-list").animate({
			left:-$(this).position().left+$(window).width()-$(this).outerWidth(true)-20
		},100);
	}
	else if(aleft<0){
		$(".menu-list").animate({
			left:-$(this).position().left
		},100);
	}
});
setTimeout(function(){
	if($(".menu-list .select").length>0){
		if($(".menu-list .select")[0].offsetLeft>($(window).width()-100))
		$(".menu-list").css("left",-($(".menu-list .select")[0].offsetLeft-$(".menu-list .select").outerWidth(true)));
	}
},10) 
$(".multi ul").each(function(){
	$(this).find("li").first().css("margin-left",$(".ratetable").find("ul").eq(0).find("li").first().outerWidth(true)-1);
});
$(".cathectic-bom").each(function(){
	$(this).find("span").first().css("width",(($(this).find(".num-yet").text().replace(",","")/($(this).find(".num-yet").text().replace(",","")*1   +  $(this).find(".num-all").text().replace(",","")*1  )*100).toFixed(2)+"%"));
})
$(".ulded").each(function(){
	$(this).find("li").first().css("line-height",$(this).height()+"px");
})
var li_width=0;
$(".chipin-left li").each(function(){
//	$(this).css("left",a_width);
	li_width+=$(this).outerWidth(true);
})
$(".chipin-left ul").width(li_width);	
$(".next").click(function(){
	var cleft=parseInt($(".chipinlist").css("left")) ;
	var clen=Math.abs(Math.ceil(cleft/$(".chipinlist li").width()));
	$(".chipinlist").animate({left:-$(".chipinlist li").eq(clen+2).position().left});
})
$(".parve").click(function(){
	var cleft=parseInt($(".chipinlist").css("left")) ;
	var clen=Math.abs(Math.ceil(cleft/$(".chipinlist li").width()));
	if(clen>2){
	$(".chipinlist").animate({left:-$(".chipinlist li").eq(clen-3).position().left})
	}else{$(".chipinlist").animate({left:0})}
})
$(".payguess select").change(function(){
	$(this).siblings("a").text($(this).find("option:selected").text());
})
var bg=['chipin-icon-1','chipin-icon-2','chipin-icon-3','chipin-icon-4']
for(var d=0;d<$(".personfer i").length;d++){
	var inum= Math.floor(Math.random()*4);
		$(".personfer i").eq(d).addClass(""+bg[inum]+"");
}
$(".personfer i").each(function(){
	var itop=Math.floor(Math.random()*($(".personfer").height()-30)+1);
	var ileft=Math.floor(Math.random()*($(".personfer").width()-30)+1);
	$(this).css({"top":itop,"left":ileft});	
});
var c=0;
if($(".athletics-arena .betting-open").length>0){
	if($(".personfer").length>0){
var personfer= setInterval(function(){
		var lilen= Math.floor(Math.random()*$(".athletics-arena .betting-open").length);
		var cdtleft=Math.floor(Math.random()*( ($(".athletics-arena .betting-open").eq(lilen).offset().left+$(".athletics-arena .betting-open").eq(lilen).outerWidth(true)-20)-$(".athletics-arena .betting-open").eq(lilen).offset().left )+$(".athletics-arena .betting-open").eq(lilen).offset().left);
		var pt=$(".personfer").offset().top;
		var ct=$(".athletics-arena .betting-open").eq(lilen).offset().top;
		var ch=$(".athletics-arena .betting-open").eq(lilen).height();		
		/* var cdttop=Math.floor(Math.random()*( (pt-ct)-(pt-ct-ch+40))+(pt-ct-ch+40)); */
		var cdttop=pt-ct-ch+27
	$(".personfer i").eq(c).animate({opacity:".7",left:cdtleft,top:-cdttop});
	c++;	
	if(c>$(".personfer i").length){
		clearInterval(personfer);
	}
},200);}}
var site;
var site_i=0;
var id;
var bt=0;
$(document).on("click",".betting-open",function(){
	 $(".betting-open").css("animation","");
	 $(".betting-glow").removeClass("betting-glow")
	 $(this).css("-webkit-animation"," glow 800ms ease-out infinite alternate").addClass("betting-glow");
	 setTimeout(function(){
	//	if( $(".chipin-gold .chipin-cnt").text()!=""){
			$(".chipin-gold .chipin-cnt").css({"padding":".05rem .1rem","top":-($(".chipin-gold .chipin-cnt").height()+10)+"px"})
			if($(".chipin-gold .chipin-cnt").find("i").length<1){
				$(".chipin-gold .chipin-cnt").append("<i></i>");
			}
		//}
		 
	 },100) 
});
  $(".chipin-right button").click(function(){
	var setTop= $(this).offset().top;
	var setLeft= $(this).offset().left;
	if($(".slidecart").length<1){
		 $(".container").after("<div class='slidecart' style='width:38px;height:38px;position: fixed;left: "+(setLeft+20)+"px;top:"+setTop+"px;z-index:101;display:none;'></div>");
	}
}); 
$(".terrace-right-bom").each(function(){
	$(this).height($(this).height()-36);
	$(this).find("span").css({"margin-top":($(this).height()-$(this).find("span").height()-20)/2});
})
$(document).on("click",".betting-open",function () {
	var arenaID = parseInt($(this).attr("data-arena"));
	var playStatus = parseInt($('#play-status-'+arenaID).val());
	var arenaStatus = parseInt($('#arena-status-'+arenaID).val());
	if(arenaStatus != ARENA_STATUS.start){
		msgBox.error("擂台已结束");
		return false;
	}
	if(playStatus >= PLAY_STATUS.start && playStatus < PLAY_STATUS.end){
		msgBox.error("比赛已开始");
		return false;
	}
	if(playStatus >= PLAY_STATUS.end){
		msgBox.error("比赛已结束");
		return false;
	}
	$("#betting-arena-id").val(arenaID);
	$("#betting-target").val($(this).attr("data-target"));
	$("#betting-odds").val($(this).attr("data-odds"));
	$("#betting-rule").val($(this).attr("data-rule"));
	$("#betting-cust-value").val('');
	$("#betting-forecast-income").text('0.00');
	$(".betting-open").css("animation","");
	getMaxBet({'arena_id':arenaID,'target':$(this).attr("data-target")});
	$(this).css("animation"," glow 800ms ease-out infinite alternate");
	if($(this).attr("data-private") && $(this).attr("data-private") != '' && $(this).attr("data-private") != undefined){
		arenaPrivate($(this).attr("data-private"),$(this).attr("data-arena"),function () {
			chipin_fixed();
		});
	}else {
		chipin_fixed();
	}
})	;

$(".chipinbg").click(function(){
	chipinclose();
})
$(".chipin-fixed button").click(function(){
	chipinclose();
})
function chipinclose(){
	$(".chipin-fixed").css({"transform":"translate(0px,-400px)"});
	$(".chipincase").css("left","-100%");
}
$(".seticon").click(function(){
	if($(this).find("ul").css("display")=="none"){
		$(this).find("ul").show();
	}else{if($(this).find("select").length<1){
			$(this).find("ul").hide();}
	}
	})
$(".downlist i").click(function(){
	if($(this).siblings("ul").css("display")=="block"){
		$(this).siblings("ul").hide();return false;
	}
})
$(".downlist select").change(function(){
	$(this).parents(".downlist").find("ul").hide();
})
$(document).on("click",".chipin-gold .chipinlist li",function(){
	$(".chipin-gold .chipinlist li").css("animation","")
	$(this).css("animation"," glow-b 800ms ease-out infinite alternate");
})
$(".ranking-time-icon a").click(function(){
	$(".ranking-time-icon span").text($(this).text());	
});
$(".screen-icon").click(function(){
	$(".screem").css("animation","screem 0.5s linear 1 forwards");
})

$(".chipin-fixed-calculate li").click(function(){
	var lival = $(this).text();
	if($(this).text().indexOf("K")==1){
		lival = parseInt($(this).text())*1000;
	}
	if($(this).text().indexOf("W")==1){
		lival = parseInt($(this).text())*10000;
	}
	var inval =$(".chipin-right input").val();
	$(".chipin-fixed-calculate").find(".chipin-right").find("input").val(inval*1+lival*1);
	
})
$(document).on("click",".left-menu-close,.left-menu-info .btn",function(){
	$(".left-menu").css("animation","leftmenu 0.1s linear 1 forwards");
}) 
$(".sideBar-icon").click(function(){
	var myScrollb=new iScroll("bwrapper",{hScroll:false,vScrollbar:false});
	$(".left-menu").css({"animation":"leftmenuIn 0.1s linear 1 forwards","-moz-animation":"leftmenuIn 0.1s linear 1 forwards","-ms-animation":"leftmenuIn 0.1s linear 1 forwards","-webkit-animation":"leftmenuIn 0.1s linear 1 forwards","-o-animation":"leftmenuIn 0.1s linear 1 forwards"});		
})
$(window).resize(function(){
$(".ft-cent i").height($(".ft-cent i").width());
})
$(".ft-cent i").height($(".ft-cent i").width())
/* 关闭弹窗 */
$(".close").click(function(){
	$(this).css("border","0")
	$(".popup").hide();
})
$(".athletic-icon").click(function(){
	if($(".athletic-menu").css("display")=="none"){
	$(".athletic-menu").show();
	}else{$(".athletic-menu").hide();}
});
$(".container").click(function(){
	$(".athletic-menu,.seticon ul").hide();
})
 $(document).on("click",".checkbox",function(){
	 if(!!$(this).is(".checked")){
		  $(this).removeClass("checked"); 
		  $(this).find("ins").attr("class","");
		 // checkMid();
	 }else if($(this).attr("class")!="checked"){ $(this).addClass("checked");$(this).find("ins").attr("class","fa fa-check-circle-o");
		 //checkMid();
	 }
}).on("click",".firsdplus",function(){
	$(".addfriend").show();
});
if($("#pullDown").length<1 && $(".header").length>1){
	$(".container").css({"padding-top":"46px"});
}else if( $(".header").length<1){$(".container").css({"padding-top":"0"});}
if($("#pullDown").length<1){
	$(".container").css({"top":"0"});
}
$(".arena-group .right-about").each(function(){
	for(var c=0;c<$(this).find("img").length;c++)
	$(this).find("img").eq(c).css({"position":"absolute","top":"","right":$(this).find("span").width()+(10*c)});
})
$(".athleticstop").each(function(){
	$(this).find(".athleticstime,.athleticspot").css("width",($(this).width()-$(this).find(".athleticspic").outerWidth(true))/2+"px");
})
$(".match-stop").each(function(){
	$(this).css("margin-left",-($(this).width()/2+13)+"px")
})
$(".betting-disabled").click(function(){
	popup.tips({
		txt:"dfjdslfjskdf"
	})
})
$(".emotion").click(function(){
	setTimeout(function(){
		var facebox=new iScroll("facebox",{hScroll:false,vScrollbar:false});
	},200)
});
$(".agent-pop-up-bg,.agent-pop-up .fa-close").click(function(){
	$(".agent-pop-up").hide();
});
$("#pstatus_div ul a").click(function(){
	var pstatus=$(this).attr("pstatus");
	if($("#"+pstatus).length>0){
		$(".container").css("top",-($("#"+pstatus).eq(0).position().top-82)+"px");
	}
})
$(".header").each(function(){
if($(".header .search").length<1){
	$(".container").css("padding-top","46px");
};
})
$(".removebtn").each(function(){
	if($(this).parents(".orderlist").find(".removebtn").length<2){
		$(this).css("right","-80px")
	}
});
$(window).load(function(){
 if($(".home-footer").length>0){
	$(".container").css("padding-bottom","55px");
	 myScroll.refresh();
};
var flicking_inner=$(".main_image li").length-$(".flicking_inner a").length;
if(flicking_inner>0){
	for(var f_inner=0;f_inner<flicking_inner;f_inner++){
		$(".flicking_inner").append("<a href='javascript:' class=''></a>")
	} 
	
}
})
$(".select-type").click(function(){
	$(".project").show();
	 project_wrapper.refresh();
	$(".project-box").css("animation","project .1s linear 1 forwards");
	$(".container,.header,.footer,.home-footer").animate({left:"100px"},100);
})
$(".project-close").click(function(){
	$(".project-box").css("animation","projectout .1s linear 1 forwards");
	$(".container,.header,.footer,.home-footer").animate({left:"0"},100,function(){$(".project").hide();})
})
 setTimeout(function(){
	 var t=$("#key").val();
	$("#key").val("").focus().val(t);
 },2) 
 $(".logininput").each(function(){
	$(".logininput").find("input").blur(function(){
		$(".login-bom").show();
	}).focus(function(){
			$(".login-bom").hide();
	});
})
 imgslide();
 $(".down-btn").click(function(){
	  chipinspeed();
 })
  $(".betting-open").click(function(){
	  chipinspeed_open();
 })
/*  $(document).on("click",".reviewicon",function(){
	 $(".forum-review").show();
	 $(".forum-review input[type='text']").focus();
 }) */
}) ;
/* 用户第一次进入时动画 */
function animation(){
	var cop=document.cookie.indexOf("firstVisit=");
	if(cop==-1){
		var exdate = new Date();
		exdate.setDate(exdate.getDate()+30);
		document.cookie="firstVisit=1;expires="+exdate.toGMTString();
		$("#thelist .betting-open").eq(0).append("<i></i>").css("z-index","10")
			$("#thelist .betting-open").eq(0).find("i").css("animation"," hand 300ms ease-out infinite alternate");
			$("#thelist .betting-open").eq(0).css("animation"," glow 800ms ease-out infinite alternate");
		var glow=0;
		var glowtime=setInterval(function(){
			$("#thelist .betting-open").find("i").remove();
			$("#thelist .betting-open").css({"animation":"","z-index":"9"});
			$("#thelist .betting-open").eq(glow).append("<i></i>").css("z-index","10")
			$("#thelist .betting-open").eq(glow).find("i").css("animation"," hand 300ms ease-out infinite alternate");
			$("#thelist .betting-open").eq(glow).css("animation"," glow 800ms ease-out infinite alternate");
			glow++;
			if(glow>2){
				clearInterval(glowtime);
			}
		},900)
		setTimeout(function(){
			$("#thelist .betting-open").find("i").remove()
			$("#thelist .betting-open").css({"animation":"","z-index":"9"});
		},3500)
	}
}
/* radio */
(function($) {
       $.icheck = {
           init: function() {
               var _this = this;
               _this._radio = "radio";
               _this._disabled = "disabled";
               _this._enable = "enable";
               _this._checked = "checked";
               _this._hover = "hover";
               _this._arrtype = [_this._radio];
               _this._mobile = /ipad|iphone|ipod|android|blackberry|windows phone|opera mini|silk/i.test(navigator.userAgent);
               $.each(_this._arrtype, function(k, v) {
                   _this.click(v);
               });
           },
           click: function(elem) {
               var _this = this;
               elem = "." + elem;
               $(document).on("click", elem, function() {
                   var $this = $(this),
                       _ins = $this.find("ins");
                   if (!(_ins.hasClass(_this._disabled) || _ins.hasClass(_this._enable))) {
                       if ( !! _ins.hasClass(_this._checked)) {
                          // _ins.removeClass(_this._checked).addClass(_this._hover);
                       } else {
                           if (/radio/ig.test(elem)) {
                               var _name = $this.attr("name");
                               $(elem + "[name=" + _name + "]").find("ins").removeClass(_this._checked);
                           }
                           $(elem).find("ins").removeClass(_this._hover);
                           _ins.addClass(_this._checked);
                       }
                   }
               });
           }
       };
       $.icheck.init();
  })(jQuery); 
 function imgslide(){
$(".picbg img").each(function(){
		if($(this).attr("src")!="//res.51bet.com/common/images/default.png"){
		$(this).css("animation","imgslide .6s linear 1");}
})}
/* 赛事列表 */
function scorelist(){
	$(".scorelist .slideTxt").each(function(){
		$(this).width(($(this).find("h3").first().width()+$(this).find(".score-bf").outerWidth(true)+$(this).find("h3").last().width())+1);
		 if($(this).width()>$(this).parent().width()){
			 if($(this).find("h3").length<4){
				$(this).html($(this).html()+$(this).html());
			}
			$(this).width($(this).width()*2+30);
			$(this).find("h3").eq(2).css("margin-left","30px");
			$(this).find("b").remove();
		} 
	});
}
function slipping(){
	setTimeout(function(){
		$(".trade-meanwhile").each(function(){
			$(".trade-meanwhile:even").css("animation","trade_2 0.5s linear 1 forwards");
			$(".trade-meanwhile:odd").css("animation","trade_1 0.5s linear 1 forwards");
		})
	},500)
}
var click=true;
var g=0;
var bd=1;
var cartoon={
		shopping_cart:function(){
			$(".shopping-cart i").css("animation","shopping-cart 1s linear 1");
			setTimeout(function(){
				$(".shopping-cart i").css("animation","");
			},1000);
		},
		octopus:function(choice){
			if(click==true){
				click=false;
			var len=$(".betting-open").length;
			var octotime;
			$(".container").after("<div class='octopusbox'><i></i><div class='octopus'><span></span></div></div>");
			setTimeout(function(){
			var t=200;
				$(".octopusbox").css("transform","scale(1) rotate(360deg)");
				octotime=	setInterval(function(){
					l=Math.floor(Math.random()*(len-0)+0);
					$(".betting-open").css("animation","");
					$(".betting-open").eq(l).css("animation"," glow 800ms ease-out infinite alternate");					
				},t)
			},100);
			setTimeout(function(){
				$(".octopusbox").css({"transform":"scale(0) rotate(0deg)"});
				$(".octopusbox").animate({opacity:"0"},function(){
					$(".octopusbox").remove();
					clearInterval(octotime);
					l=choice;
					$(".betting-open").css("animation","");
					$(".betting-open").eq(l).css("animation"," glow 800ms ease-out infinite alternate");	
					click=true;
				})
			},4000);
			
		}
		},
	   slidecart:function(){
				$(".slidecart").show();
				var cartTop=$(".shopping-cart").position().top;
				var cartLeft=$(".shopping-cart").offset().left;
				$(".slidecart").animate({
					width:"10px",
					height:"10px",
					top:cartTop+"px",
					left:cartLeft+"px"
				},400,function(){
					$(".slidecart").remove();
				}) 
		
		},
	   coinsports:function(options){
		   var set=$.extend({},options);
		   if($("body").attr("class")==""){
		   if(bd=="1"){
			   bd=0;
		 	 var inumd= Math.floor(Math.random()*(4-1)+1);
			this_top=$(".betting-glow").offset().top;
			this_topn=$(".betting-glow").position().top;
			this_topn=this_topn+177;
			this_left=$(".betting-glow").offset().left;
			/* var gtop= Math.floor(Math.random()*(($('.chipin-right button').offset().top-this_top) - ($('.chipin-right button').offset().top-this_top-22))+($('.chipin-right button').offset().top-this_top-22)); */
			var gtop=$('.chipin-right button').offset().top-this_top-$(".betting-glow").height()+12;
			if($(".gold").length<1){
				$(".container").append("<div class='goldbox'></div>");
		    }
			$(".goldbox").append("<div class='gold chipin-icon-"+inumd+"' style='width:27px;height:27px;position:fixed;top:"+(this_topn+($('.chipin-right button').offset().top-$(".betting-glow").offset().top)-10)+"px;left:"+($('.chipin-right button').offset().left-10)+"px;line-height:27px;text-align:center;font-size:14px;transform: scale(0.6);transition: all 0.3s ease-in-out 0s;'></div>")
			var gleft= Math.floor(Math.random()*(($(".gold").eq(g).offset().left-$(".betting-glow").offset().left)-  ($(".gold").eq(g).offset().left-$(".betting-glow").offset().left-$(".betting-glow").outerWidth(true)+30))+($(".gold").eq(g).offset().left-$(".betting-glow").offset().left-$(".betting-glow").outerWidth(true)+30));			
 			$(".gold").eq(g).text($("#betting-cust-value").val()).css({"transform":"translate(-"+gleft+"px,-"+gtop+"px)  scale(0.6)","opacity":".7"});
			g++ 
	   }
	   bd=1;
	   }}/* ,
	   showimg:function(el){
		var el=$.extend({},el);
		var obj=el.obj;
		if($(".showimg").length<1){
		$("body").append("<div class='showimg' style='top:"+obj.offset().top+"px;'><i></i><div class='imgslide'><span></span><img src='"+obj.attr('src')+"' alt='' /></div></div>");
		$(".showimg").find("span").fadeIn(2400);
		setTimeout(function(){
			$(".showimg").css({"animation":"showimg 0s","transform-origin":"50% 50%","transform":"rotate(180deg)","height":"200px","width":"200px","opacity":"1","top":"50%","margin-top":"-150px","right":"50%","margin-right":"-100px"});
			  $(".showimg").animate({
				 width:"0px",
				height:"0px" ,
				opacity:"0",
				top:obj.offset().top+"px",
				right:"100px",
				marginTop:"0",
				marginRight:"0"
			},300);  
			setTimeout(function(){
			$(".showimg").remove();
			},500);
		},4000);
		}
	  } */
	   
}
function laud(fs){
	var fs=$.extend({},fs);
	var _this=fs._this;
	$(".praise").find("i").css("-webkit-animation","");
	$("#"+_this).find("i").css("-webkit-animation","laud .8s linear 1");
	
}
function dopeslide(dope){
	var dope=$.extend({},dope);
	var numd=dope.numd;
	var txt=dope.dotext;
	var arena=dope.arena;
	var dd=0;
for(var de=0;de<numd;de++){
	$("body").append("<div class='dope'><p>你的投注中奖<span>1126</span>金币</p></div>");}
	if(arena>0){
		$(".dope").eq(0).addClass("arenanews")
	}
	$(".dope").eq(0).css("animation","dope .3s linear 1 forwards").find("p").text(""+txt[0]+"");
	setTimeout(function(){
		$(".dope").eq(0).css("animation","dopeout .3s linear 1 forwards");
	},3000)
	var dopetime=setInterval(function(){
		dd++;
		if(dd>numd){
			clearInterval(dopetime);
		}
	$(".dope").eq(dd).css("animation","dope .3s linear 1 forwards").find("p").text(""+txt[dd]+"");
	setTimeout(function(){
		$(".dope").eq(dd).css("animation","dopeout .3s linear 1 forwards");
	},3000);
	},3100);
	setTimeout(function(){
	$(".dope").remove();
	},(numd+1)*3100)
}
window.onload=function(){
slipping();
}
   var myscrollc;
  function loaded(){
    myscrollc=new iScroll("wrappers",{vScrollbar:false,hScrollbar:false});
   }
   document.addEventListener("touchmove",function(e){e.preventDefault();},false); 
   window.addEventListener("DOMContentLoaded",loaded,false);
		function chipin_fixed(){
			$(".chipincase").css({"left":"0"});
			$(".chipin-fixed").css({"transform":"translate3d(0px,-230px,0)"});
		}
 		function chipinspeed_open(){
			if($(".chipin-gold").css("bottom")<"0px"){
			$(".chipin-gold").animate({
					bottom:"0px"
				},100,function(){
					$(".down-btn i").css({"transform":"rotate(0deg)","-webkit-transform":"rotate(0deg)"});
					$(".shopping-cart").css("bottom",$(".chipin-gold").outerHeight(true)+10+"px");
			});}
		} 
		function chipinspeed(){
			if($(".chipin-gold").css("bottom")>="0px"){
				$(".chipin-gold").animate({
					bottom:-($(".chipin-gold").outerHeight(true))+"px"
				},100,function(){
					$(".down-btn i").css({"transform":"rotate(180deg)","-webkit-transform":"rotate(180deg)"});
				});
				$(".shopping-cart").css("bottom","80px");
			}else{
				$(".chipin-gold").animate({
					bottom:"0px"
				},100,function(){
					$(".down-btn i").css({"transform":"rotate(0deg)","-webkit-transform":"rotate(0deg)"});
				});
				$(".shopping-cart").css("bottom",$(".chipin-gold").outerHeight(true)+10+"px");
			}
		}
window.addEventListener('load', function() {
    var initX;
    var moveX; 
    var X = 0; 
    var objX = 0;
	var num=80;
	var dist=40;
    window.addEventListener('touchstart', function(event) {
     // event.preventDefault();
      var obj = event.target.parentNode;
       if(obj.getAttribute("data-mv")=="move"){
		 $("#orderlist").attr("id","")
		 obj.setAttribute("id","orderlist");
		 if($("#orderlist").find(".removebtn").length>0){
			num =	$("#orderlist").find(".removebtn").width()*$("#orderlist").find(".removebtn").length;
			dist=num/2;
	  }else{
		  num=80;
	  }
		if(obj.parentNode.className=="flex-2"){
			num=40;
			dist=17;
		}
        initX = event.targetTouches[0].pageX;
        objX = (obj.style.WebkitTransform.replace(/translateX\(/g, "").replace(/px\)/g, "")) * 1;
      }
      if (objX == 0) {
        window.addEventListener('touchmove', function(event) {
          event.preventDefault();
          var obj = event.target.parentNode;
           if(obj.getAttribute("data-mv")=="move"){
            moveX = event.targetTouches[0].pageX;
            X = moveX - initX;
            if (X >= 0) {
              obj.style.WebkitTransform = "translateX(" + 0 + "px)";
            } else if (X < 0) {
              var l = Math.abs(X);
              obj.style.WebkitTransform = "translateX(" + -l + "px)";
              if (l > num) {
                l = num;
                obj.style.WebkitTransform = "translateX(" + -l + "px)";
              }
            }
          }
        });
      } else if (objX < 0) {
      window.addEventListener('touchmove', function(event) {
         event.preventDefault();
          var obj = event.target.parentNode;
           if(obj.getAttribute("data-mv")=="move"){
            moveX = event.targetTouches[0].pageX;
            X = moveX - initX;
            if (X >= 0) {
              var r = -num + Math.abs(X);
              obj.style.WebkitTransform = "translateX(" + r + "px)";
              if (r > 0) {
                r = 0;
                obj.style.WebkitTransform = "translateX(" + r + "px)";
              }
            } else { 
              obj.style.WebkitTransform = "translateX(" + -num + "px)";
            }
          }
        });
      }

    })
    window.addEventListener('touchend', function(event) {
    //  event.preventDefault();
      var obj = event.target.parentNode;
     if(obj.getAttribute("data-mv")=="move"){
        objX = (obj.style.WebkitTransform.replace(/translateX\(/g, "").replace(/px\)/g, "")) * 1;
		$("div[data-mv='move']").css("transform","translateX(0px)")
        if (objX > -dist) {
          obj.style.WebkitTransform = "translateX(" + 0 + "px)";
          objX = 0;
        } else {
          obj.style.WebkitTransform = "translateX(" + -num + "px)";
          objX = -num;
        }
		
      }
    })
  })