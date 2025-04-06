<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no'); // nginx

@ob_end_clean();
set_time_limit(0);
ob_implicit_flush(true);

// força início do streaming
echo str_repeat(' ', 1024) . "\n";
flush();

$path = './scripts';
$cmd = "cd $path && npm run start 2>&1";

$process = popen($cmd, 'r');


while(!feof($process)) {
    $line = fgets($process);
    echo 'data: ' . trim($line) . "\n\n";
    flush();
    usleep(1000000);
}


pclose($process);

echo "event: done\ndata: done\n\n";
flush();

