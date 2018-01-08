<?php declare(strict_types=1);

namespace SomeNamespace;

final class DummyConfigurator extends DummyObject
{
    public function createContainer()
    {
        return new DummyContainer();
    }
}

final class DummyObject
{
}
