<?php

function ($stream) {
    if (\function_exists('stream_isatty')) {
        return stream_isatty($stream);
    }

    if (!\is_resource($stream)) {
        trigger_error('stream_isatty() expects parameter 1 to be resource, '.\gettype($stream).' given', \E_USER_WARNING);

        return false;
    }

    if ('\\' === \DIRECTORY_SEPARATOR) {
        $stat = @fstat($stream);
        // Check if formatted mode is S_IFCHR
        return $stat ? 0020000 === ($stat['mode'] & 0170000) : false;
    }

    return \function_exists('posix_isatty') && @posix_isatty($stream);
};
