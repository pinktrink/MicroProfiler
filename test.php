<?php
include 'microprofiler.php';
$x = new microprofiler('checkpoint 0');
$x->dump_on_jump(true, array(false, '', "\n", "\n", 5), "\n");

declare(ticks = 1){
	for($i = 0; $i < 100; $i++) $y[] = 'jfiowfjiefweio';
}

$x->jump('checkpoint 1');

declare(ticks = 1){
	$a = 1;
	$a++;
	$a++;
	$a++;
	$a++;
	$a++;
	$a++;
	$a++;
	$a++;
	$a++;
	$a++;
	$a++;
	$a++;
	$a++;
	$a++;
	$a++;
	$a++;
	$a++;
	$a++;
	$a++;
	$a++;
	$a++;
	$a++;
	$a++;
	$a++;
}

$x->jump('checkpoint 2');

declare(ticks = 1){
	$a++;
}

$x->jump('checkpoint 1');

declare(ticks = 1){
	$a++;
	$a++;
	$a++;
}

$x->jump('checkpoint 3');

$a++;
$a++;
$a++;

declare(ticks = 1){$a++;}

$x->jump('checkpoint 1');

declare(ticks = 1){$a++;}

declare(ticks = 1){
	$a++;
	$a++;
	$a++;
	$a++;
	$a++;
	$a++;
	$a++;
}

$x->jump('checkpoint 4');

declare(ticks = 1){
	for($i = 0; $i < 25; $i++) $a++;
	for($i = 0; $i < 50; $i++) $a++;
	for($i = 0; $i < 20; $i++) $a++;
	$a++;
	$a++;
	$a++;
	$a++;
	$a++;
}

$x->jump('checkpoint 5');

declare(ticks = 1){
	$a++;
	$a++;
	$a++;
}

$a++;
$a++;
$a++;

$x->disable();

declare(ticks = 1){
	$a++;
	$a++;
	$x->enable();
	$a++;
}

$x->dump(false, '', "\n", "\n", 5);