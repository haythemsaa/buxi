<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add visual coordinates to boxes table
        Schema::table('boxes', function (Blueprint $table) {
            $table->integer('plan_x')->nullable()->after('status')->comment('Position X sur le plan (pixels)');
            $table->integer('plan_y')->nullable()->after('plan_x')->comment('Position Y sur le plan (pixels)');
            $table->integer('plan_width')->default(100)->after('plan_y')->comment('Largeur sur le plan (pixels)');
            $table->integer('plan_height')->default(100)->after('plan_width')->comment('Hauteur sur le plan (pixels)');
            $table->string('plan_color')->nullable()->after('plan_height')->comment('Couleur personnalisée sur le plan');
        });

        // Add floor plan settings
        Schema::table('floors', function (Blueprint $table) {
            $table->integer('plan_width')->default(1200)->after('display_order')->comment('Largeur du plan (pixels)');
            $table->integer('plan_height')->default(800)->after('plan_width')->comment('Hauteur du plan (pixels)');
            $table->string('plan_background_image')->nullable()->after('plan_height')->comment('Image de fond du plan');
            $table->json('plan_settings')->nullable()->after('plan_background_image')->comment('Paramètres additionnels du plan');
        });

        // Add building floor plan settings
        Schema::table('buildings', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name')->comment('Description du bâtiment');
            $table->string('plan_color')->default('#3B82F6')->after('display_order')->comment('Couleur du bâtiment sur le plan global');
        });

        // Add site-level settings
        Schema::table('sites', function (Blueprint $table) {
            $table->boolean('plan_enabled')->default(true)->after('status')->comment('Plan visuel activé');
            $table->json('plan_settings')->nullable()->after('plan_enabled')->comment('Paramètres globaux du plan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boxes', function (Blueprint $table) {
            $table->dropColumn(['plan_x', 'plan_y', 'plan_width', 'plan_height', 'plan_color']);
        });

        Schema::table('floors', function (Blueprint $table) {
            $table->dropColumn(['plan_width', 'plan_height', 'plan_background_image', 'plan_settings']);
        });

        Schema::table('buildings', function (Blueprint $table) {
            $table->dropColumn(['description', 'plan_color']);
        });

        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn(['plan_enabled', 'plan_settings']);
        });
    }
};
