<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\Fixture;

function imagesavetofile($gdImage, $filename, $quality)
{
    $ext = pathinfo($filename, \PATHINFO_EXTENSION);
    if (!$ext) {
        throw new Exception('Unable to determine file extension for '.$filename);
    }

    switch (strtolower($ext)) {
        case 'jpeg':
        case 'jpg':
                imagejpeg($gdImage, $filename, $quality);
                break;

        case 'png':
                imagepng($gdImage, $filename, $pngQuality);

                break;

        case 'gif':
                imagegif($gdImage, $filename);
                break;

        default:
            throw new Exception('Unsupported file extension '.$ext);
    }
}
