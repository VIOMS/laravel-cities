<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Creates the users table
        Schema::create(config('cities.table'), function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\Vioms\Countries\Models\Country::class)
                ->constrained()
                ->cascadeOnDelete();
            $table->string('name', 200)->index();
            $table->string('ascii_name', 200)->nullable();
            $table->json('alternate_names')->nullable();
            $table->float('longitude', 16, 8)->nullable();
            $table->float('latitude', 16, 8)->nullable();
            $table->char('feature_class', 1)->nullable();
            $table->string('feature_code', 10)->nullable();
            $table->bigInteger('population')->nullable();
            $table->integer('elevation')->nullable();
            $table->string('timezone', 40)->nullable();
            $table->dateTimeTz('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop(config('cities.table'));
    }

};
