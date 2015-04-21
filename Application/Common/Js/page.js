jQuery(function($){	
	$('.page_left_left').hover(
		function()
		{
			$(this).css("background-image","url(Application/Mall/Image/page_left_hover.png)");
		},
		function()
		{
			$(this).css("background-image","url(Application/Mall/Image/page_left.png)");
		});
	$('.page_right_right').hover(
		function()
		{
			$(this).css("background-image","url(Application/Mall/Image/page_right_hover.png)");
		},
		function()
		{
			$(this).css("background-image","url(Application/Mall/Image/page_right.png)");
		});
	$('.page_left').hover(
		function()
		{
			$(this).css("background-image","url(Application/Mall/Image/circle_left_hover.png)");
		},
		function()
		{
			$(this).css("background-image","url(Application/Mall/Image/circle_left.png)");
		});
	$('.page_right').hover(
		function()
		{
			$(this).css("background-image","url(Application/Mall/Image/circle_right_hover.png)");
		},
		function()
		{
			$(this).css("background-image","url(Application/Mall/Image/circle_right.png)");
		});
	$('.page_center').hover(
		function()
		{
			var th=$(this);
			th.css("background-image","url(Application/Mall/Image/circle_hover.png)");
		},
		function()
		{
			var th=$(this);
			if(th.children().html() != page_this)
			{
				th.css("background-image","url(Application/Mall/Image/circle.png)");
			}
		});

});