<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Doctrine\DBAL\Types\Type;

class ChangeCategoryEnumCategoryToProductToTicketTable extends Migration
{
/**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Type::hasType('enum')) {
            Type::addType('enum', \Doctrine\DBAL\Types\StringType::class);
        }

        Schema::table('tickets', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->enum('category', ['product', 'delivery', 'service'])->change();
            } else {
                $table->string('category', 20)->change();
            }
        });
    }
    

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Type::hasType('enum')) {
            Type::addType('enum', \Doctrine\DBAL\Types\StringType::class);
        }
        
        Schema::table('tickets', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->enum('category', ['category', 'delivery', 'service'])->change();
            } else {
                $table->string('category', 20)->change();
            }
        });
    }
    
}
