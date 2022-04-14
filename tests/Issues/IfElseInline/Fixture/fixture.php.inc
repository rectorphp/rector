<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\IfElseInline\Fixture;

final class Fixture
{
    /**
     * @var string
     */
    private const INTERFACE = 'Interface';

    public function run(string $className): string
    {
        if (str_contains($className, '\\')) {
            $shortClassName = (string) Strings::after($className, '\\', -1);
        } else {
            $shortClassName = $className;
        }

        return $shortClassName;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\IfElseInline\Fixture;

final class Fixture
{
    /** @var string */
    private const INTERFACE = 'Interface';

    public function run(string $className): string
    {
        $shortClassName = str_contains($className, '\\') ? (string) Strings::after($className, '\\', -1) : $className;

        return $shortClassName;
    }
}

?>
