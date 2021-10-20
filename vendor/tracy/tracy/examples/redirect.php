<?php

declare (strict_types=1);
namespace RectorPrefix20211020;

require __DIR__ . '/../src/tracy.php';
use RectorPrefix20211020\Tracy\Debugger;
// session is required for this functionality
\session_start();
// For security reasons, Tracy is visible only on localhost.
// You may force Tracy to run in development mode by passing the Debugger::DEVELOPMENT instead of Debugger::DETECT.
\RectorPrefix20211020\Tracy\Debugger::enable(\RectorPrefix20211020\Tracy\Debugger::DETECT, __DIR__ . '/log');
if (empty($_GET['redirect'])) {
    \RectorPrefix20211020\bdump('before redirect ' . \date('H:i:s'));
    \header('Location: ' . (isset($_GET['ajax']) ? 'ajax.php' : 'redirect.php?&redirect=1'));
    exit;
}
\RectorPrefix20211020\bdump('after redirect ' . \date('H:i:s'));
?>
<!DOCTYPE html><html class=arrow><link rel="stylesheet" href="assets/style.css">

<h1>Tracy: redirect demo</h1>

<p><a href="?">redirect again</a> or <a href="?ajax">redirect to AJAX demo</a></p>

<?php 
if (\RectorPrefix20211020\Tracy\Debugger::$productionMode) {
    echo '<p><b>For security reasons, Tracy is visible only on localhost. Look into the source code to see how to enable Tracy.</b></p>';
}
