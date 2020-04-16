/**
 @ Author: Curitis Niewei
 @ Date: 2014-8-6
*/

(function($)
{
	$.fn.crop = function (opt)
	{
		var _this	= $(this);
		var defa	= {
		}
		var opts	= $.fn.extend(defa, opt);

		var ipt = _this.next('input[name="bounds[]"]');
		if ( ipt.length == 0 ) {
			_this.after('<input type="hidden" name="bounds[]" value="" />');
			ipt = _this.next('input[name="bounds[]"]');
		}
		var fileData = '';
		var size = _this.attr('cropsize');
		//var size = '150,100';
		var width = 0, height = 0;
		if ( size ) {
			var width = parseInt(size.split(',')[0]);
			var height = parseInt(size.split(',')[1]);
			if ( isNaN(width) ) width = 0;
			if ( isNaN(height) ) height = 0;
		}
		if ( width > 0 && height > 0 ) {
			_this.change(function(){
				ipt.val('');
				if ( window.webkitURL && window.webkitURL.createObjectURL ) {
					fileData = window.webkitURL.createObjectURL(_this.get(0).files[0]);
					$.fn.cropOpenWindowToCrop(ipt, fileData, width, height);
				}
				else if ( $.browser.msie ) {
					if ( $.browser.version == 6 ) {
						fileData = _this.get(0).value;
						
					}
					else if ( $.browser.version == 7 || $.browser.version == 8 ) {
						_this.select();
						try {
							fileData = document.selection.createRange().text;
						}
						finally {
							document.selection.empty();
						}
						$.fn.cropOpenWindowToCrop(ipt, fileData, width, height);
					}
				}
				else {
					$.fn.cropReadFileAsBase64(_this.get(0).files[0], function(data){
						fileData = data;
						if ( fileData ) $.fn.cropOpenWindowToCrop(ipt, fileData, width, height);
					});
				}
			});
		}
	}
	$.fn.cropReadFileAsBase64 = function (file, fn) {
		if (!/image\/\w+/.test(file.type)) {
			fn(false);
			return false;
		}
		if ( typeof(FileReader) === 'undefined' ) {
			fn(false);
			return false;
		}
		var reader = new FileReader();
		reader.readAsDataURL(file);
		reader.onload = function(e) {
			fn(this.result);
		}
	}
	$.fn.cropOpenWindowToCrop = function (ipt, data, width, height) {
		if (data == '' || data == null || typeof(data) == 'undefined') return;

		$('body').append('<div id="crop-window"><div class="crop-window-wrapper"><div class="title"><h3>图片裁剪</h3><a href="javascript:;" class="close"></a></div><div class="content clearfix"><div class="big"><img id="crop-window-big" src="" /></div><div class="small"><img id="crop-window-small" src="" /></div></div><div class="btns"><input type="checkbox" id="just-scale-and-fill" /><label for="just-scale-and-fill">不裁剪，仅缩放尺寸后补白 &nbsp; </label><button type="button">保存修改</button></div></div></div>');
		$('#crop-window .crop-window-wrapper').css('top', $(window).scrollTop());
		$('#crop-window').height($(document).height());
		$('#crop-window-big').attr('src', data);
		$('#crop-window-small').attr('src', data).css('width', $('#crop-window-big').width()).parent().css({ 'width': width, 'height': height, 'overflow': 'hidden' });
		if ( $('#crop-window-small').parent().width() > 350 ) {
			$('#crop-window-small').parent().remove();
			$('#crop-window-big').parent().css('width', '100%')
		}
		$('#crop-window .close').click(function(){
			if ( $('#crop-window .btns input').is(':checked') ) {
				ipt.val('');
			}
			else {
				ipt.val($('#crop-window').attr('bounds'));
				if ( ipt.val() == '' ) {
					alert('请设置图片裁剪范围');
					return false;
				}
			}
			$(this).parent().parent().parent().remove();
			return false;
		});
		$('#crop-window .btns button').click(function(){
			if ( $('#crop-window .btns input').is(':checked') ) {
				ipt.val('');
			}
			else {
				ipt.val($('#crop-window').attr('bounds'));
				if ( ipt.val() == '' ) {
					alert('请设置图片裁剪范围');
					return false;
				}
			}
			$(this).parent().parent().parent().remove();
		});
		$('#crop-window-big').load(function(){
			var ow, oh, w, h, x1, y1, x2, y2;
			ow = $(this).width();
			oh = $(this).height();
			w = width;
			h = height;

			x1 = (ow - w) / 2;
			y1 = 0;
			x2 = w + x1;
			y2 = y1 + h;

			//ipt.val($('#crop-window-big').parent().width() + ',' + $('#crop-window-big').parent().height() + ',' + x1 + ',' + y1 + ',' + x2 + ',' + y2);
			$('#crop-window').attr('bounds', $('#crop-window-big').parent().width() + ',' + $('#crop-window-big').parent().height() + ',' + x1 + ',' + y1 + ',' + x2 + ',' + y2);

			var api, boundx, boundy;
			$(this).Jcrop({
				allowSelect: false,
				allowResize: true,
				aspectRatio: width / height,
				setSelect: [x1, y1, x2, y2],
				onChange: function(c){
					$.fn.cropOpenWindowToCropChange(c, ipt, boundx, boundy);
				},
				onSelect: function(c){
					$.fn.cropOpenWindowToCropChange(c, ipt, boundx, boundy);
				}
			}, function(){
				var bounds = this.getBounds();
				boundx = bounds[0];
				boundy = bounds[1];
				api = this;
			});
		});
		$.fn.cropOpenWindowToCropChange = function(c, ipt, boundx, boundy) {
			$('#crop-window').attr('bounds', $('#crop-window-big').parent().width() + ',' + $('#crop-window-big').parent().height() + ',' + c.x + ',' + c.y + ',' + c.x2 + ',' + c.y2);
			if (parseInt(c.w) > 0 && $('#crop-window-small').length != 0) {
				var rx = $('#crop-window-small').parent().width() / c.w;
				var ry = $('#crop-window-small').parent().height() / c.h;
				$('#crop-window-small').css({
					'width': Math.round(rx * boundx),
					'height': Math.round(ry * boundy),
					'margin-left': '-' + Math.round(rx * c.x) + 'px',
					'margin-top': '-' + Math.round(ry * c.y) + 'px'
				});
			};
		}
	}
})(jQuery);