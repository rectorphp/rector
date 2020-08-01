<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\Tests\Rector\MethodCall\InArgChainFluentMethodCallToStandaloneMethodCallRectorTest\Source;

final class DummyUserProfile
{
    public function setOldDescription(string $description)
    {
        return $this;
    }
    public function setUserId($id)
    {
        $this->userId = $id;
        return $this;
    }
}
