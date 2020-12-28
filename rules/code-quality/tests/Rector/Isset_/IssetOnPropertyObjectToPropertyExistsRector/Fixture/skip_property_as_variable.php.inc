<?php

namespace Rector\CodeQuality\Tests\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector\Fixture;

class SkipPropertyAsVariable
{
    public function run($name)
    {
        return isset($this->getStorage()->$name);
    }

    private function getStorage(): array
    {
	   return [];
	}
}

?>
