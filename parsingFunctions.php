<?php

$scales = array(
    'A' => array('A','B','C#','D','E','F#','G#'),
    'Bb' => array('Bb','C','D','Eb','F','G','A'),
    'B' => array('B','C#','D#','E','F#','G#','A#'),
    'C' => array('C','D','E','F','G','A','B'),
    'C#' => array('C#','D#','E#','F#','G#','A#','B#'),
    'D' => array('D','E','F#','G','A','B','C#'),
    'D#' => array('D#','E#','G','G#','A#','C','D'),
    'E' => array('E','F#','G#','A','B','C#','D#'),
    'F' => array('F','G','A','Bb','C','D','E'),
    'F#' => array('F#','G#','A#','B','C#','D#','E#'),
    'G' => array('G','A','B','C','D','E','F#'),
    'G#' => array('G#','A#','B#','C#','D#','E#','G'),
    );

function currentSong()
{
    return substr($_GET['choose_song'], 1, -1);
}

function transposeKey($key, $transposeValue)
{
    global $scales;
    if ($transposeValue > 0) {
        $indices = array_keys($scales);
        foreach ($indices as $i => $value) {
            if ($value == $key) {
                $tKey = $indices[($i + $transposeValue) % 12];
            }
        }

        return $tKey;
    }

    return $key;
}

function printSong($raw, $showChords, $transposeValue)
{
    $raw = preg_split('/[\n\r]/', $raw);
    $isChorus = false;
    $key = 'Z';
    $tKey = 'Z';
    $stanzaNumber = 0;
    for ($i = 0; $i < count($raw); ++$i) {
        if (preg_match('/{[Tt]itle: .*}/', $raw[$i])) {
            $title = preg_replace('/{[Tt]itle: (.*)}/', '$1', $raw[$i]);
            echo "<h2>$title</h2>";
            if(preg_match('/^[ \t]*$/', $raw[$i+1])) {
                $i++;
            }
        } elseif (preg_match('/{comments: .*}/', $raw[$i])) {
            $comment = preg_replace('/{comments: (.*)}/', '$1', $raw[$i]);
            echo "<span class='comment'>$comment</span><br>";
        } elseif (preg_match('/{section: .*}/', $raw[$i])) {
            $section = preg_replace('/{section: (.*)}/', '$1', $raw[$i]);
            echo "<span class='section'>$section</span><br>";
        } elseif (preg_match('/{no_number}.*/', $raw[$i])) {
            //nothing for now, I don't do numbers yet...
        } elseif (preg_match('/{start_of_chorus}/', $raw[$i])) {
            $isChorus = true;
            echo "<div class='chorus'>";
        } elseif (preg_match('/{end_of_chorus}/', $raw[$i])) {
            $isChorus = false;
            echo '</div>';
        // } elseif (preg_match('/^[ \t]*$/', $raw[$i]) && !$isChorus) {
        //         ++$stanzaNumber;
        //         echo "<br><span class='stanzaNumber'>$stanzaNumber </span>";
        } else {
            $lyrics = array();
            $chords = array();
            $offset = 0;
            $lyricsIndex = 0;
            while ($lyricsIndex + $offset < strlen($raw[$i])) {
                if ($raw[$i][$lyricsIndex + $offset] == '[') { //create chord
                    ++$offset;
                    while ($raw[$i][$lyricsIndex + $offset] != ']') {
                        $chords[$lyricsIndex][] = $raw[$i][$lyricsIndex + $offset];
                        ++$offset;
                    }
                    ++$offset;
                } else { //create lyrics
                    $lyrics[] = $raw[$i][$lyricsIndex + $offset];
                    ++$lyricsIndex;
                }
            }

            if ($key == 'Z' && count($chords) > 0) {
                $key = preg_replace('/{.*([ABCDEFG][#b]?).*/', '$1', implode('', reset($chords)));
            }
            if ($tKey == 'Z' && $key != 'Z' && $transposeValue > 0) {
                $tKey = transposeKey($key, $transposeValue);
            }

            if ($showChords && count($chords) > 0) { //print chords
                echo "<span style='color: white'>";
                for ($n = 0;$n <= count($lyrics);++$n) {
                    if (isset($chords[$n])) {
                        echo "<span class='chord'>".transpose(implode('', $chords[$n]), $tKey, $key).'</span>';
                        $l = 0;
                        //skip over chord length, to keep subsequent chords in place.
                        while ($n < count($lyrics) &&
                            !isset($chords[$n + $l + 2]) &&
                            $l < count($chords[$n])) {
                            ++$l;
                    }
                    if ($l > 0) {
                        $n += $l - 1;
                    }
                } else {
                    if ($n != count($lyrics)) {
                        echo $lyrics[$n];
                    }
                }
            }
            echo '</span>';
            echo '<br>';
        }

            echo implode('', $lyrics).'<br>'; //print lyrics
        }
    }
}

