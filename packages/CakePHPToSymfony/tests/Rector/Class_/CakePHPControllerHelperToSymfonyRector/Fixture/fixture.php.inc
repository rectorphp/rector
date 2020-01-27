<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPControllerHelperToSymfonyRector\Fixture;

class HomepageController extends \AppController
{
    public $helpers = ['Flash'];

    public function index()
    {
        $this->Flash->success(__('Your post has been saved.'));
        $this->Flash->error(__('Unable to add your post.'));
    }
}

?>
-----
<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPControllerHelperToSymfonyRector\Fixture;

class HomepageController extends \AppController
{
    public function index()
    {
        $this->addFlash('success', __('Your post has been saved.'));
        $this->addFlash('error', __('Unable to add your post.'));
    }
}

?>
