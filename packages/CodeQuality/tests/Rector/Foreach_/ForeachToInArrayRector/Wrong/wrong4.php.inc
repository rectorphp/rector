<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Foreach_\ForeachToInArrayRector\Wrong;

class SomeAnotherClass
{
    public function function2()
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            if ($this->findCookiesArticleByDomainId($domainConfig->getId()) === $article) {
                return true;
            }
        }

        return false;
    }
}
