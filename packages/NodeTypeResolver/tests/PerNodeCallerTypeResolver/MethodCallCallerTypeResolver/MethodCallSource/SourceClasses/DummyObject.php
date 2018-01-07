<?php declare(strict_types=1);

namespace SomeNamespace;

class DummyConfigurator extends DummyObject
{
    public function createContainer()
    {
        return new DummyContainer();
    }
}

class DummyObject
{

}
