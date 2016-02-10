<?php
/*
This is free and unencumbered software released into the public domain.

Anyone is free to copy, modify, publish, use, compile, sell, or
distribute this software, either in source code form or as a compiled
binary, for any purpose, commercial or non-commercial, and by any
means.

In jurisdictions that recognize copyright laws, the author or authors
of this software dedicate any and all copyright interest in the
software to the public domain. We make this dedication for the benefit
of the public at large and to the detriment of our heirs and
successors. We intend this dedication to be an overt act of
relinquishment in perpetuity of all present and future rights to this
software under copyright law.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS BE LIABLE FOR ANY CLAIM, DAMAGES OR
OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.

For more information, please refer to <http://unlicense.org>
*/




class Wave {
    private $sampleRate;
    private $samples;
    function __construct($sampleRate) {
        $this->sampleRate = $sampleRate;
        $this->samples = array();
    }

    public function addTone($freq, $dur) {
        $samplesCount = $dur * $this->sampleRate;

        $amplitude = 65536/2 * .9;
        $w = 2 * pi() * $freq / $this->sampleRate;

        for ($n = 0; $n < $samplesCount; $n++)
            $this->samples[] = (int)($amplitude *  sin($n * $w));
    }

    public function writeWav() {
        $bps = 16; //bits per sample
        $Bps = $bps/8; //bytes per sample /// I EDITED
        return call_user_func_array("pack",
            array_merge(array("VVVVVvvVVvvVVv*"),
                array(//header
                    0x46464952, //RIFF
                    2*count($this->samples) + 38,
                    0x45564157, //WAVE
                    0x20746d66, //"fmt " (chunk)
                    16, //chunk size
                    1, //compression
                    1, //nchannels
                    $this->sampleRate,
                    $Bps*$this->sampleRate, //bytes/second
                    $Bps, //block align
                    $bps, //bits/sample
                    0x61746164, //"data"
                    count($this->samples)*2 //chunk size
                ),
                $this->samples //data
            )
        );
    }

    public function outWav() {
        $data = $this->writeWav();
        header('Content-type: audio/wav');
        header('Content-length: '.strlen($data));
        echo $data;
    }

    public function outMp3() {
        $proc = proc_open('lame - -', array(
                0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
                1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
                2 => array("file", "/dev/null", "a") // stderr is a file to write to
            ), $pipes, '/var/www/htdocs', array());

        fwrite($pipes[0], $this->writeWav());
        fclose($pipes[0]);
        $ret = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        proc_close($proc);

        header('Content-type: audio/mp3');
        header('Content-length: '.strlen($ret));
        echo $ret;
    }
}
