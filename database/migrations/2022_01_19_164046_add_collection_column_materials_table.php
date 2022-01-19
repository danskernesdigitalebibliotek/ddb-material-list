<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace
class AddCollectionColumnMaterialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->string('guid', 100)->change();
            $table->string('list', 20)->change();

            $table->string('collection')->nullable();
            $table->string('material')->nullable()->change();

            $table->dropUnique(['guid', 'list', 'material']);
            $table->unique(['guid', 'list', 'material', 'collection']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->string('material')->nullable(false)->change();
            $table->unique(['guid', 'list', 'material']);
        });
         Schema::table('materials', function (Blueprint $table) {
            $table->dropUnique(['guid', 'list', 'material', 'collection']);
         });
        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn('collection');
        });
    }
}
