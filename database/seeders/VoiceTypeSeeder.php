<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VoiceTypeSeeder extends Seeder
{
    public function run()
    {
        DB::table('voice_types')->truncate();

        $data = [
            [
                'id' => 1,
                'name' => 'HN - Ngọc Lan',
                'type' => 'hn_female_hermer_stor_48k-fhg',
            ],
            [
                'id' => 2,
                'name' => 'Hue - Hương Giang',
                'type' => 'hue_female_huonggiang_full_48k-fhg',
            ],
            [
                'id' => 3,
                'name' => 'SG - Thảo Trinh',
                'type' => 'sg_female_thaotrinh_full_48k-fhg',
            ],
            [
                'id' => 4,
                'name' => 'HN - Mạnh Dũng',
                'type' => 'hn_male_manhdung_news_48k-fhg',
            ],
            [
                'id' => 5,
                'name' => 'SG - Trung Kiên',
                'type' => 'sg_male_trungkien_vdts_48k-fhg',
            ],
            [
                'id' => 6,
                'name' => 'Hue - Duy Phương',
                'type' => 'hue_male_duyphuong_full_48k-fhg',
            ],
        ];

        DB::table('voice_types')->insert($data);
    }
}
