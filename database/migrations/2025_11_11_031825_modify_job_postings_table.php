<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_postings', function (Blueprint $table) {
            $table->date('hiring_from')->nullable()->after('status');
            $table->date('hiring_to')->nullable()->after('hiring_from');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job_postings', function (Blueprint $table) {
            $table->dropColumn(['hiring_from', 'hiring_to']);
        });
    }
};
