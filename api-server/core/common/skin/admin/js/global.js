$(function(){
	
	$('#tableTab a').each(function(i){
		$(this).click(function(){
			$('#tableTab a.current').removeClass('current');
			$(this).addClass('current').click(function(){return false;});
			$('#panes > table:visible').hide();
			$('#panes > table').eq(i).show();
		});
	});

	//帮助提示
	var tips = $('em.tip');
	if (tips.length > 0)
	{
		$('body').append('<div id="tooltip" class="tooltip" style="display:none;"></div>');
		var tipbox = $('#tooltip');
		tips.each(function(i){
			$(this).hover(function(){
				tipbox.css({
					top		: $(this).offset().top + $(this).height() + 10,
					left	: $(this).offset().left
				}).html($(this).attr('tips')).show();
				if (tipbox.width() + tipbox.offset().left + 100 > $('body').width())
				{
					tipbox.hide().css({'left' : $('body').width() - 100 - tipbox.width()}).show();
				}
			}, function(){
				$('#tooltip').hide();
			});
		});
	}

	//ajax tips
	$('#loading').css({
		'top'		: $(document).scrollTop() + ($(window).height() - 32) / 2,
		'left'		: ($('body').width() - 160) / 2,
		'z-index'	: 22
	});
	$('#loading').bind('ajaxStart', function(){
		$(this).show().css({
			'top'		: $(document).scrollTop() + ($(window).height() - 32) / 2,
			'left'		: ($('body').width() - 160) / 2,
			'opacity'	: 1
		});
	}).bind('ajaxStop', function(){
		$(this).animate({
			'opacity'	: 0,
			'left'		: 0
		}, 500);
	});
});

function chkDelete ()
{
	return window.confirm('Are you sure you want to delete? This action can not be restored!');
}


function infoPic (id, tableName, idName, picName)
{
	var str	= "?con=admin&ctl=upload&act=pic&id=" + id + "&tableName=" + tableName + "&idName=" + idName + "&picName=" + picName;
	var p	= window.open(str, "pic", "width=750, height=500, scrollbars=1, left=100, top=100");
}

function infoFile (id, tableName, idName, picName)
{
	var str	= "?con=admin&ctl=upload&act=file&id=" + id + "&tableName=" + tableName + "&idName=" + idName + "&picName=" + picName;
	var p	= window.open(str, "pic", "width=750, height=500, scrollbars=1, left=100, top=100");
}












/*
drop down list
by niewei
2011-09-14
*/

