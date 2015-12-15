<?php

function isEmpty($value) {
    return !empty($value);
}

/**
 *  Version of filter to use with versions of PHP prior to 5.6.0, without the `$flag` option
 *  
 **/
function filter(Traversable $filter, Callable $callback = null) {
    if ($callback === null) {
        $callback = 'isEmpty';
    }

    foreach ($filter as $key => $value) {
        if ($callback($value)) {
            yield $key => $value;
        }
    }
}
