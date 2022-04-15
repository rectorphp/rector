<?php

namespace Rector\Core\Tests\Issues\Issue7112\Fixture;

class Fixture
{
    public function something($callback = null): self
    {
        return $this;
    }

    public function run(): void
    {
        $model = $this->something()->value();
        $model = $this->something(
            function ($query) {
                return $query;
            }
        )->value();
    }

    private function value(): string
    {
        return 'anything';
    }
}
