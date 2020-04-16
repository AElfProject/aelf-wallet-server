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
				_ddtObj.attr('href', $(this).val()).attr('index', i).find('span').html($(this).html());
			}
			_listObj.find('dl').append('<dt class="item clearfix">'+ ( _multiple ? '<input type="checkbox"'+ ( $(this).attr('selected') ? ' checked' : '' ) +' />' : '' ) +'<a href="'+ $(this).val() +'">'+ $(this).html() +'</a></dt>');
		});
		if ( _multiple ) {
			var selectedLength = _this.find('option:selected').length;
			if ( selectedLength > 1 ) {
				_ddtObj.attr('href', '#').find('span').html('宸查€�'+ selectedLength +'椤�');
			}
			else if ( selectedLength <= 0 ) {
				var _selected = _this.find('option').eq(0);
				if ( _selected.get(0) ) {
					_ddtObj.attr('href', _selected.val()).attr('index', 0).find('span').html(_selected.html());
				}
			}
			_listObj.append('<div class="droplist-btns"><a href="#" class="close">X</a></div>');
		}
		_dropObj.css('width', _width);
		if (opts.maxWidth > 0 && _width > opts.maxWidth) {
			_dropObj.css('width', opts.maxWidth);
		}
		_ddtObj.css('width', _dropObj.width());
		if (_listObj.width() < _dropObj.width())
		{
			_listObj.css('width', _dropObj.width());
		}
		if (opts.maxWidth > 0 && _width > opts.maxWidth) {
			_listObj.css('width', _width);
		}
		_dropBg.css('height', $(document).height());
		if ( _listObj.outerHeight(true) > 300 ) {
			_listObj.css('width', _listObj.outerWidth(false) + 20);
			_listObj.find('dl').css( { 'height' : 300, 'overflow' : 'auto'} );
		}

		function hide() {
			$(document).one('click', function(){
				var selectedLength = _listObj.find('input:checked').length;
				if ( selectedLength > 1 ) {
					_ddtObj.attr('href', '#').find('span').html('宸查€�'+ selectedLength +'椤�');
				}
				else if ( selectedLength == 1 ) {
					var _selected = _listObj.find('input:checked').next();
					_ddtObj.attr('href', _selected.attr('href')).attr('index', 0).find('span').html(_selected.html());
				}
				_listObj.hide();
				_listObj.parent().css('z-index', _listObj.parent().attr('zIndex'));
				//visibilityCallback(_listObj);  //callback
			});
		}

		//init event
		_listObj.click(function(e){ e.stopPropagation(); });
		_ddtObj.click(function(){
			var _ind = parseInt(_ddtObj.attr('index'));
			_listObj.find('.item a').removeClass('current').eq(_ind).addClass('current');
			if ( _listObj.get(0).style.display != 'block' ) {
				$('.droplist .droplist-list:visible').each(function(){
					$(this).hide();
					$(this).parent().css('z-index', $(this).parent().attr('zIndex'));
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
							_ddtObj.attr('href', $(this).attr('href')).attr('index', i).find('span').html($(this).html());
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
				_ddtObj.attr('href', '#').find('span').html('宸查€�'+ selectedLength +'椤�');
			}
			else if ( selectedLength == 1 ) {
				var _selected = _listObj.find('input:checked').next();
				_ddtObj.attr('href', _selected.attr('href')).attr('index', 0).find('span').html(_selected.html());
			}
			_listObj.hide();
			$(this).hide();
			_listObj.parent().css('z-index', _listObj.parent().attr('zIndex'));
			visibilityCallback(_listObj);  //callback
			return false;
		});
		_listObj.find('.item a').each(function(i){
			$(this).click(function(){
				_ddtObj.attr('href', $(this).attr('href')).attr('index', i).find('span').html($(this).html());
				_listObj.find('.item input:checked').attr('checked', false);
				_listObj.find('.item input').eq(i).attr('checked', true);
				_listObj.hide();
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
			_select.selectedIndex = dropObj.find(' > a').attr('index');
			$(_select).change();
		});
		dropObj.find('.droplist-list input').each(function(i){
			$(this).click(function(){
				var _select = dropObj.prev();
				if ( $(this).get(0).checked ) {
					_select.find('option').eq(i).attr('selected', true);
				}
				else {
					_select.find('option').eq(i).attr('selected', false);
				}
			});
		});
	}, function(listObj){});
}

$(function(){
	$('.editTable select').each(function(){
		setDroplist($(this));
	});
});