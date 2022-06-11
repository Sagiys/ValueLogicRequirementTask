<?php declare(strict_types=1);

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

    public function build(): self
    {
        $base = $this->dot($this->source);

        $options = $this->dot($this->config, ignoreIfLastLayerIsArray: true);

        $cross = [];

        //ensure that config array has only arrays as values
        foreach ($options as $key => $values) {
            if (!is_array($values)) {
                $options[$key] = [$values];
            }
        }

        $totalNumberOfCombinations = 1;
        // array with sizes of config arrays with dotted keys for access
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
            $this->result [] = $this->undot(array_merge($base, $crossedOption));
        }

        return $this;
    }

    public function toArray(): array
    {
        return $this->result;
    }

    public function toFiles()
    {
        $this->ensureEmptyBuildDirectoryExists();

        foreach ($this->result as $key => $result) {
            $filename = $key + 1;
            file_put_contents("build/{$filename}.json", json_encode($result));
        }
    }

    private function ensureEmptyBuildDirectoryExists()
    {
        $path = realpath('build');

        if ($path === false) {
            mkdir('build', 0755);
        }

        array_map('unlink', glob("build/*.*"));
    }

    // "Borrowed" from Official Laravel Repository
    private function dot($array, $prepend = '', $ignoreIfLastLayerIsArray = false)
    {
        $results = [];

        foreach ($array as $key => $value) {
            // this ugly check ic creation of mine
            // it checks if all elements of array aren't arrays
            // basically if it's second to last element for example: threshold => [1,2,3]
            // instead of making it like this:
            // threshold.0 => 1
            // threshold.1 => 2
            // threshold.2 => 3
            // it leaves array without change
            $isAnyValueAnArray = false;
            if ($ignoreIfLastLayerIsArray) {
                if (is_array($value) && !empty($value)) {
                    foreach ($value as $items) {
                        if (is_array($items)) {
                            $isAnyValueAnArray = true;
                            break;
                        }
                    }
                }
            }
            if (is_array($value) && !empty($value) && (!$ignoreIfLastLayerIsArray || $isAnyValueAnArray)) {
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
