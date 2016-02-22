<html>
	<?php 
    require_once 'parsingFunctions.php';
    $songs_folder = 'songs_master/';
    ?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type "text/css" href="style.css">
<head>
<title>Indigo Song Book</title>
</head>
<body>
<div class='wrapper'>
<form id='form' method='GET' action='index.php'>

<!-- Select a song -->
<select class='select-style' id='choose_song' name='choose_song' onchange='this.form.submit()'>
<?php
    echo '<option value="Select A Song">Select A Song</option>';
    $files = array();
    foreach (glob($songs_folder.'*.song') as $file) {
        $name = preg_replace('/songs_master\/(.*)\.song/', '$1', $file);
        $selected = '';
        if (isset($_GET['choose_song']) && currentSong() == $name) {
            $selected = 'selected';
        }
        echo '<option value="&quot;'.$name.'&quot;" '.$selected.'>'.$name.'</option>';
    }
?>
</select>
<?php

 $showChords = true;
?>
<!-- Buttons and controls -->
<br>
<input type='submit' class='button' name='transpose' value='Capo'>
<input type='text' name='transposeValue' size='2' style='height: 25px; text-align:center;' value='<?php echo isset($_GET['transposeValue']) ? $_GET['transposeValue'] : '0'?>'>

</form>


<!-- Print selected song -->
<?php
if (isset($_GET['choose_song'])) {
    $getFile = $songs_folder.currentSong().'.song';
    $transposeValue = isset($_GET['transposeValue']) ? $_GET['transposeValue'] : 0;
    printSong(file_get_contents($getFile), $showChords, $transposeValue);
    //console.log(getTextWidth("hello there!", "bold 12pt arial"));  // close to 86
    //echo imagefontwidth(12) * strlen("You’re my friend, and You are my Brother,");
}

?>
</div>




<!-- Start of StatCounter Code for Default Guide -->
<script type="text/javascript">
var sc_project=10574996; 
var sc_invisible=1; 
var sc_security="499befe9"; 
var scJsHost = (("https:" == document.location.protocol) ?
"https://secure." : "http://www.");
document.write("<sc"+"ript type='text/javascript' src='" +
scJsHost+
"statcounter.com/counter/counter.js'></"+"script>");
</script>
<noscript><div class="statcounter"><a title="free hit
counter" href="http://statcounter.com/" target="_blank"><img
class="statcounter"
src="http://c.statcounter.com/10574996/0/499befe9/1/"
alt="free hit counter"></a></div></noscript>
<!-- End of StatCounter Code for Default Guide -->

</body>
</html>
