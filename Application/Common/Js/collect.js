jQuery(function($)
{
	var col=0;
	$('.heart').hover(
		function()
		{
			var src=$(this);
			if(src.attr('src')=="Application/Mall/Image/heart.png")
				col=1;
			else
			{
				$(this).attr('src','Application/Mall/Image/heart.png');
			}
		},
		function()
		{
			if(col==0)
			{
				$(this).attr('src','Application/Mall/Image/heart_grey.png');
			}
			else
			{
				col=0;
			}
		});

	$('.heart').click(
		function()	//点击之后收藏操作。
		{		
			var num=$(this).parents('.box').attr('id');
			if(num==undefined)
				num=$(this).parents('.goods_content').attr('id');

			if(col==0)
			{
				var url="./?m=mall&a=coll_ajax";
				var data={"goods_id":num};
				$.post(url,data,function(r){
					if(r.code >= 1)
					{
						var x=$('#'+num+' .col_num');
						noty({text: '收藏成功~~',layout: 'center', type: 'success',timeout:'1000',});
						x.html(x.html()*1 - (-1));
						col=1;
					}			
					else if(r.code == -1)
					{
						noty({text: '已收藏过，请勿重复收藏',layout: 'center', type: 'error',timeout:'1000',});
						col=1;
					}
					else if(r.code == 0)
						noty({text: '尚未登录，无法收藏',layout: 'center', type: 'error',timeout:'1000',});
				},'json');
			}
			else
			{
				noty({text: '已收藏过，请勿重复收藏',layout: 'center', type: 'success',timeout:'1000',});
			}
		});
});