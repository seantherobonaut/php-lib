<?
    //File with a collection of useful functions

    function push_log($file, $data)
    {
        if(!file_exists($file))
                mkdir(pathinfo($file)['dirname']);
        
        $targetFile = fopen($file, 'a');
        fwrite($targetFile, $data);
        fclose($targetFile);
    }
?>
