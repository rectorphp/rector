<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use ApiPlatform\Core\Annotation\ApiResource;

/**
 * @ApiResource(
 *     collectionOperations={},
 *     graphql={
 *         "sumOnlyPersist"={
 *             "mutation"="app.graphql.mutation_resolver.dummy_custom_only_persist",
 *             "normalization_context"={"groups"={"result"}},
 *             "denormalization_context"={"groups"={"sum"}},
 *             "read"=false,
 *             "deserialize"=false,
 *             "validate"=false,
 *             "serialize"=false
 *          }
 *     }
 * )
 */
class ApiPlatformResource
{
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use ApiPlatform\Core\Annotation\ApiResource;

#[\ApiPlatform\Core\Annotation\ApiResource(collectionOperations: [], graphql: ['sumOnlyPersist' => ['mutation' => 'app.graphql.mutation_resolver.dummy_custom_only_persist', 'normalization_context' => ['groups' => ['result']], 'denormalization_context' => ['groups' => ['sum']], 'read' => false, 'deserialize' => false, 'validate' => false, 'serialize' => false]])]
class ApiPlatformResource
{
}

?>
