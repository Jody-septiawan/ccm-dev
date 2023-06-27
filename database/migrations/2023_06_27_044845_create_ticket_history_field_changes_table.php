<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketHistoryFieldChangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticket_history_field_changes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_history_id');
            $table->string('field', 50);
            $table->timestamps();

            $table->foreign('ticket_history_id')->references('id')->on('ticket_histories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ticket_history_field_changes');
    }
}
