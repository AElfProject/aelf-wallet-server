/*
jquery
*/

function ccWindow (id, classname)
{

	this.id			= id;
	this.classname	= classname;
	this.width		= 0;
	this.height		= 0;
	this.move		= false;
	this.title		= '';
	this.background	= '#D8E6F7';
	this.border		= '1px solid #D8E6F7';
	this.boxShadow	= '2px 2px 11px #666666';

	this.create = function ()
	{
		if (!document.getElementById(this.id))
		{
			$('body').append('<div id="'+ this.id +'" class="'+ this.classname +'"></div><div id="'+ this.id +'_bg" class="'+ this.classname +'_bg"></div>');
		}
		$('#'+ this.id).show().css({
			'width'		: this.width,
			'height'	: this.height,
			'background': this.background,
			'border'	: this.border,
			'box-shadow': this.boxShadow,
			'position'	: 'absolute',
			'left'		: ($('body').width() - this.width) / 2,
			'top'		: $(document).scrollTop() + ($(window).height() - this.height) / 2,
			'z-index'	: 20
		});
		$('#'+ this.id +'_bg').show().css({
			'position'	: 'absolute',
			'background': '#CCC',
			'opacity'	: 0.8,
			'left'		: 0,
			'top'		: 0,
			'height'	: $(document).height(),
			'width'		: '100%',
			'z-index'	: 19
		});

		var _this = this;
		$('#'+ this.id +'_bg').click(function(){
			$(this).hide();
			$('#'+ _this.id).fadeOut(500);
		});
	}

}