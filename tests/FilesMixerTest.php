<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class FilesMixerTest extends TestCase
{
    public const BASE = [
        'strategy_index' => 1,
        'details' => [
            'threshold' => 0.5,
            'buffer_size' => 6
        ],
        'model' => [
            "type" => "A",
            "unit" => 5,
            "max_age" => 5,
            "calculation_cost" => 0.3
        ],
    ];

    public function testEmailExample(): void
    {
        $config = [
            'strategy_index' => [1, 2, 3, 4, 5],
            'details' => [
                'threshold' => [0.1, 0.2],
            ],
            'model' => [
                'max_age' => [5, 10, 15, 20],
                'type' => ["A", "B", "C"]
            ]
        ];

        $output = (new FilesMixer(self::BASE, $config))->build()->toArray();

        $this->assertCount(120, $output);
    }

    public function testConfigWithValuesThatAreNotArrays()
    {
        $config = [
            'strategy_index' => 1,
            'details' => [
                'threshold' => [0.1, 0.2],
            ],
            'model' => [
                'max_age' => [5, 10, 15, 20],
                'type' => ["A", "B", "C"]
            ]
        ];

        $output = (new FilesMixer(self::BASE, $config))->build()->toArray();

        $this->assertCount(24, $output);
    }

    public function testVeryDeepObject()
    {
        $config = [
            'details' => [
                'threshold' => [0.1, 0.2],
                'some_value' => [
                    'some_deeper_value' => [
                        'even_deeper_value' => [
                            'final_deep' => ['A', 2, 3.5]
                        ]
                    ]
                ]
            ],
            'model' => [
                'max_age' => [5, 10, 15, 20],
                'type' => ["A", "B", "C"]
            ]
        ];

        $output = (new FilesMixer(self::BASE, $config))->build()->toArray();

        $this->assertCount(72, $output);
    }

    public function testPropertiesThatNotInBase()
    {
        $config = [
            'measurements' => [
                'wood' => ['100x100', '50x50', '25x25']
            ],
            'species' => ['oak', 'birch', 'spring', 'acacia']

        ];

        $output = (new FilesMixer(self::BASE, $config))->build()->toArray();

        $this->assertCount(12, $output);
    }
    
//    public function testArraysInArrays()
//    {
//        $config = [
//            'strategy_index' => [1, 2, 3, 4],
//            'details' => [
//                'threshold' => [[0.1, 0.2], [0.3, 0.4]],
//            ],
//        ];
//
//        $output = (new FilesMixer(self::BASE, $config))->build()->toArray();
//
//        $this->assertCount(8, $output);
//    }

}