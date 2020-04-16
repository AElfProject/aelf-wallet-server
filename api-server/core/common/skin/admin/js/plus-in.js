jQuery.scrollto = function (obj, target, speed)
{
	if (obj == null) obj = (window.opera) ? (document.compatMode == "CSS1Compat" ? $('html') : $('body')) : $('html, body');
	obj.animate({scrollTop : target}, speed);
	return false;
}