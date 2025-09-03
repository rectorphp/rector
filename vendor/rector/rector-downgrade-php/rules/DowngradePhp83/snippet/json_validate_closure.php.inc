<?php

function (string $json, int $depth = 512, int $flags = 0) {
    if (function_exists('json_validate'))  {
        return json_validate($json, $depth, $flags);
    }

    $maxDepth = 0x7FFFFFFF;

    if (0 !== $flags && \defined('JSON_INVALID_UTF8_IGNORE') && \JSON_INVALID_UTF8_IGNORE !== $flags) {
        throw new \ValueError('json_validate(): Argument #3 ($flags) must be a valid flag (allowed flags: JSON_INVALID_UTF8_IGNORE)');
    }

    if ($depth <= 0) {
        throw new \ValueError('json_validate(): Argument #2 ($depth) must be greater than 0');
    }

    if ($depth > $maxDepth) {
        throw new \ValueError(sprintf('json_validate(): Argument #2 ($depth) must be less than %d', $maxDepth));
    }

    json_decode($json, true, $depth, $flags);
    return \JSON_ERROR_NONE === json_last_error();
};
