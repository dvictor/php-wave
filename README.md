# php-wave
Generate sinusoidal sound wave as wav or mp3 from a list of frequencies and durations

I created this class because on some phones (Safari iOS) the tones generated with JavaScript had the wrong frequency.

You need to have lame installed:

`apt-get install lame`


Usage:

```PHP
$song = array(1000, 1500, 1200);
$duration = .3;

$wav = new Wave(44100);
for ($i=0; $i<count($song); $i++)
    $wav->addTone($song, $duration);
header('X-Debug-info: '.join(',', $song));
$wav->outMp3();
```
