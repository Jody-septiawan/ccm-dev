<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_pipeline_id');
            $table->unsignedBigInteger('user_id');
            $table->string('title', 100);
            $table->string('ticket_number', 150);
            $table->enum('priority', ['low', 'medium', 'high']);
            $table->enum('status', ['open', 'assigned', 'in progress', 'pending', 'rejected', 'resolved']);
            $table->enum('category', ['category', 'delivery', 'service']);
            $table->string('subcategory', 100);
            $table->text('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tickets');
    }
}
