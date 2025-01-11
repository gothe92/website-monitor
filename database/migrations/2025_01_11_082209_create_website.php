<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('websites', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::create('website_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained();
            $table->float('response_time')->nullable();
            $table->string('status');
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('website_logs');
        Schema::dropIfExists('websites');
    }
};
