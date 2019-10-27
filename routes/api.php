<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('school')->group(function () {
    // 加载学校的作息时间表
    Route::any('/load-time-slots','Api\School\TimeSlotsController@load_by_school')
        ->name('api.school.load.time.slots');

    // 为课程表添加功能提供返回有效时间段的接口
    Route::any('/load-study-time-slots','Api\School\TimeSlotsController@load_study_time_slots')
        ->name('api.school.load.study.time.slots');

    // 获取某个学校所有的专业
    Route::any('/load-majors','Api\School\MajorsController@load_by_school')
        ->name('api.school.load.majors');

    // 获取某个专业的所有班级
    Route::any('/load-major-grades','Api\School\MajorsController@load_major_grades')
        ->name('api.school.load.major.grades');

    // 获取某个专业的所有课程
    Route::any('/load-major-courses','Api\School\MajorsController@load_major_courses')
        ->name('api.school.load.major.courses');

    // 获取某个学校所有的课程
    Route::any('/load-courses','Api\School\CoursesController@load_courses')
        ->name('api.school.load.courses');

    // 搜索某个学校的老师
    Route::any('/search-teachers','Api\School\TeachersController@search_by_name')
        ->name('api.school.search.teachers');

    // 根据给定的课程, 返回所有教授该课程的老师的列表
    Route::any('/load-course-teachers','Api\School\TeachersController@load_course_teachers')
        ->name('api.school.load.course.teachers');

    // 保存课程的接口
    Route::any('/save-course','Api\School\CoursesController@save_course')
        ->name('api.school.save.course');

    // 删除课程的接口
    Route::any('/delete-course','Api\School\CoursesController@delete_course')
        ->name('api.school.delete.course');

    // 获取学校的所有建筑
    Route::any('/load-buildings','Api\School\LocationController@load_buildings')
        ->name('api.school.load.buildings');

    // 根据给定的建筑, 加载建筑内所有房间的接口
    Route::any('/load-building-rooms','Api\School\LocationController@load_building_rooms')
        ->name('api.school.load.building.rooms');

    // 根据条件为课程表创建表单返回未被占用的房间
    Route::any('/load-building-available-rooms','Api\School\LocationController@load_building_available_rooms')
        ->name('api.school.load.building.available.rooms');
});

Route::prefix('timetable')->group(function () {
    // 保存课程表项的接口
    Route::post('/save-timetable-item','Api\Timetable\TimetableItemsController@save')
        ->name('api.timetable.save.item');

    // 克隆项目
    Route::post('/clone-timetable-item','Api\Timetable\TimetableItemsController@clone_item')
        ->name('api.timetable.clone.item');

    // 删除课程表项的接口
    Route::post('/delete-timetable-item','Api\Timetable\TimetableItemsController@delete')
        ->name('api.timetable.delete.item');

    // 保存课程表项的接口
    Route::post('/update-timetable-item','Api\Timetable\TimetableItemsController@update')
        ->name('api.timetable.update.item');

    // 尝试加载课程表: 查询条件是只要有班级, 年和学期即可
    Route::post('/load','Api\Timetable\TimetableItemsController@load')
        ->name('api.timetable.load.items');

    // 尝试加载课程表项: 查询条件是id
    Route::post('/load-item','Api\Timetable\TimetableItemsController@load_item')
        ->name('api.timetable.load.item');
});