<?php

namespace Rector\Downgrade\Tests\Rector\LNumber\ChangePhpVersionInPlatformCheckRector\Fixture;

$issues = [];

if (!(PHP_VERSION_ID >= 70300)) {
    $issues[] = 'Your Composer dependencies require a PHP version ">= 7.3.0". You are running ' . PHP_VERSION  .  '.';
}

?>
-----
<?php

namespace Rector\Downgrade\Tests\Rector\LNumber\ChangePhpVersionInPlatformCheckRector\Fixture;

$issues = [];

if (!(PHP_VERSION_ID >= 70100)) {
    $issues[] = 'Your Composer dependencies require a PHP version ">= 7.1". You are running ' . PHP_VERSION  .  '.';
}

?>
