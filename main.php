<?php

declare(strict_types=1);
require 'vendor/autoload.php';

class FilesMixer
{
    private array $result = [];

    public function __construct(
        public array $source,
        public array $config
    )
    {
        ray()->clearAll();
    }

    public function build(): array
    {
        $base = $this->dot($this->source);

        $options = $this->dot($this->config, ignoreIfLastLayerIsArray: true);

        $cross = [];

        $totalNumberOfCombinations = 1;
        $breaks = [];
        foreach ($options as $key => $values) {
            $countOfValues = count($values);
            $breaks [$key] = $countOfValues;
            $totalNumberOfCombinations *= $countOfValues;
        }

        foreach ($options as $key => $values) {
            foreach ($values as $valueKey => $value) {
                for ($i = 0; $i < $totalNumberOfCombinations / $breaks[$key]; $i++) {
                    $cross[$i * $breaks[$key] + $valueKey] [$key] = $value;
                }
            }
        }

        foreach ($cross as $crossedOption) {
            $this->result [] = array_merge($base, $crossedOption);
        }

        return $this->result;
    }

    // "Borrowed" from Official Laravel Repository with my ugly twist added
    private function dot($array, $prepend = '', $ignoreIfLastLayerIsArray = false)
    {
        $results = [];

        foreach ($array as $key => $value) {
            $areAllValuesAnArray = false;
            if ($ignoreIfLastLayerIsArray) {
                if (is_array($value) && !empty($value)) {
                    foreach ($value as $items) {
                        if (is_array($items)) {
                            $areAllValuesAnArray = true;
                            break;
                        }
                    }
                }
            }
            if (is_array($value) && !empty($value) && (!$ignoreIfLastLayerIsArray || $areAllValuesAnArray)) {
                $results = array_merge($results, $this->dot($value, $prepend . $key . '.', $ignoreIfLastLayerIsArray));
            } else {
                $results[$prepend . $key] = $value;
            }
        }

        return $results;
    }

    // "borrowed" from https://github.com/DivineOmega/array_undot

    private function undot(array $dotNotationArray)
    {
        $array = [];
        foreach ($dotNotationArray as $key => $value) {
            $this->set($array, $key, $value);
        }

        return $array;
    }

    // "borrowed" from https://github.com/DivineOmega/array_undot
    private function set(&$array, $key, $value)
    {
        if (is_null($key)) {
            return $array = $value;
        }
        $keys = explode('.', $key);
        while (count($keys) > 1) {
            $key = array_shift($keys);
            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }
            $array = &$array[$key];
        }
        $array[array_shift($keys)] = $value;

        return $array;
    }

}


$source = [
    'strategy_index' => 1,
    'details' => [
        'threshold' => 0.5,
        'buffer_size' => 6
    ],
    'model' => [
        'type' => "A",
        'unit' => 5,
        'max_age' => 5,
        'calculation_cost' => 0.3
    ]
];

$config = [
    'strategy_index' => [1, 2, 3, 4, 5],
    'details' => [
        'threshold' => [0.1, 0.2],
    ],
    'model' => [
        'max_age' => [5, 10, 15, 20],
        'type' => ["A", 'B', 'C'],
    ],
];

$fileMixer = new FilesMixer($source, $config);
$output = $fileMixer->build();

ray(count($output));
ray($output);
