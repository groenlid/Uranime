<pre>This page is under construction and will therefore change.</pre>
<div class="row">
	<div class="span11">
		<table class="table table-striped table-bordered">
			<thead>
				<tr>
					<th class="span1"></th>
					<th>Nick</th>
					<th>Joined</th>
				<tr>
			</thead>
		<tbody>
		<?php

		foreach($users as $user){
			echo "<tr>";
			echo "<td>";
			echo $this->Gravatar->image($user['User']['email'], array('class' => 'animeimage', 'size' => '50', 'rating' => 'pg'));
			echo "</td>";
			echo "<td><a href='/user/view/".$user['User']['id']."/".$user['User']['nick']."'>".$user['User']['nick']."</a></td>";
			echo "<td>".$user['User']['joined']."</td>";
			echo "</tr>";
		}
		?>
		</tbody>
		</table>
	</div>
</div>
 