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
        Schema::create('hubspot_submissions', function (Blueprint $table) {
            $table->integer('id', true);
            $table->timestamp('timestamp')->nullable()->useCurrent();
            $table->string('newsletter', 191)->nullable()->default('true');
            $table->string('email', 191)->nullable();
            $table->string('firstname', 191)->nullable();
            $table->string('lastname', 191)->nullable();
            $table->string('address', 191)->nullable();
            $table->string('city', 191)->nullable();
            $table->string('state', 191)->nullable();
            $table->string('zip', 191)->nullable();
            $table->string('mobilephone', 191)->nullable();
            $table->string('tv_provider', 191)->nullable();
            $table->string('form_last_activity', 191)->nullable();
            $table->string('how_did_you_hear_about_us', 191)->nullable();
            $table->boolean('was_submitted_successfully')->nullable()->index('hubspot_submissions_wasSubmittedSuccessfully');
            $table->boolean('is_test')->nullable()->index('hubspot_submissions_is_test');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hubspot_submissions');
    }
};