function transpose($chord, $tKey, $key)
{
    global $scales;
    if ($tKey == 'Z') {
        return $chord;
    }
    // $scales = array
    //      (
    //          'A' => array('A','B','C#','D','E','F#','G#'),
    //          'A#' => array('A#','B#','D','D#','E#','G','A'),
    //          'Bb' => array('Bb','C','D','Eb','F','G','A'),
    //          'B' => array('B','C#','D#','E','F#','G#','A#'),
    //          'Cb' => array('Cb','Db','Eb','Fb','Gb','Ab','Bb'),
    //          'B#' => array('B#','D','E','E#','G','A','B'),
    //          'C' => array('C','D','E','F','G','A','B'),
    //          'C#' => array('C#','D#','E#','F#','G#','A#','B#'),
    //          'Db' => array('Db','Eb','F','Gb','Ab','Bb','C'),
    //          'D' => array('D','E','F#','G','A','B','C#'),
    //          'D#' => array('D#','E#','G','G#','A#','C','D'),
    //          'Eb' => array('Eb','F','G','Ab','Bb','C','D'),
    //          'E' => array('E','F#','G#','A','B','C#','D#'),
    //          'E#' => array('E#','G','A','A#','B#','D','E'),
    //          'Fb' => array('Fb','Gb','Ab','A','Cb','Db','Eb'),
    //          'F' => array('F','G','A','Bb','C','D','E'),
    //          'F#' => array('F#','G#','A#','B','C#','D#','E#'),
    //          'Gb' => array('Gb','Ab','Bb','Cb','Db','Eb','F'),
    //          'G' => array('G','A','B','C','D','E','F#'),
    //          'G#' => array('G#','A#','B#','C#','D#','E#','G'),
    //          'Ab' => array('Ab','Bb','C','Db','Eb','F','G'),
    //      );


    $stripped = preg_replace('/(.*)([ABCDEFG][#b]?)(.*)/', '$2', $chord);

    for ($i = 1; $i < count($scales[$key]); $i = ($i + 1) % count($scales[$key])) {
        if ($scales[$key][$i] == $stripped) {
            $transposed = $scales[$tKey][$i];
            $stringBuilder = '$1'.$transposed.'$3';
            $output = preg_replace('/(.*)([ABCDEFG][#b]?)(.*)/', '$1'."$transposed".'$3', $chord);

            return $output;
        }
        if ($i == 0) {
            return $chord; //couldn't find chord in key.
        }
    }
}
//JS Function to get text width.
// /**
//  * Uses canvas.measureText to compute and return the width of the given text of given font in pixels.
//  * 
//  * @param {String} text The text to be rendered.
//  * @param {String} font The css font descriptor that text is to be rendered with (e.g. "bold 14px verdana").
//  * 
//  * @see http://stackoverflow.com/questions/118241/calculate-text-width-with-javascript/21015393#21015393
//  */
// function getTextWidth(text, font) {
//     // re-use canvas object for better performance
//     var canvas = getTextWidth.canvas || (getTextWidth.canvas = document.createElement("canvas"));
//     var context = canvas.getContext("2d");
//     context.font = font;
//     var metrics = context.measureText(text);
//     return metrics.width;
// };
?>
