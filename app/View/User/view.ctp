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
    	));

//debug($activity);
?>
</div>
</div>
</div>
