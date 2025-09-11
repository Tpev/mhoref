<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {


	Schema::create('step_comments', function (Blueprint $table) {
		$table->id();
		$table->foreignId('workflow_step_id')->constrained('workflow_steps')->cascadeOnDelete();
		$table->foreignId('referral_id')->constrained('referrals')->cascadeOnDelete(); // add this line
		$table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
		$table->text('comment');
		$table->timestamps();
	});


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('step_comments');
    }
};