(function($)
{
	$.fn.droplist = function (opt, callback, visibilityCallback)
	{
		var _this	= $(this);
		var defa	= {
			'maxWidth' : 0
		}
		var opts	= $.fn.extend(defa, opt);

		var _oldWidth = _this.width();
		_this.css('width', 'auto');
		var _width = _this.width();
		if (_oldWidth != _width) _width = _oldWidth;
		//create
		if ( ! $('.droplist-bg').get(0) ) {
			//$('body').append('<div class="droplist-bg"></div>');
		}
		var _dropBg	= $('.droplist-bg');
		_this.hide().after('<div class="droplist clearfix"></div>');
		var _dropObj= _this.next('div');
		var _ddt	= '<a href="#" class="droplist-default-text"><input type="text" /><b></b><span></span></a>';
		_dropObj.append(_ddt);
		_dropObj.append('<div class="droplist-list"><dl></dl></div>');
		var _ddtObj	= _dropObj.find('.droplist-default-text');
		var _listObj= _dropObj.find('.droplist-list');
		//var _listObj= _dropObj.find('dl');

		//assembly
		var _multiple = _this.attr('multiple');
		var _selected_option = _this.find('option:selected');
		_this.find('option').each(function(i){
			if ( $(this).attr('selected') ) {
				_ddtObj.attr('href', $(this).val()).attr('index', i).find('span').html($(this).html().replace(/&lt;/g, '<').replace(/&gt;/g, '>'));
			}
			var _child = parseInt( $(this).attr('child') );
			if ( isNaN( _child ) ) _child = 0;
			_listObj.find('dl').append('<dt class="item clearfix">'+ ( _multiple && _child == 0 ? '<input type="checkbox"'+ ( $(this).attr('selected') ? ' checked' : '' ) +' />' : '' ) +'<a href="'+ $(this).val() +'">'+ $(this).html().replace(/&lt;/g, '<').replace(/&gt;/g, '>') +'</a></dt>');
		});
		if (_this.attr('defa') != '') {
			_ddtObj.find('span').html(_this.attr('defa'));
		}
		if ( _multiple ) {
			var selectedLength = _this.find('option:selected').length;
			if ( selectedLength > 1 ) {
				_ddtObj.attr('href', '#').find('span').html('已选'+ selectedLength +'项');
			}
			else if ( selectedLength <= 0 ) {
				var _selected = _this.find('option').eq(0);
				if ( _selected.get(0) ) {
					_ddtObj.attr('href', _selected.val()).attr('index', 0).find('span').html(_selected.html().replace(/&lt;/g, '<').replace(/&gt;/g, '>'));
				}
			}
			_listObj.append('<div class="droplist-btns"><a href="#" class="close">X</a></div>');
		}
		_dropObj.css('width', _width + 20);
		if (opts.maxWidth > 0 && _width > opts.maxWidth) {
			_dropObj.css('width', opts.maxWidth);
		}
		//_ddtObj.css('width', _dropObj.width());
		if (_listObj.width() < _dropObj.width())
		{
			_listObj.css('width', _dropObj.width());
		}
		if (opts.maxWidth > 0 && _width > opts.maxWidth) {
			_listObj.css('width', _width);
		}
		//以下这段代码，可以自动处理宽度，_ddtObj这个需要在样式中添加右边箭头的宽度padding
		_dropObj.css('width', 'auto');
		//结束

		_dropBg.css('height', $(document).height());
		if ( _listObj.outerHeight(true) > 300 ) {
			_listObj.css('width', _listObj.outerWidth(false) + 20);
			_listObj.find('dl').css( { 'height' : 300, 'overflow' : 'auto'} );
		}

		function hide() {
			$(document).one('click', function(){
				var selectedLength = _listObj.find('input:checked').length;
				if ( selectedLength > 1 ) {
					_ddtObj.attr('href', '#').find('span').html('已选'+ selectedLength +'项');
				}
				else if ( selectedLength == 1 ) {
					var _selected = _listObj.find('input:checked').next();
					_ddtObj.attr('href', _selected.attr('href')).attr('index', 0).find('span').html(_selected.html().replace(/&lt;/g, '<').replace(/&gt;/g, '>'));
				}
				_listObj.hide();
				_ddtObj.find('b').removeClass('current');
				_listObj.parent().css('z-index', _listObj.parent().attr('zIndex'));
				visibilityCallback(_listObj);  //callback
			});
		}

		//init event
		_listObj.click(function(e){ e.stopPropagation(); });
		_ddtObj.click(function(){
			var _ind = parseInt(_ddtObj.attr('index'));
			_ddtObj.find('b').addClass('current');
			_listObj.find('.item a').removeClass('current').eq(_ind).addClass('current');
			if ( _listObj.get(0).style.display != 'block' ) {
				$('.droplist .droplist-list:visible').each(function(){
					$(this).hide();
					$(this).parent().css('z-index', $(this).parent().attr('zIndex'));
					visibilityCallback($(this));  //callback
				});
				_listObj.parent().attr('zIndex', _listObj.parent().css('z-index')).css('z-index', 999);
				_listObj.show();
				_dropBg.show();
				hide();
				_ddtObj.find('input').focus().unbind('keyup').keyup(function(e){
					var _val = $(this).val();
					$(this).val('');
					_val = _val.replace('/', '\/').replace('?', '\?').replace('*', '\*');
					var _test = eval("/^" + _val + "/gi");
					_listObj.find('.item a').each(function(i){
						if (_test.test($(this).html())) {
							_ddtObj.attr('href', $(this).attr('href')).attr('index', i).find('span').html($(this).html().replace(/&lt;/g, '<').replace(/&gt;/g, '>'));
							_listObj.find('.item input:checked').attr('checked', false);
							_listObj.find('.item input').eq(i).attr('checked', true);
							_listObj.find('a.current').removeClass('current');
							$(this).addClass('current');
							var _select = _dropObj.prev().get(0);
							_select.selectedIndex = _dropObj.find(' > a').attr('index');
							$(_select).change();
							return false;
						}
					});
				});
			}
			else {
				/*_listObj.hide();
				_dropBg.hide();
				_listObj.parent().css('z-index', _listObj.parent().attr('zIndex'));*/
			}
			visibilityCallback(_listObj);  //callback
			return false;
		}).focus(function(){
			$(this).blur();
		});
		_dropBg.click(function(){
			var selectedLength = _listObj.find('input:checked').length;
			if ( selectedLength > 1 ) {
				_ddtObj.attr('href', '#').find('span').html('已选'+ selectedLength +'项');
			}
			else if ( selectedLength == 1 ) {
				var _selected = _listObj.find('input:checked').next();
				_ddtObj.attr('href', _selected.attr('href')).attr('index', 0).find('span').html(_selected.html().replace(/&lt;/g, '<').replace(/&gt;/g, '>'));
			}
			_listObj.hide();
			_ddtObj.find('b').removeClass('current');
			$(this).hide();
			_listObj.parent().css('z-index', _listObj.parent().attr('zIndex'));
			visibilityCallback(_listObj);  //callback
			return false;
		});
		_listObj.find('.item a').each(function(i){
			$(this).click(function(){
				_ddtObj.attr('href', $(this).attr('href')).attr('index', i).find('span').html($(this).html().replace(/&lt;/g, '<').replace(/&gt;/g, '>'));
				_listObj.find('.item input:checked').attr('checked', false);
				_listObj.find('.item:eq('+i+') input').attr('checked', true);
				_listObj.hide();
				_ddtObj.find('b').removeClass('current');
				_dropBg.hide();
				_listObj.parent().css('z-index', _listObj.parent().attr('zIndex'));
				visibilityCallback(_listObj);  //callback
				return false;
			}).mouseover(function(){
				_listObj.find('.item a').removeClass('current');
				$(this).addClass('current');
			});
		});
		if ( _multiple ) {
			_listObj.find('.droplist-btns .close').click(function(){
				_dropBg.click();
				return false;
			});
		}

		//callback
		callback(_dropObj);
	}
})(jQuery);


