function searchy(inputString) {
	if(inputString.length == 0) {
		$('#results').fadeOut();
   	} else {
    	$.post("/front/searchlimit", {search: ""+inputString+""}, function(data){
	       	$('#results').fadeIn();
	       	$('#results').html(data);
	    });
	}
}

$(document).ready(function() { 
	$('.dropdown-toggle').dropdown()
	$(".alert-message").alert();
	$('.genres li').tooltip({'placement':'bottom'});
	/*$("input.scoreinput").hide();*/
	$("#searchTable").tablesorter(); 
	$('span.scorevalue').click(function(){
		id = $(this).attr("id");
		$(this).hide();
		$('input.scoreinput[id='+id+']').show();
		$('input.scoreinput[id='+id+']').focus();
	});

	$('span.episodevalue').click(function(){
		id = $(this).attr("id");
		$(this).hide();
		$('input.episodeinput[id='+id+']').show();
		$('input.episodeinput[id='+id+']').focus();
	});
	/*
	 * $('a#login').click(function(){
		$('#loginForm').toggle('normal');
		$('#UserUsername').focus();
	});*/
	
	$('a#closelogin').click(function(){
		$('#loginForm').hide();
	});

	$('.episodestatus').click(function(e){
		e.preventDefault();
		var href = $(this).attr("href");
		var id = $(this).attr("id");
		$("img[id="+id+"]").attr('src','/img/loading.gif');
		
		$.get(href, function(data){
			var img = $("img[id="+id+"]");
			var a = $('a[id='+id+']');
			var newImage = (img.attr("src") == "/img/checked.png") ? "/img/unchecked.png" : "/img/checked.png";
			var newUrl = (a.attr("href").substring(0,15) == "/episode/watch/") ? "/episode/unwatch/"+id : "/episode/watch/"+id;
			a.attr("href", newUrl);
			img.attr("src",newImage);
			
		});
	});

	$('a.ajax').click(function(e){
		e.preventDefault();
		var href = $(this).attr("href");
		
		$(".ajaxresults").html('<img src="/img/loading.gif">');
		$.get(href, function(data){
			$(".ajaxresults").html(data);
		});
	});
	
}); 
