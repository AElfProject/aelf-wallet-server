/**
 @ Author: Curitis Niewei
 @ Date: 2014-8-6
 @ 2015-8-11
	添加了识别当前节点的功能，记忆之前裁剪范围功能，同时处理裁剪后图片的实时显示
	<input type="file" name="file1" accept="image/*" cropsize="要裁剪的尺寸，宽度用英文逗号分隔" />
	<a href="图片" id="cFile1" cropsize="要裁剪的尺寸，宽度用英文逗号分隔"><img src="图片" /></a>
 @ 2015-8-16
	添加了旋转和缩放功能，同时服务器端接收图片后的处理程序也统一了，更方便使用
*/

(function($)
{
	$.fn.crop = function (opt)
	{
		var _this	= $(this);
		var defa	= {
		}
		var opts	= $.fn.extend(defa, opt);
		var type	= _this.get(0).tagName.toLowerCase();
		var iptName	= type == 'input' ? 'bounds' : 'a_bounds';

		var ipt = _this.next('input[name="'+ iptName +'[]"]');
		if ( ipt.length == 0 ) {
			_this.after('<input type="hidden" name="'+ iptName +'[]" value="" />');
			ipt = _this.next('input[name="'+ iptName +'[]"]');
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
			if (type == 'input') {
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
			else if (type == 'a') {
				_this.click(function(){
					$.fn.cropOpenWindowToCrop(ipt, _this.attr('href'), width, height, _this);
					return false;
				});
			}
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
		var sourceObj = arguments[4] ? arguments[4] : null;

		$('body').append('<div id="crop-window"><div class="crop-window-wrapper"><div class="title"><h3>图片裁剪</h3><a href="javascript:;" class="close"></a></div><div class="content clearfix"><div class="big"><img id="crop-window-big" src="" /></div><div class="small"><img id="crop-window-small" src="" /></div></div><div class="btns"><span class="tool-btn"><i class="fa fa-rotate-left"></i>向左旋转</span><span class="tool-btn"><i class="fa fa-rotate-right"></i>向右旋转</span><span class="tool-btn"><i class="fa fa-minus"></i>缩小</span><span class="tool-btn"><i class="fa fa-plus"></i>放大</span><button type="button">保存修改</button></div></div></div>');
		$('#crop-window .crop-window-wrapper').css('top', $(window).scrollTop());
		$('#crop-window').height($(document).height());
		$('#crop-window-big').attr('src', data);
		$('#crop-window-small').attr('src', data).css('width', $('#crop-window-big').width()).parent().css({ 'width': width, 'height': height, 'overflow': 'hidden' });
		if ( $('#crop-window-small').parent().width() > 350 ) {
			$('#crop-window-small').parent().remove();
			$('#crop-window-big').parent().css('width', '100%')
		}
		$('#crop-window .close').click(function(){
			$.fn.cropOpenWindowClose(ipt, width, height, sourceObj, true);
			return false;
		});
		$('#crop-window .btns button').click(function(){
			$.fn.cropOpenWindowClose(ipt, width, height, sourceObj, false);
		});
		$('#crop-window .btns .tool-btn:eq(0)').click(function(){
			$.fn.cropOpenWindowTransform(ipt, 'rotate', 'left');
		});
		$('#crop-window .btns .tool-btn:eq(1)').click(function(){
			$.fn.cropOpenWindowTransform(ipt, 'rotate', 'right');
		});
		$('#crop-window .btns .tool-btn:eq(2)').click(function(){
			$.fn.cropOpenWindowTransform(ipt, 'scale', 'small');
		});
		$('#crop-window .btns .tool-btn:eq(3)').click(function(){
			$.fn.cropOpenWindowTransform(ipt, 'scale', 'big');
		});
		if (ipt.val() != '') $('#crop-window').attr('bounds', ipt.val());
		else $('#crop-window').attr('bounds', '');

		$('#crop-window-big').load(function(){
			var ow, oh, w, h, x1, y1, x2, y2, r1, s1;
			ow = $(this).width();
			oh = $(this).height();
			w = width;
			h = height;

			if ($('#crop-window').attr('bounds') != '') {
				var old = $('#crop-window').attr('bounds').split(',');
				x1 = old[2];
				y1 = old[3];
				x2 = old[4];
				y2 = old[5];
				r1 = parseInt(old[6]);
				s1 = parseFloat(old[7]);
				if (isNaN(r1)) r1 = 0;
				if (isNaN(s1)) s1 = 1;
			}
			else {
				x1 = (ow - w) / 2;
				y1 = 0;
				x2 = w + x1;
				y2 = y1 + h;
				r1 = 0;
				s1 = 1;
			}

			//ipt.val($('#crop-window-big').parent().width() + ',' + $('#crop-window-big').parent().height() + ',' + x1 + ',' + y1 + ',' + x2 + ',' + y2);
			$('#crop-window').attr('bounds', $('#crop-window-big').parent().width() + ',' + $('#crop-window-big').parent().height() + ',' + x1 + ',' + y1 + ',' + x2 + ',' + y2 + ',' + r1 + ',' + s1);

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
				$('#crop-window-big').next('.jcrop-holder').find('img').css({ 'transform': 'rotate('+ r1 +'deg) scale('+ s1 +')' });
				$('#crop-window-small').css({ 'transform': 'rotate('+ r1 +'deg) scale('+ s1 +')' });
			});
		});
		$.fn.cropOpenWindowToCropChange = function(c, ipt, boundx, boundy) {
			var r1 = 0, s1 = 1;
			if ($('#crop-window').attr('bounds') != '') {
				var old = $('#crop-window').attr('bounds').split(',');
				r1 = parseInt(old[6]);
				s1 = parseFloat(old[7]);
				if (isNaN(r1)) r1 = 0;
				if (isNaN(s1)) s1 = 1;
			}
			$('#crop-window').attr('bounds', $('#crop-window-big').parent().width() + ',' + $('#crop-window-big').parent().height() + ',' + c.x + ',' + c.y + ',' + c.x2 + ',' + c.y2 + ',' + r1 + ',' + s1);
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
		$.fn.cropOpenWindowClose = function(ipt, width, height, sourceObj, close) {
			if ( $('#crop-window .btns input').is(':checked') ) {
				ipt.val('');
			}
			else {
				if (close) {
					ipt.val('');
					if (sourceObj) {
						$(sourceObj).css({ 'display': 'inline', 'overflow': 'hidden', 'width': 'auto', 'height': 'auto' }).find('img').css({ 'width': 'auto', 'height': 'auto', 'margin': '0', 'transform': 'rotate(0deg) scale(1)' });
					}
				}
				else {
					ipt.val($('#crop-window').attr('bounds'));
					if ( ipt.val() == '' ) {
						alert('请设置图片裁剪范围');
						return false;
					}
					if (sourceObj) {
						var bounds = ipt.val().split(',');
						var cropWidth = parseInt(bounds[0]);
						var cropHeight = parseInt(bounds[1]);
						var cropX1 = parseInt(bounds[2]);
						var cropX2 = parseInt(bounds[4]);
						var cropY1 = parseInt(bounds[3]);
						var cropY2 = parseInt(bounds[5]);
						var rotate1 = parseInt(bounds[6]);
						var scale1 = parseFloat(bounds[7]);
						if (isNaN(cropWidth)) cropWidth = 0;
						if (isNaN(cropHeight)) cropHeight = 0;
						if (isNaN(cropX1)) cropX1 = 0;
						if (isNaN(cropX2)) cropX2 = 0;
						if (isNaN(cropY1)) cropY1 = 0;
						if (isNaN(cropY2)) cropY2 = 0;
						if (isNaN(rotate1)) rotate1 = 0;
						if (isNaN(scale1)) scale1 = 1;
						var cropBoxWidth = cropX2 - cropX1;
						var cropBoxHeight = cropY2 - cropY1;
						var marginTop = (width / cropWidth) * cropY1 * (cropWidth / cropBoxWidth);
						var marginLeft = (width / cropWidth) * cropX1 * (cropWidth / cropBoxWidth);
						var imgWidth = (cropWidth / cropBoxWidth) * width;
						var imgHeight = (cropHeight / cropBoxHeight) * height;
						$(sourceObj).css({ 'display': 'block', 'overflow': 'hidden', 'width': width, 'height': height }).find('img').css({ 'width': imgWidth, 'height': 'auto', 'margin': '-'+ marginTop +'px 0 0 -'+ marginLeft +'px', 'transform': 'rotate('+ rotate1 +'deg) scale('+ scale1 +')' });
					}
				}
			}
			$('#crop-window').remove();
		}
		$.fn.cropOpenWindowTransform = function(ipt, type, direction) {
			var x1, y1, x2, y2, r1, s1;
			if ($('#crop-window').attr('bounds') != '') {
				var old = $('#crop-window').attr('bounds').split(',');
				x1 = old[2];
				y1 = old[3];
				x2 = old[4];
				y2 = old[5];
				r1 = parseInt(old[6]);
				s1 = parseFloat(old[7]);
				if (isNaN(r1)) r1 = 0;
				if (isNaN(s1)) s1 = 1;
			}
			else {
				x1 = (ow - w) / 2;
				y1 = 0;
				x2 = w + x1;
				y2 = y1 + h;
				r1 = 0;
				s1 = 1;
			}
			if (type == 'rotate') {
				if (direction == 'left') r1 -= 5;
				else if (direction == 'right') r1 += 5;
			}
			else if (type == 'scale') {
				if (direction == 'small') {
					s1 -= 0.05;
					if (s1 <= 0.5) s1 = 0.5;
				}
				else if (direction == 'big') {
					s1 += 0.05;
					if (s1 >= 2) s1 = 2;
				}
			}
			$('#crop-window').attr('bounds', $('#crop-window-big').parent().width() + ',' + $('#crop-window-big').parent().height() + ',' + x1 + ',' + y1 + ',' + x2 + ',' + y2 + ',' + r1 + ',' + s1);
			$('#crop-window-big').next('.jcrop-holder').find('img').css({ 'transform': 'rotate('+ r1 +'deg) scale('+ s1 +')' });
			$('#crop-window-small').css({ 'transform': 'rotate('+ r1 +'deg) scale('+ s1 +')' });
		}
	}
})(jQuery);