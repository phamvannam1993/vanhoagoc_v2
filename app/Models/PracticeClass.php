<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PracticeClass extends Model
{
    use HasFactory;

    protected $table = 'practices_classes';
    protected $guarded = [];

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d/m/Y');
    }

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function users()
    {
        return $this->hasMany(UserClass::class, 'class_id', 'class_id');
    }

    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    public function week()
    {
        return $this->belongsTo(Week::class, 'week_id');
    }

    public function practice()
    {
        return $this->belongsTo(Practice::class, 'practice_id');
    }

    //////////////////////////////////////////////////

    public function getPointDetailAnswer()
    {
        $practicePoints = data_get($this, 'practice.practicePoints');

        if (empty($practicePoints)) {
            return [];
        }else {
            $totalCorrect = 0;
            foreach ($practicePoints as $practicePoint) {
                $totalPoint += $practicePoint->pointDetails->count();
                $totalCorrect += $practicePoint->pointDetails->sum('star_count');
            }

            return [$totalCorrect, $totalPoint];
        }
    }

    public function getStatusLabel()
    {
        $now = new \DateTime();
        if (!empty($this->to)) {
            $endDate = new \DateTime($this->to);
            if($endDate < $now) {
                return 'Đã hết hạn';
            }
        }

        $practicePoints = data_get($this, 'practice.practicePoints', collect([]));

        if ($practicePoints->isEmpty()) {
            return 'Chưa thực hiện';
        }else {
            $totalCorrect = 0;
            foreach ($practicePoints as $practicePoint) {
                $totalCorrect += $practicePoint->pointDetails->sum('star_count');
            }
            return 'Đã thực hiện';
        }
    }
}
