<?php declare(strict_types=1);

$configCache = new \Symfony\Component\Config\ConfigCache('...');

$stringValue = (string) $configCache;

$stringValue = $configCache->__toString();
