function oWindowShow (html)
{
	if (!$('#oWindow').get(0)) $('body').append('<div id="oWindow" class="oWindow"></div>');
	if (!$('#oWindowBg').get(0)) $('body').append('<div id="oWindowBg" class="oWindowBg"></div>');
	var oWin	= $('#oWindow');
	var oWinBg	= $('#oWindowBg');
	oWinBg.css({
		'height' : $(document).height()
	}).show();
	oWin.css({
		'left'	: ($('body').width() - 500) / 2,
		'top'	: $(document).scrollTop() + ($(window).height() - 260) / 2 + 100
	});
	oWin.show().animate({
		'top'		: $(document).scrollTop() + ($(window).height() - 260) / 2,
		'opacity'	: 1
	}, 100, 'linear', function(){
		$(this).load(html + '.htm');
	});

	oWinBg.click(function(){
		oWindowHide();
	});
}

function oWindowHide ()
{
	var oWin	= $('#oWindow');
	var oWinBg	= $('#oWindowBg');
	oWinBg.hide();
	oWin.html('').animate({
		'top'		: $(document).scrollTop() + ($(window).height() - 260) / 2 + 100,
		'opacity'	: 0
	}, 100, 'linear', function(){
		$(this).hide();
	});
	
}