<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('workflow_steps', function (Blueprint $table) {
            $table->json('group_can_write')->nullable()->after('metadata');
            $table->json('group_can_see')->nullable()->after('group_can_write');
            $table->json('group_get_notif')->nullable()->after('group_can_see');
        });
    }

    public function down()
    {
        Schema::table('workflow_steps', function (Blueprint $table) {
            $table->dropColumn(['group_can_write','group_can_see','group_get_notif']);
        });
    }
};
