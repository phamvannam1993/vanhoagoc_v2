<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\PointConstant;
use App\Models\Book;
use App\Models\Event;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type_id',
        'tel',
        'status',
        'img',
        'username',
        'address',
        'birthday',
        'user_id_app',
        'create_id',
        'create_by'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'created_at' => 'datetime:d/m/Y H:i',
    ];

    protected $appends = ['avatar'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function userFollow()
    {
        return $this->belongsTo(UserFollow::class, "id", 'user_follow_id');
    }

    public function userInfo()
    {
        return $this->belongsTo(UserInfo::class, "id", 'user_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, "user_id", 'id');
    }

    public function userApp()
    {
        return $this->hasMany(Role::class, 'user_id', 'id');
    }

    public function userType()
    {
        return $this->belongsTo(UserType::class, 'user_type_id', 'id');
    }

    public function directorApp()
    {
        return $this->hasOne(App::class, 'user_id', 'id');
    }

    public function directorApps()
    {
        return $this->hasMany(App::class, 'user_id', 'id');
    }

    public function teacherApp()
    {
        return $this->hasMany(Classes::class, 'user_id', 'id');
    }

    public function teacherCourses()
    {
        return $this->hasMany(Book::class, 'user_id', 'id');
    }

    public function teacherEventRoles()
    {
        return $this->hasMany(Role::class, 'user_id', 'id')
            ->where('permission', 'like', 's_%');
    }

    public function teacherEvents()
    {
        return $this->belongsToMany(
            Event::class,
            'role',
            'user_id',
            'id'
        )->whereRaw("role.permission = CONCAT('s_', events.id)");
    }

    public function studentApp()
    {
        return $this->hasManyThrough(App::class,UserClass::class,'user_id','id','id', 'app_id');
    }

    public function classes()
    {
        return $this->belongsToMany(Classes::class, 'users_classes', 'user_id', 'class_id');
    }

    public function studentClass()
    {
        return $this->hasOne(UserClass::class, 'user_id');
    }

    public function practicesClasses()
    {
        return $this->hasMany(PracticeClass::class, 'user_id');
    }

    public function bestPoint()
    {
        return $this->hasOne(Point::class)
            ->where('type', 1)
            ->orderByDesc('star_count')
            ->orderBy('time');
    }

    public function bestPointClass()
    {
        return $this->hasOne(Point::class)
            ->orderByDesc('star_count')
            ->orderBy('time');
    }

    public function bestPointsPerPractice()
    {
        return $this->hasMany(Point::class)
            ->select('id', 'user_id', 'practice_id', 'star_count', 'time', 'class_id', 'type', 'is_assign');
    }

    ////////////////////////////////
    public function isAdmin()
    {
        return $this->userType->type == UserType::TYPE_ADMIN;
    }

    public function isEditor()
    {
        return $this->userType->type == UserType::TYPE_EDITOR;
    }

    public function isTeacher()
    {
        return $this->userType->type == UserType::TYPE_TEACHER;
    }

    public function getAvatarAttribute()
    {
        if (!$this->img) {
            return asset('images/icon-avatar-default.png');
        }
        if (str_starts_with($this->img, 'http')) {
            return $this->img;
        }
        return asset('storage/' . $this->img);
    }
}
