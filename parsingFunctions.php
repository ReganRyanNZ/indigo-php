<?php

$scales = array(
    'A' => array('A','B','C#','D','E','F#','G#'),
    'Bb' => array('Bb','C','D','Eb','F','G','A'),
    'B' => array('B','C#','D#','E','F#','G#','A#'),
    'C' => array('C','D','E','F','G','A','B'),
    'Db' => array('Db','Eb','F','Gb','Ab','Bb','C'),
    'D' => array('D','E','F#','G','A','B','C#'),
    'Eb' => array('Eb','F','G','Ab','Bb','C','D'),
    'E' => array('E','F#','G#','A','B','C#','D#'),
    'F' => array('F','G','A','Bb','C','D','E'),
    'Gb' => array('Gb','Ab','Bb','Cb','Db','Eb','F'),
    'G' => array('G','A','B','C','D','E','F#'),
    'Ab' => array('Ab','Bb','C','Db','Eb','F','G'),
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
                // echo "<span class='invisible-padding-words'>";
                echo '<span data-text="';
                for ($n = 0;$n <= count($lyrics);++$n) {
                    if (isset($chords[$n])) {
                        echo "\"></span><span class='chord'>".transpose(implode('', $chords[$n]), $tKey, $key)."</span><span <span data-text=\"";
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
            echo '"></span>';
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
?>
