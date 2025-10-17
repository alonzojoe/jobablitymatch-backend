<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE users MODIFY lastname VARCHAR(50) NULL');
        DB::statement('ALTER TABLE users MODIFY firstname VARCHAR(50) NULL');
        DB::statement('ALTER TABLE users MODIFY middlename VARCHAR(50) NULL');
        DB::statement('ALTER TABLE users MODIFY gender VARCHAR(50) NULL');
        DB::statement('ALTER TABLE users MODIFY pwd_id_no VARCHAR(50) NULL');
        DB::statement('ALTER TABLE users MODIFY email VARCHAR(100) NOT NULL UNIQUE');
        DB::statement('ALTER TABLE users MODIFY phone BIGINT UNSIGNED NULL');

        Schema::table('users', function (Blueprint $table) {
            $table->string('pwdid_path')->nullable()->after('role_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE users MODIFY lastname VARCHAR(255) NULL');
        DB::statement('ALTER TABLE users MODIFY firstname VARCHAR(255) NULL');
        DB::statement('ALTER TABLE users MODIFY middlename VARCHAR(255) NULL');
        DB::statement('ALTER TABLE users MODIFY gender VARCHAR(255) NULL');
        DB::statement('ALTER TABLE users MODIFY pwd_id_no VARCHAR(255) NULL');
        DB::statement('ALTER TABLE users MODIFY email VARCHAR(255) NOT NULL UNIQUE');
        DB::statement('ALTER TABLE users MODIFY phone VARCHAR(255) NULL');
        DB::statement('ALTER TABLE users MODIFY phone VARCHAR(255) NULL');


        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('pwdid_path');
        });
    }
};
