<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

final class PostController
{
    /**
     * @ParamConverter("post", class="Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Post", options={"id": "post"})
     */
    public function __invoke(Site $site, Post $post)
    {
    }
}
?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

final class PostController
{
    #[\Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter('post', class: 'Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Post', options: ['id' => 'post'])]
    public function __invoke(Site $site, Post $post)
    {
    }
}
?>
