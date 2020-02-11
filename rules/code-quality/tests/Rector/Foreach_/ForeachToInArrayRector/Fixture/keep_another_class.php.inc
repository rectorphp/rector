<?php

namespace Rector\CodeQuality\Tests\Rector\Foreach_\ForeachToInArrayRector\Fixture;

class KeepAnotherClass
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
