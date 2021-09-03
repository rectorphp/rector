<?php

namespace Rector\Core\Tests\Issues\Issue6677\Fixture;

final class DoNotTriggerPhpWarning
{
    public function testListStyle() : void
    {
        list(, $b) = $this->value();
    }

    public function testBracketStyle() : void
    {
        [, $b] = $this->value();
    }

    private function value(): array
    {
        return ['a', 'b'];
    }
}
?>
-----
<?php

namespace Rector\Core\Tests\Issues\Issue6677\Fixture;

final class DoNotTriggerPhpWarning
{
    public function testListStyle() : void
    {
        list(, $b) = $this->value();
    }

    public function testBracketStyle() : void
    {
        list(, $b) = $this->value();
    }

    private function value(): array
    {
        return ['a', 'b'];
    }
}
?>