function setDroplist(obj){
	var width = arguments[1] ? parseInt(arguments[1]) : 0;
	$(obj).droplist({}, function(dropObj){
		if (width > 0 && dropObj.find('.droplist-list .item').length > 1) dropObj.find('.droplist-list').css('width', width);
		dropObj.find('.droplist-list .item a').click(function(){
			var _select = dropObj.prev().get(0);
			var _input = $(this).prev('input');
			if ( $(_select).attr('multiple') && _input.length <= 0 ) {
				_select.selectedIndex = -1;
			}
			else {
				_select.selectedIndex = dropObj.find(' > a').attr('index');
				$(_select).change();
			}
		});
		dropObj.find('.droplist-list input').each(function(i){
			$(this).click(function(){
				var _select = dropObj.prev();
				if ( $(this).get(0).checked ) {
					_select.find('option[value="'+ $(this).next('a').attr('href') +'"]').attr('selected', true);
				}
				else {
					_select.find('option[value="'+ $(this).next('a').attr('href') +'"]').attr('selected', false);
				}
			});
		});
	}, function(listObj){});
}

$(function(){
	$('select').addClass('no-droplist');
	$('.editTable select').not('.no-droplist').each(function(){
		setDroplist($(this));
	});
	$('.search select').not('.no-droplist').each(function(){
		setDroplist($(this));
	});
});