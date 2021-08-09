<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\IssueCreatePublicPropertyOnRename\Fixture;

final class DemoFile
{
    public function __construct(private \DateTimeImmutable $date)
    {
    }

    public function run(): string
    {
        return $this->date->format('l, F j, Y');
    }
}
?>
-----
<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\IssueCreatePublicPropertyOnRename\Fixture;

final class DemoFile
{
    public function __construct(private \DateTimeImmutable $dateTimeImmutable)
    {
    }

    public function run(): string
    {
        return $this->dateTimeImmutable->format('l, F j, Y');
    }
}
?>
