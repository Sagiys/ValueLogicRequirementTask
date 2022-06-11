<?php

declare(strict_types=1);
require 'vendor/autoload.php';

$baseFileName = readline('Enter filename of base .json file (default: base.json): ');
$configFileName = readline('Enter filename of config .json file (default: config.json): ');

$baseFileName = $baseFileName === '' ? 'base.json' : $baseFileName;
$configFileName = $configFileName === '' ? 'config.json' : $configFileName;

$source = json_decode(file_get_contents("input/{$baseFileName}"), true, 512, JSON_THROW_ON_ERROR);
$config = json_decode(file_get_contents("input/{$configFileName}"), true, 512, JSON_THROW_ON_ERROR);

$fileMixer = new \FilesMixer($source, $config);
$builtConfigs = $fileMixer->build();

$output = $builtConfigs->toArray();

//file generation
$builtConfigs->toFiles();
