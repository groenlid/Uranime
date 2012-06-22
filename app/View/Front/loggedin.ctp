<div class="row">
	<div class="span12">
<?php

if(count($episodes) != 0)
	echo '<h2 class="calendar">Episodes airing today <small><span class="smaller subtle pull-right"><a href="/calendar/view">see more</a></span></small></h2><div id="anime-gallery">';
foreach($episodes as $episode)
{

	$fanart = $episode['Anime']['fanart'];
	if($fanart == "" || $fanart == null)
		$fanart = "http://placehold.it/200x112/";
	else
		$fanart = SERVER_PATH . IMAGE_PATH . $fanart;

	$timeStr = strtotime($episode['Episode']['aired']);
		

		echo '<div class="anime-gallery-single">
			<div class="anime-gallery-single-inner">'.
		     	'<a href="/anime/view/'.$episode['Anime']['id'].'/'.$episode['Anime']['title'].'">';
		if($episode['Anime']['fanart'] != null)
			echo '<img src="http://src.sencha.io/200/'.$fanart.'">';
		else
			echo '<img src="http://placehold.it/200x112">';
		echo '</a>'.
			'<span class="anime-gallery-single-hover"><a href="/episode/view/'.$episode['Episode']['id'].'/">View Episode</a></span>'.
			'</div>
			<p class="bold calendarinfo"><a href="/anime/view/'.$episode['Anime']['id'].'/'.$episode['Anime']['title'].'">'.$episode['Anime']['title']. '</a> ' . $episode['Episode']['number'] . '</p>
			<p class="calendarinfo">'. $this->Text->truncate($episode['Episode']['name'],35).'</p>
			</div>';	
}
if(count($episodes) != 0)
	echo '<br class="clear"></div>';
?>
</div>
</div>

<div class="row">
<div class="span7">
<h2>What are people watching right now?</h2>
<div id="newsfeed">
	<img src="/img/loading2.gif">
</div>
</div>

