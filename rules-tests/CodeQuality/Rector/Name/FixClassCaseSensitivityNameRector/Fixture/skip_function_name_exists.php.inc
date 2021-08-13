<?php

declare(strict_types = 1);

namespace X {
    class Y {
    }

    function y() {
        echo 'test';
    }
}

namespace Test {
	use function X\y;
    
    y();
}
?>
