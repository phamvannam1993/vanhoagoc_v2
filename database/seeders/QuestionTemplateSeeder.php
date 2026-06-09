<?php

namespace Database\Seeders;

use App\Models\QuestionTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionTemplateSeeder extends Seeder
{
    public function run()
    {
//        DB::table('question_templates')->truncate();
//
//        $data = [];
//
//        $types = [
//            [
//                'label' => 'Game chọn đáp án đúng',
//                'type' => 'game_chon',
//                'template' => [
//                    [
//                        'img' => 'game_chon_1.jpg',
//                        'template' => [
//                            'totalInput' => 2,
//                            'componentMap' => 'PreviewChooseCorrectOne'
//                        ],
//                    ],
//                    [
//                        'img' => 'game_chon_2.jpg',
//                        'template' => [
//                            'totalInput' => 3,
//                            'componentMap' => 'PreviewChooseCorrectTwo'
//                        ],
//                    ],
//                    [
//                        'img' => 'game_chon_3.jpg',
//                        'template' => [
//                            'totalInput' => 4,
//                            'componentMap' => 'PreviewChooseCorrectThree'
//                        ],
//                    ],
//                    [
//                        'img' => 'game_chon_4.jpg',
//                        'template' => [
//                            'totalInput' => 4,
//                            'componentMap' => 'PreviewChooseCorrectFour'
//                        ],
//                    ],
//                    [
//                        'img' => 'game_chon_5.jpg',
//                        'template' => [
//                            'totalInput' => 6,
//                            'componentMap' => 'PreviewChooseCorrectFive'
//                        ],
//                    ],
//                    [
//                        'img' => 'game_chon_6.jpg',
//                        'template' => [
//                            'totalInput' => 3,
//                            'componentMap' => 'PreviewChooseCorrectSix'
//                        ],
//                    ],
//                    [
//                        'img' => 'game_chon_7.jpg',
//                        'template' => [
//                            'totalInput' => 4,
//                            'componentMap' => 'PreviewChooseCorrectSeven'
//                        ],
//                    ],
//                    [
//                        'img' => 'game_chon_8.jpg',
//                        'template' => [
//                            'totalInput' => 3,
//                            'componentMap' => 'PreviewChooseCorrectEight'
//                        ],
//                    ],
//                    [
//                        'img' => 'game_chon_9.jpg',
//                        'template' => [
//                            'totalInput' => 4,
//                            'componentMap' => 'PreviewChooseCorrectNine'
//                        ],
//                    ],
//                    [
//                        'img' => 'game_chon_10.jpg',
//                        'template' => [
//                            'totalInput' => 3,
//                            'componentMap' => 'PreviewChooseCorrectTen'
//                        ],
//                    ],
//                    [
//                        'img' => 'game_chon_11.jpg',
//                        'template' => [
//                            'totalInput' => 4,
//                            'componentMap' => 'PreviewChooseCorrectEleven'
//                        ],
//                    ],
//                    [
//                        'img' => 'game_chon_12.jpg',
//                        'template' => [
//                            'totalInput' => 4,
//                            'componentMap' => 'PreviewChooseCorrectTwelve'
//                        ],
//                    ]
//                ]
//            ],
//            [
//                'label' => 'Game kéo',
//                'type' => 'game_keo',
//                'template' => [
//                    [
//                        'img' => 'game_keo_1.jpg',
//                        'template' => [
//                            'totalInput' =>  2,
//                            'componentMap' => 'PreviewDragDropOne'
//                        ]
//                    ],
//                    [
//                        'img' => 'game_keo_2.jpg',
//                        'template' => [
//                            'totalInput' =>  3,
//                            'componentMap' => 'PreviewDragDropTwo'
//                        ],
//                    ],
//                    [
//                        'img' => 'game_keo_3.jpg',
//                        'template' => [
//                            'totalInput' =>  4,
//                            'componentMap' => 'PreviewDragDropThree'
//                        ],
//                    ],
//                    [
//                        'img' => 'game_keo_4.jpg',
//                        'template' => [
//                            'totalInput' =>  4,
//                            'componentMap' => 'PreviewDragDropFour'
//                        ],
//                    ],
//                ]
//            ],
//            [
//                'label' => 'Game nối',
//                'type' => 'game_noi',
//                'template' => [
//                    [
//                        'img' => 'game_noi_1.jpg',
//                        'template' => [
//                            'totalInputQuestion' => 3,
//                            'totalInputAnswer' => 3,
//                            'componentMap' => 'PreviewConnectSentenceOne'
//                        ],
//                    ],
//                    [
//                        'img' => 'game_noi_2.jpg',
//                        'template' => [
//                            'totalInputQuestion' => 3,
//                            'totalInputAnswer' => 3,
//                            'componentMap' => 'PreviewConnectSentenceTwo'
//                        ],
//                    ],
//                    [
//                        'img' => 'game_noi_3.jpg',
//                        'template' => [
//                            'totalInputQuestion' => 3,
//                            'totalInputAnswer' => 3,
//                            'componentMap' => 'PreviewConnectSentenceThree'
//                        ],
//                    ],
//                    [
//                        'img' => 'game_noi_4.jpg',
//                        'template' => [
//                            'totalInputQuestion' => 4,
//                            'totalInputAnswer' => 4,
//                            'componentMap' => 'PreviewConnectSentenceFour'
//                        ],
//                    ],
//                    [
//                        'img' => 'game_noi_5.jpg',
//                        'template' => [
//                            'totalInputQuestion' => 4,
//                            'totalInputAnswer' => 4,
//                            'componentMap' => 'PreviewConnectSentenceFive'
//                        ],
//                    ],
//                    [
//                        'img' => 'game_noi_6.jpg',
//                        'template' => [
//                            'totalInputQuestion' => 4,
//                            'totalInputAnswer' => 4,
//                            'componentMap' => 'PreviewConnectSentenceSix'
//                        ],
//                    ],
//                    [
//                        'img' => 'game_noi_7.jpg',
//                        'template' => [
//                            'totalInputQuestion' => 2,
//                            'totalInputAnswer' => 3,
//                            'componentMap' => 'PreviewConnectSentenceSeven'
//                        ],
//                    ],
//                    [
//                        'img' => 'game_noi_8.jpg',
//                        'template' => [
//                            'totalInputQuestion' => 2,
//                            'totalInputAnswer' => 4,
//                            'componentMap' => 'PreviewConnectSentenceEight'
//                        ],
//                    ],
//                    [
//                        'img' => 'game_noi_9.jpg',
//                        'template' => [
//                            'totalInputQuestion' => 2,
//                            'totalInputAnswer' => 4,
//                            'componentMap' => 'PreviewConnectSentenceNine'
//                        ],
//                    ]
//                ]
//            ],
//            [
//                'label' => 'Game sắp xếp',
//                'type' => 'game_sap_xep',
//                'template' => [
//                    [
//                        'img' => 'game_sap_xep_1.jpg',
//                        'template' => [
//                            'totalInput' => 3,
//                            'componentMap' => 'PreviewArrangeOne'
//                        ],
//                    ],
//                    [
//                        'img' => 'game_sap_xep_2.jpg',
//                        'template' => [
//                            'totalInput' => 4,
//                            'componentMap' => 'PreviewArrangeTwo'
//                        ],
//                    ],
//                    [
//                        'img' => 'game_sap_xep_3.jpg',
//                        'template' => [
//                            'totalInput' => 5,
//                            'componentMap' => 'PreviewArrangeThree'
//                        ],
//                    ]
//                ]
//            ],
//            [
//                'label' => 'Game ghép ảnh',
//                'type' => 'game_ghep_anh',
//                'template' => [
//                    [
//                        'img' => 'game_ghep_anh_1.jpg',
//                        'template' => []
//                    ]
//                ]
//            ],
//        ];
//
//        foreach ($types as $type) {
//            foreach ($type['template'] as $template) {
//                $data[] = [
//                    'type' => $type['type'],
//                    'type_label' => $type['label'],
//                    'image' => $template['img'],
//                    'template' => json_encode($template['template']),
//                    'created_at' => now(),
//                    'updated_at' => now()
//                ];
//            }
//        }
//
//        DB::table('question_templates')->insert($data);
        $batchSize = 100;
        $counter = 1;
        $step = 1000;
        $start = now()->timestamp * 1000;

        QuestionTemplate::orderBy('id')->whereNull('sort_number')->chunk($batchSize, function ($questionTemplates) use (&$counter, $step, $start) {
            foreach ($questionTemplates as $questionTemplate) {
                $questionTemplate->sort_number = $start + ($counter * $step);
                $questionTemplate->save();
                $counter++;
            }
        });
    }
}
