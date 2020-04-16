$(function() {
	$("dt").click(function() {
		$(this).toggleClass("current").next("dd").slideToggle("fast");
		$(this).siblings("dt").next("dd").slideUp("fast");
		$(this).siblings("dt").removeClass("current");
		$(this).children("a").blur();
	});

	$("li a").click(function() {
		$("li a").removeClass("current");
		$("a.current").removeClass("current");
		$(this).addClass("current").blur();
	});
});