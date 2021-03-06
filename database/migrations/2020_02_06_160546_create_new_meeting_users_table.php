<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewMeetingUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('new_meeting_users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('meet_id')->comment('会议ID');
            $table->integer('user_id')->comment('用户ID');
            $table->tinyInteger('signin_status')->default(0)->comment('签到状态 0未签到');
            $table->dateTime('signin_time')->comment('签到时间')->nullable();
            $table->tinyInteger('signout_status')->default(0)->comment('签退状态 0未签退');
            $table->dateTime('signout_time')->comment('签退时间')->nullable();
            $table->timestamps();
        });
        DB::statement(" ALTER TABLE new_meeting_users comment '参会人员表' ");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('new_meeting_users');
    }
}
