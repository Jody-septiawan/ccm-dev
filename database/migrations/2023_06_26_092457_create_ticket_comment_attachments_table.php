<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketCommentAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticket_comment_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_comment_id');
            $table->string('url', 255);
            $table->string('filename', 50);
            $table->decimal('size', 10, 2);
            $table->string('type', 50);
            $table->timestamps();

            $table->foreign('ticket_comment_id')->references('id')->on('ticket_comments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ticket_comment_attachments');
    }
}
