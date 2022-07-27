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
        Schema::connection("sqlsrv-global-microservices-queues")->create('k2cache_submission_queue', function (Blueprint $table) {
            $table->integer('id', true);
            $table->timestamp('timestamp')->nullable()->useCurrent()->index('k2cache_submission_queue_timestamp');
            $table->text('data')->nullable();
            $table->string('status', 50)->nullable()->index('k2cache_submission_queue_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection("sqlsrv-global-microservices-queues")->dropIfExists('k2cache_submission_queue');
    }
};
