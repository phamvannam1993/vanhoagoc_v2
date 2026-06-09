<?php

namespace App\Services;

use App\Repositories\ActiveCodeRepository;

class ActiveCodeService
{

    private $activeCodeRepository;

    public function __construct(
        ActiveCodeRepository $activeCodeRepository
    ) {
        $this->activeCodeRepository = $activeCodeRepository;
    }

    public function getList($params = [])
    {
        return $this->activeCodeRepository->searchByFilters($params);
    }

    public function generateCode($quantity)
    {
        $activeCodes = [];
        $codes = $this->generateRandomCodes($quantity, 8);
        foreach($codes as $code) {
            $dataSave = [
                'code' => $code,
                'is_used' => 0
            ];
            $activeCodes[] = $this->activeCodeRepository->create($dataSave);
        }
        return $activeCodes;
    }

    function generateRandomCodes($quantity, $length = 8, $prefix = '') {
        $codes = [];
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        for ($i = 0; $i < $quantity; $i++) {
            $code = '';
            for ($j = 0; $j < $length; $j++) {
                $code .= $characters[rand(0, strlen($characters) - 1)];
            }
            $codes[] = $prefix.$code;
        }
        return $codes;
    }

    public function generateCodeFromTool($quantity, $length = 8, $prefix = 'T-')
    {
        return $this->generateRandomCodes($quantity, $length, $prefix);
    }
}
