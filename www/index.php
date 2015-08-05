<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<?php
$output = exec('ls -la');
echo "<pre>$output</pre>";
?>
<?php
$output = exec('R --quiet --no-restore --no-save -f ./demo.r > ./output.Rout');
echo "<pre>$output</pre>";
?>
<?php
//$output = exec("sh ./script.sh");
echo "<pre>$output</pre>";
?>
<?php
echo file_get_contents( "./output.Rout" ); // get the contents, and echo it out.
?>
