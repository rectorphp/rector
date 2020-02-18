<?php

namespace Rector\Zend2Rocket\Tests\Rector\ClassMethod\RenderZendViewRector\Fixture;

class SomeController {
    public function getArticleInfoAction()
    {
    }
}

?>
-----
<?php

namespace Rector\Zend2Rocket\Tests\Rector\ClassMethod\RenderZendViewRector\Fixture;

class SomeController {
    public function getArticleInfoAction()
    {
        return $this->currentZendViewResult();
    }
}

?>
