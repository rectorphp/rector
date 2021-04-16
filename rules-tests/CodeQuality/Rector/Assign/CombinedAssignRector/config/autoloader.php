<?php

spl_autoload_register(function() {
    class ClassWhichNeedsShouldTriggerAutoloading {}
    
    echo "keep on autoloading";
    exit();
});

