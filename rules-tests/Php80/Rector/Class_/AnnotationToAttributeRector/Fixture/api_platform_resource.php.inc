<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use ApiPlatform\Core\Annotation\ApiResource;

/**
 * @ApiResource(collectionOperations={}, itemOperations={
 *     "get",
 *     "get_by_isbn"={"method"="GET", "path"="/books/by_isbn/{isbn}.{_format}", "requirements"={"isbn"=".+"}, "identifiers"="isbn"}
 * })
 */
class ApiPlatformResource
{
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use ApiPlatform\Core\Annotation\ApiResource;

#[\ApiPlatform\Core\Annotation\ApiResource(collectionOperations: [], itemOperations: ['get', 'get_by_isbn' => ['method' => 'GET', 'path' => '/books/by_isbn/{isbn}.{_format}', 'requirements' => ['isbn' => '.+'], 'identifiers' => 'isbn']])]
class ApiPlatformResource
{
}

?>
