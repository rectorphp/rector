<?php

namespace Rector\Core\Tests\Issues\IssueAnnotation\Fixture;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

final class Anything extends Autoconfigure
{
    public function __construct()
    {
        parent::__construct(
            tags: ['some_items']
        );
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\IssueAnnotation\Fixture;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

final class Anything extends Autoconfigure
{
    public function __construct()
    {
        parent::__construct(
            ['some_items']
        );
    }
}

?>
