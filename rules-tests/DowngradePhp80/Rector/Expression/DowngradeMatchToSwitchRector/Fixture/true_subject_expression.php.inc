<?php

namespace Rector\Tests\DowngradePhp80\Rector\Expression\DowngradeMatchToSwitchRector\Fixture;

class TrueSubjectExpression
{
    public function run($statusCode)
    {
        $result = match (true) {
            ($statusCode >= 200 && $statusCode < 300) => 'success',
            in_array($statusCode, range(400, 499)) => 'client error',
            default => 'unknown status code',
        };
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\Expression\DowngradeMatchToSwitchRector\Fixture;

class TrueSubjectExpression
{
    public function run($statusCode)
    {
        switch (true) {
            case $statusCode >= 200 && $statusCode < 300:
                $result = 'success';
                break;
            case in_array($statusCode, range(400, 499)):
                $result = 'client error';
                break;
            default:
                $result = 'unknown status code';
                break;
        }
    }
}

?>
