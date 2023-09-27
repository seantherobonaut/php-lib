<?php
    /*File with a collection of useful functions*/

    //Adds content to a file, ensures folder exists
    function append_file($file, $data)
    {
        $path = pathinfo($file)['dirname'];
        if(!file_exists($path))
            mkdir($path);
        
        $targetFile = fopen($file, 'a');
        fwrite($targetFile, $data);
        fclose($targetFile);
    }
?>
