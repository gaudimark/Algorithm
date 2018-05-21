/* 下拉加载 */
var myScroll,
	pullDownEl, pullDownOffset,
	pullUpEl, pullUpOffset,
	generatedCount = 0;
	var dg=0;

/**
 * 下拉刷新 */
function pullDownAction () {
	setTimeout(function () {	
		var postParam = $("#postParam").val();
		var postUrl = $("#postUrl").val();
		if(postParam === null || postParam == undefined ){
			window.location.reload();
		}else{
			//去掉分页信息
			postParam = postParam.replace(/(&page=\d+|page=\d+)/g,'');
			$.ajax({
				type:'post',
				url:postUrl,
				data:postParam,
				success:function(ret){
					//$("#thelist").empty();
					//$("#thelist").append(ret.data);
					//$(".pullUpLabel").show();
	                myScroll.refresh();
	                if($(".trade-meanwhile").length>0){
	                	slipping();
	                }
	                //$("#hasNextPages").val(ret.msg.hasNextPages);
	                //$("#postParam").val(ret.msg.newParam);
					scorelist();
	                clear();
	                slidebox();
					
				}
			});
		}
		
	}, 10);	
}
/*
 滚动翻页 
 */
function pullUpAction () {
	setTimeout(function () {
		var el, li, i;
		el = document.getElementById('thelist');
		var hasNextPages = $("#hasNextPages").val();
		if(hasNextPages == 0){
			$(".pullUpLabel").html("没有更多");
		}else{
			var postParam = $("#postParam").val();
			var postUrl = $("#postUrl").val();
			if(dg=="1"){
				$.ajax({
					type:'post',
					url:postUrl,
					data:postParam,
					success:function(ret){
						var container = extractContainer(ret,'#thelist');
						$(container.content).appendTo($('#thelist'));
		                myScroll.refresh();
		                if($(".trade-meanwhile").length>0){
		                	slipping();
		                }
						$("#hasNextPages").val(container.hasNextPages);
						$("#postParam").val(container.postParam);
						$("#postUrl").val(container.postUrl);
						scorelist();
		                clear();
		                slidebox();
						dg=0;
					}
				});
			}
		}
				
	}, 10);	
}

/**
 * 初始化iScroll控件
 */
function loaded() {
	if($("#pullDown").length>0){
	pullDownEl = document.getElementById('pullDown');
	pullDownOffset = pullDownEl.offsetHeight;
	if($("#pullUp").length>0){
		pullUpEl = document.getElementById('pullUp');	
		pullUpOffset = pullUpEl.offsetHeight;
	}
	myScroll = new iScroll('wrapper', {
		hScroll:false,vScrollbar:false,hScrollbar:false,useTransition:false,
		scrollbarClass: 'myScrollbar', 
		useTransition: false, 
		topOffset: pullDownOffset,
		onRefresh: function () {
			if (pullDownEl.className.match('loading')) {
				pullDownEl.className = '';
				pullDownEl.querySelector('.pullDownLabel').innerHTML = '下拉刷新...';
			} 
			if($("#pullUp").length>0){
			var hasNextPages = $("#hasNextPages").val();
			if(hasNextPages == 0){
				pullUpEl.querySelector('.pullUpLabel').innerHTML = '没有更多';
				return false;
			}
			 if (pullUpEl.className.match('loading')) {
				pullUpEl.className = '';
				pullUpEl.querySelector('.pullUpLabel').innerHTML = '上拉加载更多...';
			}}
		},
		onScrollMove: function () {
				pullUpEl.style.display="block";
			if (this.y > 5 && !pullDownEl.className.match('flip')) {
				pullDownEl.className = 'flip';
				pullDownEl.querySelector('.pullDownLabel').innerHTML = '松开更新...';
				this.minScrollY = 0;
			} else if (this.y < 5 && pullDownEl.className.match('flip')) {
				pullDownEl.className = '';
				pullDownEl.querySelector('.pullDownLabel').innerHTML = '下拉刷新...';
				this.minScrollY = -pullDownOffset;
			}
			if($("#pullUp").length>0){
			var hasNextPages = $("#hasNextPages").val();
			if(hasNextPages == 0){
				pullUpEl.querySelector('.pullUpLabel').innerHTML = '没有更多';
				pullUpEl.style.display="none";
				return false;
			}
			if (this.y < (this.maxScrollY - 5) && !pullUpEl.className.match('flip')) {
				pullUpEl.className = 'flip';
				pullUpEl.querySelector('.pullUpLabel').innerHTML = '松手开始更新...';
				this.maxScrollY = this.maxScrollY;
			} else if (this.y > (this.maxScrollY + 5) && pullUpEl.className.match('flip')) {
				pullUpEl.className = '';
				pullUpEl.querySelector('.pullUpLabel').innerHTML = '上拉加载更多...';
				this.maxScrollY = pullUpOffset;
			}
			}
		},
		onScrollEnd: function () {
			if (pullDownEl.className.match('flip')) {
				pullDownEl.className = 'loading';
				pullDownEl.querySelector('.pullDownLabel').innerHTML = '加载中...';				
				pullDownAction();
			}
			if($("#pullUp").length>0){
				if (pullUpEl.className.match('flip')) {
					dg +=1;
					pullUpEl.className = 'loading';
					pullUpEl.querySelector('.pullUpLabel').innerHTML = '加载中...';				
					
					pullUpAction();	// Execute custom function (ajax call?)
				}
			}
		}
	});
}else{myScroll = new iScroll('wrapper', {hScroll:false,vScrollbar:false,hScrollbar:false})}
}
	setTimeout(function () { document.getElementById('wrapper').style.left = '0'; }, 800);
//初始化绑定iScroll控件 
document.addEventListener('touchmove', function (e) { e.preventDefault(); }, false);
document.addEventListener('DOMContentLoaded', loaded, false); 

//全选
function checkAllMatch(){
	$(".checkbox,.checkbox-index").each(function(){
		$(this).attr("class","checkbox checked");
		$(this).find("ins").attr("class","fa fa-check-circle-o");
	});
}

//反选
function reverseCheckAllMatch(){
	$(".checkbox,.checkbox-index").each(function(){
		if($(this).attr("class") == "checkbox checked"){
			$(this).attr("class","checkbox");
			$(this).find("ins").attr("class","");
		}else{
			$(this).attr("class","checkbox checked");
			$(this).find("ins").attr("class","fa fa-check-circle-o");
		}
	});
}


function parseHTML(html) {
	return $.parseHTML(html, document, true)
}

function findAll(elems, selector) {
	return elems.filter(selector).add(elems.find(selector));
}

function extractContainer(data, fragment) {
	var fullDocument = /<html/i.test(data);
	var obj = {};
	if (fullDocument) {
		var $head = $(parseHTML(data.match(/<head[^>]*>([\s\S.]*)<\/head>/i)[0]))
		var $body = $(parseHTML(data.match(/<body[^>]*>([\s\S.]*)<\/body>/i)[0]))
	} else {
		var $head = $body = $(parseHTML(data))
	}
	var $fragment = findAll($body, fragment).first();
	obj.content = $fragment.contents();
	obj.hasNextPages = $body.find("#hasNextPages").val();
	obj.postUrl = $body.find("#postUrl").val();
	obj.postParam = $body.find("#postParam").val();
	return obj;
}
