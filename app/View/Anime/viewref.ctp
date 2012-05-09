<div class="row">
<?php
	extract($anime['Anime']);
?>

<div class="span8">
<?php
	include('sub_menu.php');
?>
<a href="#" id="showHidden" class="pull-right">Show the references list</a><br class="clear">
<table id="searchTable" class="hidden episodelist table table-striped table-bordered table-condensed">
<thead>
<?php
echo $this->Html->tableHeaders(array('Source','Source ID','Scrape Episodes','Use for episodes','Use for information','Edit / Delete'));
?>
</thead>
<tbody>
<?php


foreach($info as $scrapeinfo)
{
	extract($scrapeinfo['ScrapeInfo']);
	/*echo "<tr>";
	
	
	
	echo $scrapeinfo['ScrapeInfo']['scrape_source'];
	echo "</tr>";*/
echo $this->Form->create('anime', array('url' => '/anime/editref/'.$id,'class'=> 'stylish'));
echo $this->Html->tableCells(
		array(
			// Make the dropdown
			$this->Form->input('ScrapeInfo.scrape_source', array(
				'default' => $scrape_source, 
				'label' => false,
				'options' => array('anidb'=>'anidb','thetvdb'=>'thetvdb','mal'=>'mal','themoviedb' => 'themoviedb'),
				'id' => $id, 
				'class' => 'span2')),
			$this->Form->input('ScrapeInfo.scrape_id', array(
				'default' => $scrape_id,
				'style' => '',
				'type'	=> 'text',
				'label' => false,
				'id' => $id,
				'div' => false,
				'class' => 'span1'
			)),
			$this->Form->input('ScrapeInfo.scrape_episodes', array(
				'default' => $scrape_episodes,
				'style' => '',
				'label' => false,
				'id' => $id,
				'class' => 'span1'
			)),
			$this->Form->input('ScrapeInfo.fetch_episodes', array(
				'type'	=> 'checkbox',
				'label' => false,
				'checked' => ($fetch_episodes == NULL) ? false : true,
				'id' => $id,
				'class' => 'fetch_episodes span1'
			)),
			$this->Form->input('ScrapeInfo.fetch_information', array(
				'type'	=> 'checkbox',
				'label' => false,
				'checked' => ($fetch_information == NULL) ? false : true,
				'id' => $id,
				'class' => 'fetch_information span1'
			)),
			$this->Form->button('Edit', array('type' => 'submit','class' => 'btn'))
			
			)
		);
echo $this->Form->end();
}

echo $this->Form->create('anime', array('url' => '/anime/addref/'.$siteid,'class'=> 'stylish'));
echo $this->Html->tableCells(
		array(
			// Make the dropdown
			$this->Form->input('ScrapeInfo.scrape_source', array(
				'label' => false,
				'options' => array('anidb'=>'anidb','thetvdb'=>'thetvdb','mal'=>'mal','themoviedb' => 'themoviedb'),
				'class' => 'span2')),
			
			$this->Form->input('ScrapeInfo.scrape_id', array(
				'type'	=> 'text',
				'label' => false,
				'style' => '',
				'class' => 'span1'
			)),
			$this->Form->input('ScrapeInfo.scrape_episodes', array(
				'label' => false,
				'style' => '',
				'class' => 'span1'
			)),
			$this->Form->input('ScrapeInfo.fetch_episodes', array(
				'type'	=> 'checkbox',
				'label' => false,
				'checked' => false,
				'class' => 'fetch_episodes span1'
			)),
			$this->Form->input('ScrapeInfo.fetch_information', array(
				'type'	=> 'checkbox',
				'label' => false,
				'checked' => false,
				'class' => 'fetch_information span1'
			)),
			$this->Form->button('Add', array('type' => 'submit','class' => 'btn btn-primary'))
			
			)
		);
echo $this->Form->end();

?>
</tbody>
</table>
<hr>
<div class="actions"><center>
<?php
echo $this->Html->link('<i class="icon-search"></i> Search myanimelist', $this->Html->url('/anime/searchReferences/myanimelist/'.$anime['Anime']['id'], false), array('class' => 'ajax btn','escape' => false)) . " ";
echo $this->Html->link('<i class="icon-search"></i> Search anidb', $this->Html->url('/anime/searchReferences/anidb/'.$anime['Anime']['id'], false), array('class' => 'ajax btn', 'escape' => false)). " ";
echo $this->Html->link('<i class="icon-search"></i> Search thetvdb', $this->Html->url('/anime/searchReferences/thetvdb/'.$anime['Anime']['id'], false), array('class' => 'ajax btn', 'escape' => false)). " ";
echo $this->Html->link('<i class="icon-search"></i> Search themoviedb', $this->Html->url('/anime/searchReferences/themoviedb/'.$anime['Anime']['id'], false), array('class' => 'ajax btn', 'escape' => false));
?></center>
</div>
<div class="ajaxresults">
</div>
<hr>
<div class="notif">
<strong>Recommended settings:</strong>
<p><strong>anidb</strong>: episodes + information</p>
<p><strong>mal</strong>: information</p>
<p><strong>thetvdb</strong>: sometimes episodes.</p>
</div>
</div>

<?php
	include('leftside.ctp');
?>
</div>