<?php

namespace Rector\Tests\Issues\Issue835\Fixture {
    use Cake\View\ViewBuilder;

    final class SomeController
    {
        public function view($id = null)
        {
            $this->viewBuilder()->layout('ajax');
        }

        public function viewBuilder(): ViewBuilder
        {
        }
    }
}

namespace Cake\View {
    class ViewBuilder { }
}

?>
-----
<?php

namespace Rector\Tests\Issues\Issue835\Fixture {
    use Cake\View\ViewBuilder;

    final class SomeController
    {
        public function view($id = null)
        {
            $this->viewBuilder()->setLayout('ajax');
        }

        public function viewBuilder(): ViewBuilder
        {
        }
    }
}

namespace Cake\View {
    class ViewBuilder { }
}

?>
