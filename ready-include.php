<?php

/*
 * This file is a part of the Lorhondel project.
 *
 * Copyright (c) 2021-present Valithor Obsidion <valzargaming@gmail.com>
 */

echo "[READY] Logged in as {$lorhondel->discord->user->username}#{$lorhondel->discord->user->discriminator} ({$lorhondel->discord->user->id})".PHP_EOL;

echo "[DATE] ";
$dt = new DateTime("now");  // convert UNIX timestamp to PHP DateTime
echo $dt->format('d-m-Y H:i:s') . PHP_EOL; // output = 2017-01-01 00:00:00

$lorhondel->discord->on('message', function ($message) use ($lorhondel, $loop, $token, $stats, /*$connector,*/ $browser) { //Handling of a message
	include 'message-include.php';
}); //end small function with content

$lorhondel->discord->on("error", function(\Throwable $e) {
	echo '[ERROR]' . $e->getMessage() . " in file " . $e->getFile() . " on line " . $e->getLine() . PHP_EOL;
});