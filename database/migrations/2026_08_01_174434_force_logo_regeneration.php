<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $seeder = new \Database\Seeders\DemoBakeriesSeeder();
        
        $tenants = \App\Models\Tenant::whereIn('slug', [
            'sweetmagnolia', 'cookiecottage', 'rusticcrumb', 'honeybutter', 
            'sugarstudioatx', 'wildflowerweddingcakes', 'goldenwhisk', 
            'velvetandvine', 'marigoldpastry', 'copperkettle'
        ])->get();
        
        $themes = \App\Models\Tenant::getAllThemes();

        $bakeries = [
            'sweetmagnolia' => 'Sweet Magnolia Bakery',
            'cookiecottage' => 'The Cookie Cottage',
            'rusticcrumb' => 'Rustic Crumb Bakery',
            'honeybutter' => 'Honey & Butter Cakes',
            'sugarstudioatx' => 'The Sugar Studio',
            'wildflowerweddingcakes' => 'Wildflower Wedding Cakes',
            'goldenwhisk' => 'Golden Whisk Bakehouse',
            'velvetandvine' => 'Velvet & Vine Cakery',
            'marigoldpastry' => 'Marigold Pastry Co.',
            'copperkettle' => 'Copper Kettle Bakery'
        ];

        foreach ($tenants as $tenant) {
            $name = $bakeries[$tenant->slug] ?? $tenant->name;
            $accent = $themes[$tenant->theme_id]['preview_accent'] ?? '#e67399';
            
            // Generate the SVG file dynamically on disk and retrieve relative path
            $path = $seeder->writeLogo($tenant->id, $name, $accent);
            
            // Save the exact logo path in the database
            $tenant->update(['logo_path' => $path]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
