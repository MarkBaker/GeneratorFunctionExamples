<?php

namespace GeneratorHelper;

class fluent {
    private $generator;

    private $filters = [];

    private $mappings = [];

    private $offset = 0;
    private $limit = null;

    public function __construct(\Generator $generator) {
        $this->generator = $generator;
    }

    public function offset($offset = 0) {
        $this->offset = $offset;

        return $this;
    }

    public function limit($limit = 1) {
        $this->limit = $limit;

        return $this;
    }

    public function map(Callable $callback) {
        $this->mappings[] = $callback;

        return $this;
    }

    private function applyMappings($value) {
        foreach($this->mappings as $callback) {
            $valid = $callback($value);
        }
    }

    public function filteredBy(Callable $callback, $flag = 0) {
        $this->filters[] = new \GeneratorHelper\filter($callback, $flag);

        return $this;
    }

    private function applyFilters($key, $value) {
        $valid = true;
        foreach($this->filters as $filter) {
            $callback = $filter->callback;
            switch($filter->flag) {
                case ARRAY_FILTER_USE_KEY:
                    $valid = $callback($key);
                    break;
                case ARRAY_FILTER_USE_BOTH:
                    $valid = $callback($value, $key);
                    break;
                default:
                    $valid = $callback($value);
                    break;
            }
            if (!$valid) {
                break;
            }
        }

        return $valid;
    }

    public function do(Callable $function) {
        foreach($this->generator as $key => $value) {
            if ($this->applyFilters($key, $value)) {
                if ($this->offset > 0) {
                    --$this->offset;
                } elseif(is_null($this->limit) || $this->limit > 0) {
                    $this->applyMappings($value);
                    $function($key, $value);
                    if (!is_null($this->limit)) {
                        --$this->limit;
                    }
                }
            }
        }
    }
}
