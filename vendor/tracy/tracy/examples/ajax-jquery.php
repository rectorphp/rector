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
if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    // AJAX request
    \RectorPrefix20211020\bdump('AJAX request ' . \date('H:i:s'));
    if (!empty($_GET['error'])) {
        \RectorPrefix20211020\this_is_fatal_error();
    }
    $data = [\rand(), \rand(), \rand()];
    \header('Content-Type: application/json');
    \header('Cache-Control: no-cache');
    echo \json_encode($data);
    exit;
}
\RectorPrefix20211020\bdump('classic request ' . \date('H:i:s'));
?>
<!DOCTYPE html><html class=arrow><link rel="stylesheet" href="assets/style.css">

<h1>Tracy: AJAX demo</h1>

<p>
	<button>AJAX request</button> <span id=result>see Debug Bar in the bottom right corner</span>
</p>

<p>
	<button class=error>Request with error</button> use ESC to toggle BlueScreen
</p>


<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script>

// default settings:
// window.TracyAutoRefresh = true;
// window.TracyMaxAjaxRows = 3;

var jqxhr;

$('button').click(function() {
	$('#result').text('loading…');

	if (jqxhr) {
		jqxhr.abort();
	}

	jqxhr = $.ajax({
		data: {error: $(this).hasClass('error') * 1},
		dataType: 'json',
		jsonp: false,
		// headers: {'X-Tracy-Ajax': Tracy.getAjaxHeader()}, // use when auto-refresh is disabled via window.TracyAutoRefresh = false;
	}).done(function(data) {
		$('#result').text('loaded: ' + data);

	}).fail(function() {
		$('#result').text('error');
	});
});


</script>


<?php 
if (\RectorPrefix20211020\Tracy\Debugger::$productionMode) {
    echo '<p><b>For security reasons, Tracy is visible only on localhost. Look into the source code to see how to enable Tracy.</b></p>';
}
