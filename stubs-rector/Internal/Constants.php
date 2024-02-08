<?php

if (PHP_VERSION_ID < 80100) {
    if (! defined('MHASH_XXH32')) {
        define('MHASH_XXH32', 38);
    }

    if (! defined('MHASH_XXH64')) {
        define('MHASH_XXH64', 39);
    }

    if (! defined('MHASH_XXH3')) {
        define('MHASH_XXH3', 40);
    }

    if (! defined('MHASH_XXH128')) {
        define('MHASH_XXH128', 41);
    }
}
