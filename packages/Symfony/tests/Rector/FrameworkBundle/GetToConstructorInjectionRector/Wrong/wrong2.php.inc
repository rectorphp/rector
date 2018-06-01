<?php declare (strict_types=1);

use App\Service\Anything;
use Rector\Symfony\Tests\Rector\FrameworkBundle\Source\SymfonyController;

class ClassWithNamedService extends SymfonyController
{
    public function render()
    {
        $this->renderTwig([
            'posts' => $this->get(\App\Service\Something::class)->callMe(),
            'comments' => $this->get(Anything::class)->callMe(),
        ]);
    }
}
