<?php

namespace App\Models\Users;

use App\Models\Schools\School;
use App\Models\Schools\Campus;
use App\Models\Schools\Department;
use App\Models\Schools\Grade;
use App\Models\Schools\Institute;
use App\Models\Schools\Major;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class GradeUser extends Model
{
    protected $fillable = [
        'user_id',
        'school_id',
        'campus_id',
        'institute_id',
        'department_id',
        'major_id',
        'grade_id',
        'last_updated_by',
    ];

    /**
     * 关联的学校
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function school(){
        return $this->belongsTo(School::class);
    }

    /**
     * 关联的校区
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function campus(){
        return $this->belongsTo(Campus::class);
    }

    /**
     * 关联的学院
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institute(){
        return $this->belongsTo(Institute::class);
    }

    /**
     * 关联的系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function department(){
        return $this->belongsTo(Department::class);
    }

    /**
     * 关联的专业
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function major(){
        return $this->belongsTo(Major::class);
    }

    /**
     * 关联的班级
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function grade(){
        return $this->belongsTo(Grade::class);
    }

    /**
     * 关联的用户, 老师/学生/商户
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(){
        return $this->belongsTo(User::class);
    }
}