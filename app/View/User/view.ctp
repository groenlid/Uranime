<?php
	extract($user['User']);
	
?>
<div class="row">
<div class="span3">
	<div>
		<?= $this->Gravatar->image($email, array('size' => '150', 'rating' => 'pg'),array('class' => 'animeimage')) ?>
	</div><hr>
<a href="/library/view/<?=$id.'/'.$nick?>" class="btn" style="margin-bottom:10px">View Anime-library</a>
<a href="/watchlist/view/<?=$id.'/'.$nick?>" class="btn">View Watchlist</a>
</div>
<div class="span8">
	<h2><?= $nick ?></h2>

<!-- http://158.39.171.120/api/userEpisodeGraph/1.json -->
<script>
$.get("http://urani.me/api/userEpisodeGraph/<?=$id?>.json", function(data){
var acc = 0;
/*data[0]['data'].forEach( function(t) {
		acc += t['y'];
		t['y'] = acc;
	} );*/

var graph = new Rickshaw.Graph( {
        element: document.querySelector("#chart"),
        width: 580,
        renderer: 'bar',
        stroke: true,
        height: 150,
        series: [ {
                color: 'steelblue',
                /*color:'grey',*/
                data: data[0]['data']
        } ]
} );

graph.render();


var hoverDetail = new Rickshaw.Graph.HoverDetail( {
    graph: graph
} );

var ticksTreatment = 'glow';
var xAxis = new Rickshaw.Graph.Axis.Time( {
	graph: graph,
	ticksTreatment: ticksTreatment
} );

xAxis.render();

var yAxis = new Rickshaw.Graph.Axis.Y( {
	graph: graph,
	tickFormat: Rickshaw.Fixtures.Number.formatKMBT,
	ticksTreatment: ticksTreatment
} );

yAxis.render();

});

/*var palette = new Rickshaw.Color.Palette( { scheme: 'httpStatus' } );

var graph = new Rickshaw.Graph.Ajax( {
	element: document.getElementById("chart"),
	dataURL: 'http://158.39.171.120/api/userEpisodeGraph/<?php echo $id;?>.json',
	width: 500,
	height: 200,
	renderer: 'bar',
	stroke: true,
	
} );*/

</script>
	<blockquote>
		<p class="animedesc">	
			<?= $desc ?>
		</p>
	</blockquote>
	<a href="#" id="showHidden" class="pull-right">Show user graph</a>
	<br class="clear">
	<hr>
	<div class="hidden" style="background:#fafafa; padding:10px; margin:20px; margin-left:0; border:1px solid #EEE; width:600px" id="chart_container">
		<div id="chart"></div>
		<div id="timeline"></div>
	</div>
<div id="newsfeed">
<?php
foreach($activity as $a)
	echo $this->element('activity', array(
    	"activity" => $a,
    	"kindOfObject" => "user"
    	));
//	echo print_activity($a);


/*function print_activity($activity)
{
	if(!isset($activity['object']['Anime']))
		return;
	$result = "";
	$result .= "<div class='row newsfeed'>";
	// The image
	$result .= "<div class='span1'>";
	switch($activity['Activity']['object_type']){
		case('episode'):
		case('reference'):
		case('image'):
		case('anime'):
		case('fanart'):
		
		$result .= "<a href='/anime/view/".$activity['object']['Anime']['id']."/".$activity['object']['Anime']['title']."'><img src='http://src.sencha.io/50/".SERVER_PATH . IMAGE_PATH. $activity['object']['Anime']['fanart']."'></a>";

		break;
	}
	$result .= "</div>";
	// The desc
	$result .= "<div class='span5'><p>";
	$result .= $activity['subject']['nick'];

	switch($activity['Activity']['verb']){

		case('added'):

			switch($activity['Activity']['object_type']){
				case('anime'):
					$result .= " added a new anime to the system named " . $activity['object']['Anime']['title'];
				break;
				case('fanart'):
					$result .= " added/changed fanart for " . $activity['Activity']['option'] . " for anime " . $activity['object']['Anime']['title'];
				break;
				case('image'):
					$result .= " added/changed image for " . $activity['Activity']['option'] . " for anime " . $activity['object']['Anime']['title'];
				break;
				case('reference'):
					$result .= " added a reference for " . $activity['Activity']['option'] . " for anime " . $activity['object']['Anime']['title'];
				break;

			}

		break;
		case('watched'):

			switch($activity['Activity']['object_type']){

				case('episode'):
					$result .= " watched <a href='/episode/view/".$activity['object']['Episode']['id']."'>episode " . $activity['object']['Episode']['number']. "</a> of anime <a href='/anime/view/".$activity['object']['Anime']['id']."'>" . $activity['object']['Anime']['title']."</a>";
				break;
			}

		break;

	}
		
	$result .= "";
	
	$result .= "</p><p class='subtle'>".$activity['Activity']['timestamp']."</p>";
	$result .= "</div>";	
	$result .= "</div>";
	return $result;
}*/
//debug($activity);
?>
</div>
</div>
</div>
