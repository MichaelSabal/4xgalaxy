<?php
if (isset($_SERVER['REQUEST_METHOD'])) {
	http_response_code(404);
	readfile('404.html');
	exit(1); // This file can only be run from the command line
}

?>