
;(function($){
	var wst	= $(window).scrollTop(),
		wh	= $(window).height(),
		ww	= $(window).width();

	$(window).scroll(function(){
		wst	= $(window).scrollTop();
	});


	$.fn.extend({
		loading : function(options){
			var defaults = {
				hideTime: 3000,
				overTime: 120000,
				unit: true,
				loaded: function(){}
			};
			var settings = $.extend({}, defaults, options);
			this.each(function(){
				var _this = $(this),
					time = null,
					loadingValue = 0,

					loaded = function(){
						clearTimeout(time);
						_this.fadeOut(settings.hideTime, settings.loaded);
					},

					progress = function(){
						if(settings.unit){
							_this.find('.loading-txt').html(parseInt(loadingValue) + '%');
						}else{
							_this.find('.loading-txt').html(parseInt(loadingValue));
						}
					},

					backLoadingValue = function(){
						if(imgArray.length){
							loadingValue += (100 / imgArray.length);
						}else{
							loadingValue = 100;
						}
						progress();
						if(loadingValue >= 100 || loadingValue >= 99.9){
							loadingValue = 100;
							progress();
							loaded();
						}
					},

					imgArray = function(){
						var ay = [];
						$('body img').each(function(){
							ay.push($(this).attr('src'));
						});
						$('body .img-bg').each(function(){
							var bgSrc = $(this).css('background-image');
							if(bgSrc != 'none'){
								var re = /url\((.*)\)/;
								bgSrc = re.exec(bgSrc);
								ay.push(bgSrc[1].replace(/\"/g,''));
							}
						});
						return ay;
					},

					isLoad = function() {
						if (imgArray.length) {
							for (var i = 0; i < imgArray.length; i++) {
								var imgs = new Image();
								imgs.src = imgArray[i];
								if (imgs.complete) {
									backLoadingValue();
								} else {
									imgs.onload = function () {
										backLoadingValue();
									};
									imgs.onerror = function () {
										backLoadingValue();
									};
								}
							}
						} else {
							backLoadingValue();
						}
					};

				imgArray();
				isLoad();
				time = setTimeout(loaded, settings.overTime);
				window.onload = loaded;
			});
		},

		scrollActive : function(options){
			var defaults = {
				top: 30,
				className: 'active'
			};
			var settings = $.extend({}, defaults, options);
			this.each(function(){
				var _this = $(this);
				$(window).scroll(function(){
					if(wst > settings.top){
						_this.addClass(settings.className);
					}else{
						_this.removeClass(settings.className);
					}
				});
			});
			return this;
		},

		scrollTo : function(options){
			var defaults = {
				position: 0,
				speed : 3000
			}
			var settings = $.extend({}, defaults, options);
			this.each(function(){
				var _this = $(this);
				_this.bind('click', function(){
					$('body, html').stop(true).animate({
						'scrollTop' : settings.position
					}, settings.speed);
				});
			});
			return this;
		},

		star : function(){
			this.each(function(){
				var _this = $(this);
				var _thisIndex = -1;
				var changeStar = function(index){
					$(this).find('i').removeClass('fa-star').addClass('fa-star-o');
					for(var i=0; i<=index; i++){
						$(this).find('i').eq(i).removeClass('fa-star-o').addClass('fa-star');
					}
				}
				_this.find('i').each(function(index){
					var index = index;
					$(this).bind('click', function(){
						_thisIndex = index;
						_this.find('input').val(_thisIndex+1);
						changeStar.call(_this, index);
					});
					$(this).bind('mouseenter', function(){
						changeStar.call(_this, index);
					});
					$(this).bind('mouseleave', function(){
						changeStar.call(_this, _thisIndex);
					});
				});
			});
			return this;
		}
	});
})(jQuery);

$('.loading').loading();
$('.header').scrollActive();
$('.star').star();
$('.backTop').scrollTo({
	position : 0
});
$('.scroll-down').scrollTo({
	position : $(window).height()
});
