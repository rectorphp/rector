<?php

function toStringToMethodCall()
{
    $configCache = new \Symfony\Component\Config\ConfigCache('...');

    $stringValue = (string)$configCache;

    $stringValue = $configCache->__toString();
}

?>
-----
<?php

function toStringToMethodCall()
{
    $configCache = new \Symfony\Component\Config\ConfigCache('...');

    $stringValue = $configCache->getPath();

    $stringValue = $configCache->getPath();
}

?>
