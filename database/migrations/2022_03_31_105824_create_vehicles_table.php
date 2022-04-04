<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('make_id')->unsigned();
            $table->foreign('make_id')->references('id')->on('makes');
            $table->integer('range_id')->unsigned();
            $table->foreign('range_id')->references('id')->on('ranges');
            $table->integer('model_id')->unsigned();
            $table->foreign('model_id')->references('id')->on('models');
            $table->integer('derivative_id')->unsigned();
            $table->foreign('derivative_id')->references('id')->on('derivatives');
            $table->string('reg', 8);
            $table->string('colour', 30);
            $table->decimal('price_including_vat',10,2);
            $table->integer('mileage')->unsigned();
            $table->string('vehicle_type', 10);
            $table->date('date_on_forecourt');
            $table->boolean('available');
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
        Schema::dropIfExists('vehicles');
    }
}
