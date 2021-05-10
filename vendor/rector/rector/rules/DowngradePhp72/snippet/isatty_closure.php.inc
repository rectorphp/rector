<?php

function ($stream) {
    if ('\\' === \DIRECTORY_SEPARATOR) {
        $stat = @fstat($stream);
        return $stat ? 020000 === ($stat['mode'] & 0170000) : false;
    }
    return @posix_isatty($stream);
};
