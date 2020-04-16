$(function(){
	$('#exportBtn').click(function(){
		$('#listForm').attr('action', '?con=admin&ctl=system/api').submit();
	});
});