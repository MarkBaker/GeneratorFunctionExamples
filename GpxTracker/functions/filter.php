<?php

function notEmpty($value) {
    return !empty($value);
}

/**
 *  The `$flag` option (and the constants ARRAY_FILTER_USE_KEY and ARRAY_FILTER_USE_BOTH) were introduced in PHP 5.6.0
 *  
 **/
function filter(Traversable $filter, Callable $callback = null, $flag = 0) {
    if ($callback === null) {
        $callback = 'notEmpty';
    }

    foreach ($filter as $key => $value) {
        switch($flag) {
            case ARRAY_FILTER_USE_KEY:
                if ($callback($key)) {
                    yield $key => $value;
                }
                break;
            case ARRAY_FILTER_USE_BOTH:
                if ($callback($value, $key)) {
                    yield $key => $value;
                }
                break;
            default:
                if ($callback($value)) {
                    yield $key => $value;
                }
                break;
        }
    }
}
