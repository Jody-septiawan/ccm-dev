<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketNotificationHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticket_notification_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_notification_id');
            $table->enum('status', ['success', 'failed']);
            $table->text('error_message');
            $table->timestamps();

            $table->foreign('ticket_notification_id')->references('id')->on('ticket_notifications')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ticket_notification_histories');
    }
}
