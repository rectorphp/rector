<?php

namespace Rector\PHPUnit\Tests\Rector\Class_\SelfContainerGetMethodCallFromTestToInjectPropertyRector\Fixture;

use Rector\PHPUnit\Tests\Rector\Class_\SelfContainerGetMethodCallFromTestToInjectPropertyRector\Source\TestCaseWithGetContainer;

class AnotherCaseTest extends TestCaseWithGetContainer
{
    protected function getDomainBaseUrl(): string
    {
        /** @var \Shopsys\FrameworkBundle\Component\Domain\Domain $domain */
        $domain = $this->getContainer()->get(Domain::class);

        return $domain->getUrl();
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\Class_\SelfContainerGetMethodCallFromTestToInjectPropertyRector\Fixture;

use Rector\PHPUnit\Tests\Rector\Class_\SelfContainerGetMethodCallFromTestToInjectPropertyRector\Source\TestCaseWithGetContainer;

class AnotherCaseTest extends TestCaseWithGetContainer
{
    /**
     * @inject
     */
    private \Rector\PHPUnit\Tests\Rector\Class_\SelfContainerGetMethodCallFromTestToInjectPropertyRector\Fixture\Domain $domain;
    protected function getDomainBaseUrl(): string
    {
        return $this->domain->getUrl();
    }
}

?>
