<?php

include("TimescaleLib.php");

$times = parseDefaultTimescale();

?>
<html>


<script>
  var times = <?=json_encode($times)?>;
  console.log('times for series HOLOCENE = ', times.filter(function(t) { return t.series.toUpperCase() === 'HOLOCENE' }));
  console.log('times = ', times);
</script>

</html>
