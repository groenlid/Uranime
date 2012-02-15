 <h2>Scraping new information</h2> 
<script>
setInterval(get_output,500);
function get_output()
{
	$("#logfile").load('/anime/getlogfile');
}
</script>
<pre style="font-size:0.8em;" id="logfile">
 </pre>
