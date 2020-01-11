<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdStatussToProjectTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oa_project_tasks', function (Blueprint $table) {
            //
            $table->smallInteger('status')->default(0)->comment('状态 1:待开始 2:正在进行 3:已结束')->change();
            $table->integer('project_id')->nullable()->change();
            $table->string('remark')->comment('备注')->nullable()->change();
        });
        DB::statement(" ALTER TABLE oa_projects comment '项目表' ");
        DB::statement(" ALTER TABLE oa_project_tasks comment '项目任务表' ");
        DB::statement(" ALTER TABLE oa_project_members comment '项目成员表' ");
        DB::statement(" ALTER TABLE oa_project_task_discussions comment '项目讨论表' ");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('oa_project_tasks', function (Blueprint $table) {
            //
            $table->smallInteger('status')->default(0)->comment('状态 1:待开始 2:正在进行 3:已结束')->change();
            $table->integer('project_id')->nullable()->change();
        });
    }
}
