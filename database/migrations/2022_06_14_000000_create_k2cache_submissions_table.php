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
        Schema::create('k2cache_submissions', function (Blueprint $table) {
            $table->integer('id', true);
            $table->timestamp('timestamp')->nullable()->useCurrent();
            $table->string('campaign')->nullable();
            $table->string('k2userid')->nullable();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('emailaddress')->nullable()->index('k2cache_submissions_emailaddress');
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zipcode')->nullable();
            $table->string('provider')->nullable();
            $table->string('optin', 1)->default('0');
            $table->string('source')->nullable();
            $table->string('emailmessage')->nullable();
            $table->string('crisis')->nullable();
            $table->boolean('was_submitted_successfully')->nullable()->index('k2cache_submissions_wasSubmittedSuccessfully');
            $table->boolean('is_test')->nullable()->index('k2cache_submissions_is_test');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('k2cache_submissions');
    }
};
