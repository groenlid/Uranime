<h2>Import from your myanimelist account</h2>

<a href="#" class="importMal btn">Import</a>
<div class="progress progress-info progress-striped active pull-right" style='width:200px'>
  <div class="bar" style="width: 0%;"></div>
</div>

<table class="table table-small table-striped malimport">
	<thead>
    <tr>
      <th>Anime title</th>
      <th>MAL status</th>
      <th>MAL link</th>
    </tr>
  </thead>
  <tbody>
<?php
	//$ = print_r($animelist);
	$have = 0;
	foreach($animelist as $anime)
	{
		if($anime['scrape_info'] != null)
			echo "
				<tr class='AnimeRow'>
					<td>";
		else
			echo "<tr><td>";

		if($anime['scrape_info'] != null){
			echo "<span class='requestStatus'></span><a class='animelink' id='".$anime['scrape_info']['Anime']['id']."' href='/anime/view/".$anime['scrape_info']['Anime']['id']."'>".$anime['title']."</a>";
			$have++;
		}
		else
			echo $anime['title'];
		echo "</td>
			";
		echo "
			<td>
				<span class='epseen'>".$anime['epseen'] . "</span> / " . $anime['eptotal'] ."
			</td>
			<td>
				<a class='label label-info' href='http://myanimelist.net/anime/".$anime['id']."'>MAL</a>
			</td>
		</tr>";
	}	
	echo "<tr><td></td><td><strong>".$have." / ".count($animelist)."</strong></td><td></td><td></td></tr>"
?>
 </tbody>
</table>