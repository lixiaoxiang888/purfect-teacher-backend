<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Students\StudentProfile;
use App\User;
use App\Models\RecruitStudent\RegistrationInformatics;
use Faker\Generator as Faker;
use App\Models\Acl\Role;
use Illuminate\Support\Facades\Hash;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;
use App\Models\Schools\RecruitmentPlan;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(User::class, function (Faker $faker) {
    return [
        'mobile'=>'3333'.rand(1, 99).rand(1, 99),
        'name' => $faker->name,
        'uuid'=>Uuid::uuid4()->toString(),
        'api_token'=>Uuid::uuid4()->toString(),
        'password'=>Hash::make('ac59075b964b0715'),
        'status'=>Role::VISITOR,
        'type'=>Role::VISITOR,
        'mobile_verified_at'=>Carbon::now(),
        'email'=>$faker->safeEmail,
    ];
});

$factory->define(StudentProfile::class, function (Faker $faker) {
    return [
        'serial_number' => '0',
        'avatar' => 'www.xx.test',
        'device' => 'ios',
        'year' => '2019',
        'gender' => rand(1, 2),
        'country' => '北京',
        'state' => '北京',
        'city' => '北京市',
        'area' => '朝阳区',
        'address_line' => '电信工程局9楼',
        'id_number' => '2019' . rand(100, 300) . rand(1000, 3000) . rand(3999, 9000),
        'birthday' => '2019-11-1',
        'political_name' => '党员',
        'nation_name' => '叶赫那拉氏',
        'source_place' => '北京',
        'parent_name' => '帕菲特',
        'parent_mobile' => '9999' . rand(1, 99) . rand(1, 99)
    ];
});

$factory->define(RegistrationInformatics::class, function (Faker $faker) {
    return [
        'school_id' => rand(1, 9),
        'recruitment_plan_id' => 1,
        'major_id' => rand(1, 3),
        'relocation_allowed' => rand(0, 1),
        'status' => 1,
        'user_id' => 1,
        'name' => $faker->name,
        'note' => null,
    ];
});

// Mock 招生计划的数据
$factory->define(RecruitmentPlan::class, function (Faker $faker) {
    return [
        'school_id'=>1,
        'major_id'=>1,
        'major_name'=>$faker->name,// 专业名
        'type'=>RecruitmentPlan::TYPE_SELF, // 招生类型: 自主招生/统招 等
        'title'=>$faker->title,// 本次招生计划的标题
        'start_at'=>date('Y-m-d'),  // 开始招生日期
        'end_at'=>date('Y-m-d'),    // 招生截止日期
        'description'=>$faker->paragraph,  // 招生简章详情
        'tease'=>$faker->paragraph,  // 简介
        'tags'=>$faker->paragraph,  // 标签
        'fee'=>1000,  // 专业学费
        'hot'=>$faker->boolean,  // 热门专业
        'seats'=>100,// 招生人数
        'grades_count'=>3,// 招几个班级
        'year'=>2019, // 招生年度
        'applied_count'=>0, // 已报名人数
        'enrolled_count'=>0, // 已招生人数
        'manager_id'=>1, // 负责人: 本次招生的收信人
        'target_students'=>$faker->paragraph, // 录取方式
        'student_requirements'=>$faker->paragraph, // 报名条件
        'how_to_enrol'=>$faker->paragraph, // 录取方式
    ];
});

// 办公/项目的 mock
$factory->define(\App\Models\OA\Project::class, function (Faker $faker) {
    return [
        'school_id'=>1,
        'user_id'=>11,
        'title'=>$faker->title,// 专业名
        'status'=>\App\Models\OA\Project::STATUS_IN_PROGRESS, // 招生类型: 自主招生/统招 等
        'content'=>$faker->paragraph,// 本次招生计划的标题
    ];
});
$factory->define(\App\Models\OA\ProjectMember::class, function (Faker $faker) {
    return [
        'oa_project_id'=>1,
        'user_id'=>10,
    ];
});