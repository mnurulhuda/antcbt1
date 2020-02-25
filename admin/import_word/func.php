<?php
function xml_attribute($object, $attribute)
{
    if (isset($object[$attribute])) {
        return (string) $object[$attribute];
    }
}

function get_numerics($str)
{
    preg_match_all('/Soal:\d+/', $str, $matches);
    return $matches[0];
}

function rrmdir($dir)
{
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir . "/" . $object) == "dir") {
                    rrmdir($dir . "/" . $object);
                } else {
                    unlink($dir . "/" . $object);
                }

            }
        }
        reset($objects);
        if ($dir != "uploads") {
            rmdir($dir);
        }
    } else {
        unlink($dir);
    }
}