<script type="text/javascript">
	//getInitialNewsFeed();
	var last = 0;
	var amount = 0;
	var startAmount = 10;
	localStorage.clear();
	setInterval("getNewsFeed()",5000);

	function decider(){
		if(last == 0)
			getInitialNewsFeed();
		else
			getNewsFeed();
	}

	function getNewsFeed(){
		var url = "";
		if(last == 0)
			url = "/api/lastseen/"+startAmount+".json";
		else
			url = '/api/lastseenAfter/'+last+'.json';
		$.getJSON(url, function(data) {
		  var items = [];
		  $.each(data.data, function(key2, val2) {
		  	$.each(val2, function(key, val) {
				/* Show one -> hide one */
				console.log(key);
				if(amount == 0)
					$('#newsfeed').html(activityItem(val,false));

				else if(amount >= startAmount){
					$('#newsfeed').prepend(activityItem(val,true));
						$('#newsfeed .newsfeed:first-child').slideDown('slow');
					$('#newsfeed .newsfeed:last-child').slideUp('slow',function(){
						$('#newsfeed .newsfeed:last-child').remove();				
					});
				}
				else
					$('#newsfeed').append(activityItem(val,false));
				amount += 1;
		    		//items.push(activityItem(val));
		  	});
		  });
		  //$('#newsfeed').prepend(items.join(''));

		});
	}

	function activityItem(item,append){
		var printable = "";
		last = (item.UserEpisode.id > last)? item.UserEpisode.id : last;
		if(append == true)
			printable += "<div style='display:none' class='row newsfeed'>";
		else
			printable += "<div class='row newsfeed'>";
		// Get usernick from username
		var subject_name = "";
		var object_name = "";
		var verb = new Array();
		verb['added'] = ' added ';
		verb['watched'] = ' watched ';

		// First we find the subject
		printable += "<div class='span1'>";

			subject_name = "<a href='/user/view/"+item.User.id+"'><strong>" + item.User.nick + "</strong></a>";
			printable += "<img src='"+get_gravatar(item.User.email,50)+"'>";

		printable += "</div>";
		
		// Then we do the 

		var anime;
		var episodeData;

		printable += "<div class='span4'><p>";
		var statusText = "";
		
		episodeData = getEpisodeInfo(item.Episode.id);
		anime = getAnimeInfo(episodeData.data.episode.anime_id);
		statusText = ' <strong><a href="/episode/view/' + episodeData.data.episode.id + '">episode ' + episodeData.data.episode.number + '</a></strong> of <strong><a href="/anime/view/'+anime.id+'">' + anime.title + '</a></strong>';
		/*switch(item.Activity.object_type)
		{
			case "fanart":
				anime = getAnimeInfo(item.Activity.object_id);
				statusText = ' fanart on anime <strong><a href="/anime/view/'+item.Activity.object_id+'">' + anime.title + '</a></strong>';
			break;
			case "image":
				anime = getAnimeInfo(item.Activity.object_id);
				statusText = ' image on anime <strong><a href="/anime/view/'+item.Activity.object_id+'">' + anime.title + '</a></strong>';
			break;
			case "episode":
				episodeData = getEpisodeInfo(item.Activity.object_id);
				anime = getAnimeInfo(episodeData.data.episode.anime_id);
				statusText = ' <strong><a href="/episode/view/' + episodeData.data.episode.id + '">episode ' + episodeData.data.episode.number + '</a></strong> of anime <strong><a href="/anime/view/'+anime.id+'">' + anime.title + '</a></strong>';
			break;
			case "reference":
				anime = getAnimeInfo(item.Activity.object_id);
				statusText = ' a reference for '+item.Activity.option+' for anime  <strong><a href="/anime/view/'+item.Activity.object_id+'">' + anime.title + '</a></strong>';
			break;
			case "anime":
				anime = getAnimeInfo(item.Activity.object_id);
				statusText = ' a spear through the whale\'s blowhole and added a new anime <strong><a href="/anime/view/'+item.Activity.object_id+'">' + anime.title + '</a></strong>';
			break;
		}*/

		printable += subject_name + ' watched ' + statusText;
		//printable += "</p><p class='subtle'>"+jQuery.timeago(item.UserEpisode.timestamp)+"</p></div>";
		printable += "</p><p class='subtle'><abbr class='timeago' title='"+item.UserEpisode.timestamp+"'>"+jQuery.timeago(item.UserEpisode.timestamp)+"</abbr></p></div>";
		
		printable += "<div class='span1'>";
		printable += "<img src='http://src.sencha.io/150/50/http://urani.me/attachments/photos/orginal/"+anime.fanart+"'>"
		printable += "</div>";
		
		printable += "</div>";
		
		return printable;
	}

	function getAnimeInfo(id){
		//return	synchronousAJAX("/api/anime/" + id +".json").data.anime;
		// This is not used anymore
		var retrievedObject = localStorage.getItem('anime_'+id);
		//var result;
		if(retrievedObject == undefined)
		{
			var animeData = synchronousAJAX("/api/anime/" + id +".json").data.anime;
			localStorage.setItem('anime_'+id, JSON.stringify(animeData));
			result = animeData;
		}
		else
			result = JSON.parse(retrievedObject);
		return result;
	}

	function getEpisodeInfo(id){
		//return synchronousAJAX("/api/episode/" + id +".json");
		// This is not used anymore
		var retrievedObject = localStorage.getItem('episode_'+id);
		//var result;
		if(retrievedObject == undefined)
		{
			var episodeData = synchronousAJAX("/api/episode/" + id +".json");
			localStorage.setItem('episode_'+id, JSON.stringify(episodeData));
			result = episodeData;
		}
		else
			result = JSON.parse(retrievedObject);
		return result;
	}

	function synchronousAJAX(url){
		var returns = "";
		$.ajax({  
		  url: url,  
		  dataType: 'json',    
		  async: false,  
		  success: function(json){  
		                  returns = json;
		               }  
		});  
		return returns;
	}

	function synchronousGET(url){
		var returns = "";
		$.ajax({  
		  url: url,     
		  async: false,  
		  success: function(json){  
		                  returns = json;
		               }  
		});  
		return returns;
	}

	function get_gravatar(email, size) {

    // MD5 (Message-Digest Algorithm) by WebToolkit
    // http://www.webtoolkit.info/javascript-md5.html

    var MD5=function(s){function L(k,d){return(k<<d)|(k>>>(32-d))}function K(G,k){var I,d,F,H,x;F=(G&2147483648);H=(k&2147483648);I=(G&1073741824);d=(k&1073741824);x=(G&1073741823)+(k&1073741823);if(I&d){return(x^2147483648^F^H)}if(I|d){if(x&1073741824){return(x^3221225472^F^H)}else{return(x^1073741824^F^H)}}else{return(x^F^H)}}function r(d,F,k){return(d&F)|((~d)&k)}function q(d,F,k){return(d&k)|(F&(~k))}function p(d,F,k){return(d^F^k)}function n(d,F,k){return(F^(d|(~k)))}function u(G,F,aa,Z,k,H,I){G=K(G,K(K(r(F,aa,Z),k),I));return K(L(G,H),F)}function f(G,F,aa,Z,k,H,I){G=K(G,K(K(q(F,aa,Z),k),I));return K(L(G,H),F)}function D(G,F,aa,Z,k,H,I){G=K(G,K(K(p(F,aa,Z),k),I));return K(L(G,H),F)}function t(G,F,aa,Z,k,H,I){G=K(G,K(K(n(F,aa,Z),k),I));return K(L(G,H),F)}function e(G){var Z;var F=G.length;var x=F+8;var k=(x-(x%64))/64;var I=(k+1)*16;var aa=Array(I-1);var d=0;var H=0;while(H<F){Z=(H-(H%4))/4;d=(H%4)*8;aa[Z]=(aa[Z]|(G.charCodeAt(H)<<d));H++}Z=(H-(H%4))/4;d=(H%4)*8;aa[Z]=aa[Z]|(128<<d);aa[I-2]=F<<3;aa[I-1]=F>>>29;return aa}function B(x){var k="",F="",G,d;for(d=0;d<=3;d++){G=(x>>>(d*8))&255;F="0"+G.toString(16);k=k+F.substr(F.length-2,2)}return k}function J(k){k=k.replace(/rn/g,"n");var d="";for(var F=0;F<k.length;F++){var x=k.charCodeAt(F);if(x<128){d+=String.fromCharCode(x)}else{if((x>127)&&(x<2048)){d+=String.fromCharCode((x>>6)|192);d+=String.fromCharCode((x&63)|128)}else{d+=String.fromCharCode((x>>12)|224);d+=String.fromCharCode(((x>>6)&63)|128);d+=String.fromCharCode((x&63)|128)}}}return d}var C=Array();var P,h,E,v,g,Y,X,W,V;var S=7,Q=12,N=17,M=22;var A=5,z=9,y=14,w=20;var o=4,m=11,l=16,j=23;var U=6,T=10,R=15,O=21;s=J(s);C=e(s);Y=1732584193;X=4023233417;W=2562383102;V=271733878;for(P=0;P<C.length;P+=16){h=Y;E=X;v=W;g=V;Y=u(Y,X,W,V,C[P+0],S,3614090360);V=u(V,Y,X,W,C[P+1],Q,3905402710);W=u(W,V,Y,X,C[P+2],N,606105819);X=u(X,W,V,Y,C[P+3],M,3250441966);Y=u(Y,X,W,V,C[P+4],S,4118548399);V=u(V,Y,X,W,C[P+5],Q,1200080426);W=u(W,V,Y,X,C[P+6],N,2821735955);X=u(X,W,V,Y,C[P+7],M,4249261313);Y=u(Y,X,W,V,C[P+8],S,1770035416);V=u(V,Y,X,W,C[P+9],Q,2336552879);W=u(W,V,Y,X,C[P+10],N,4294925233);X=u(X,W,V,Y,C[P+11],M,2304563134);Y=u(Y,X,W,V,C[P+12],S,1804603682);V=u(V,Y,X,W,C[P+13],Q,4254626195);W=u(W,V,Y,X,C[P+14],N,2792965006);X=u(X,W,V,Y,C[P+15],M,1236535329);Y=f(Y,X,W,V,C[P+1],A,4129170786);V=f(V,Y,X,W,C[P+6],z,3225465664);W=f(W,V,Y,X,C[P+11],y,643717713);X=f(X,W,V,Y,C[P+0],w,3921069994);Y=f(Y,X,W,V,C[P+5],A,3593408605);V=f(V,Y,X,W,C[P+10],z,38016083);W=f(W,V,Y,X,C[P+15],y,3634488961);X=f(X,W,V,Y,C[P+4],w,3889429448);Y=f(Y,X,W,V,C[P+9],A,568446438);V=f(V,Y,X,W,C[P+14],z,3275163606);W=f(W,V,Y,X,C[P+3],y,4107603335);X=f(X,W,V,Y,C[P+8],w,1163531501);Y=f(Y,X,W,V,C[P+13],A,2850285829);V=f(V,Y,X,W,C[P+2],z,4243563512);W=f(W,V,Y,X,C[P+7],y,1735328473);X=f(X,W,V,Y,C[P+12],w,2368359562);Y=D(Y,X,W,V,C[P+5],o,4294588738);V=D(V,Y,X,W,C[P+8],m,2272392833);W=D(W,V,Y,X,C[P+11],l,1839030562);X=D(X,W,V,Y,C[P+14],j,4259657740);Y=D(Y,X,W,V,C[P+1],o,2763975236);V=D(V,Y,X,W,C[P+4],m,1272893353);W=D(W,V,Y,X,C[P+7],l,4139469664);X=D(X,W,V,Y,C[P+10],j,3200236656);Y=D(Y,X,W,V,C[P+13],o,681279174);V=D(V,Y,X,W,C[P+0],m,3936430074);W=D(W,V,Y,X,C[P+3],l,3572445317);X=D(X,W,V,Y,C[P+6],j,76029189);Y=D(Y,X,W,V,C[P+9],o,3654602809);V=D(V,Y,X,W,C[P+12],m,3873151461);W=D(W,V,Y,X,C[P+15],l,530742520);X=D(X,W,V,Y,C[P+2],j,3299628645);Y=t(Y,X,W,V,C[P+0],U,4096336452);V=t(V,Y,X,W,C[P+7],T,1126891415);W=t(W,V,Y,X,C[P+14],R,2878612391);X=t(X,W,V,Y,C[P+5],O,4237533241);Y=t(Y,X,W,V,C[P+12],U,1700485571);V=t(V,Y,X,W,C[P+3],T,2399980690);W=t(W,V,Y,X,C[P+10],R,4293915773);X=t(X,W,V,Y,C[P+1],O,2240044497);Y=t(Y,X,W,V,C[P+8],U,1873313359);V=t(V,Y,X,W,C[P+15],T,4264355552);W=t(W,V,Y,X,C[P+6],R,2734768916);X=t(X,W,V,Y,C[P+13],O,1309151649);Y=t(Y,X,W,V,C[P+4],U,4149444226);V=t(V,Y,X,W,C[P+11],T,3174756917);W=t(W,V,Y,X,C[P+2],R,718787259);X=t(X,W,V,Y,C[P+9],O,3951481745);Y=K(Y,h);X=K(X,E);W=K(W,v);V=K(V,g)}var i=B(Y)+B(X)+B(W)+B(V);return i.toLowerCase()};

    var size = size || 80;

    return 'http://www.gravatar.com/avatar/' + MD5(email) + '.jpg?s=' + size + "&rating=pg";
}
</script>
<div class="span4">
<h2>News</h2>
<ul>
<?php
// JUST TEMPORARY, FETCHING SOME NEWS ITEMS FROM ANIMENEWSNETWORK!
$url = "http://www.animenewsnetwork.com/all/rss.xml";

//http://cdn.animenewsnetwork.com/encyclopedia/api.xml?anime=6704
try{
	$xml = simplexml_load_string(file_get_contents($url));

	$i = 0;
	foreach($xml->channel->item as $item)
	{
		if($i > 10)
			break;
		//echo "<div>";
		echo "<li><a href='".$item->link."'>" . $item->title . "</a></li>";
		//echo "<p>" . $item->description . "</p>";
		//echo "</div>";
		$i++;
	}
}catch(Exception $e){
	echo "<li>Could not get news at this time</li>";
}
/*echo "<pre>";
print_r($activities);
echo "</pre>";*/
//echo "<pre>";
//print_R($xml);
//echo "</pre>";
?>
</ul>
</div>
</div>
