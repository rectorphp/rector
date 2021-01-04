<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use Elasticr\FoRest\Request\ApiRequest;
use Tests\Elasticr\FoRest\Request\ArticleRequest;

final class SkipAlreadyClassExists
{
    /**
     * @return \Tests\Elasticr\FoRest\App\Request\AlreadyClassExists
     */
    public static function create(array $data): self
    {
    }
}
