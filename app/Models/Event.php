<?php

namespace App\Models;

use App\Enums\EventConstant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function practices()
    {
        return $this->belongsToMany(Practice::class);
    }

    public function class()
    {
        return $this->belongsTo(\App\Models\Classes::class, 'class_id');
    }

    public function eventPoints()
    {
        return $this->hasMany(EventPoint::class);
    }

    /////////////
    public function getEventStatus($returnLabel = false)
    {
        if ($this->status === EventConstant::STATUS_DRAFT) {
            return $returnLabel ? 'Nháp' : 0;
        }

        $now = Carbon::now();

        if ($now->lt($this->start_datetime)) {
            return $returnLabel ? 'Sắp diễn ra' : 1;
        }

        if ($now->between($this->start_datetime, $this->end_datetime)) {
            return $returnLabel ? 'Đang diễn ra' : 2;
        }

        return $returnLabel ? 'Đã kết thúc' : 3;
    }

    public function canDelete(): bool
    {
        // Draft: always deletable
        if ($this->status === EventConstant::STATUS_DRAFT) {
            return true;
        }
        // Ended (end_datetime passed): deletable
        if (Carbon::now()->gt($this->end_datetime)) {
            return true;
        }
        // Ongoing: not deletable
        return false;
    }

    public function getStartDatetimeLabel()
    {
        return Carbon::parse($this->start_datetime)->format('d/m/Y H:i:s');
    }

    public function getEndDatetimeLabel()
    {
        return Carbon::parse($this->end_datetime)->format('d/m/Y H:i:s');
    }
}
