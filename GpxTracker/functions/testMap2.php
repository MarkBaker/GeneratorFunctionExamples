<?php

include('map.php');
include('mmap.php');


$array1 = [
    1, 2, 3
];

$array2 = [
    'A', 'B', 'C'
];


function display1($first) {
    echo (!empty($first) ? $first : ' '), PHP_EOL;
}

function display2($first, $second) {
    echo (!empty($first) ? $first : ' '), ' => ', (!empty($second) ? $second : ' '), PHP_EOL;
}

function display3($first, $second, $third) {
    echo (!empty($first) ? $first : ' '), ' => ', (!empty($second) ? $second : ' '), ' => ', (!empty($third) ? $third : ' '), PHP_EOL;
}

echo PHP_EOL;
echo 'array_map(display2)', PHP_EOL;

array_map('display2', $array1, $array2);

echo PHP_EOL;
echo 'foreach(array_map(display2))', PHP_EOL;

foreach(array_map('display2', $array1, $array2) as $value) {
}




function generator1($array) {
    foreach($array as $value) {
        yield $value;
    }
}


echo '----', PHP_EOL;
echo 'mmap(display1)', PHP_EOL;

$gen1 = generator1(range('A', 'D'));
foreach(mmap('display1', $gen1) as $value) {
}

echo PHP_EOL;
echo 'mmap(display2)', PHP_EOL;

$gen1 = generator1(range('A', 'D'));
$gen2 = generator1(range('E', 'G'));
foreach(mmap('display2', $gen1, $gen2) as $value) {
}

echo '----', PHP_EOL;
echo 'foreach(mmap(display3))', PHP_EOL;

$gen1 = generator1(range('A', 'D'));
$gen2 = generator1(range('E', 'G'));
$gen3 = generator1(range('H', 'L'));
foreach(mmap('display3', $gen1, $gen2, $gen3) as $value) {
}
