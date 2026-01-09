<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tenant;

class MenuFromImagesSeeder extends Seeder
{
    public function run()
    {
        $tenantId = 1; // Assuming default tenant

        // Ensure tenant exists
        if (!Tenant::find($tenantId)) {
            Tenant::create(['id' => $tenantId, 'name' => 'Restaurant La Sanaga', 'domain' => 'lasanaga.local']);
        }

        $menuData = [
            // IMAGE 1: Bières et Jus
            [
                'category' => 'Bières',
                'destination' => 'bar',
                'items' => [
                    ['name' => 'Mutzig', 'price' => 10],
                    ['name' => 'Castel', 'price' => 10],
                    ['name' => 'Isenbeck', 'price' => 10],
                    ['name' => '33', 'price' => 10],
                    ['name' => 'Booster', 'price' => 10],
                    ['name' => 'Grande - Guinness', 'price' => 15],
                    ['name' => 'Petite - Guinness', 'price' => 8],
                    ['name' => 'Desperados', 'price' => 8],
                    ['name' => 'Heineken', 'price' => 8],
                    ['name' => 'Leffe', 'price' => 8],
                    ['name' => '1664', 'price' => 8],
                ]
            ],
            [
                'category' => 'Jus en Canette',
                'destination' => 'bar',
                'items' => [
                    ['name' => 'Oasis', 'price' => 3],
                    ['name' => 'Coca', 'price' => 3],
                    ['name' => 'Schweppes', 'price' => 3],
                    ['name' => 'Red Bull', 'price' => 3],
                    ['name' => 'Orangina', 'price' => 3],
                ]
            ],
            [
                'category' => 'Jus Gazeux',
                'destination' => 'bar',
                'items' => [
                    ['name' => 'Top Manas', 'price' => 10],
                    ['name' => 'Djino', 'price' => 10],
                    ['name' => 'Pamplemousse', 'price' => 10],
                    ['name' => 'Grenadine', 'price' => 10],
                    ['name' => 'UCB', 'price' => 10],
                    ['name' => 'L\'eau 2', 'price' => 10],
                    ['name' => 'Badoit', 'price' => 3],
                    ['name' => 'Perrier', 'price' => 3],
                    ['name' => 'San Pellegrino', 'price' => 10],
                ]
            ],
            [
                'category' => 'Jus Naturel',
                'destination' => 'bar',
                'items' => [
                    ['name' => 'Goyave', 'price' => 6],
                    ['name' => 'Ananas', 'price' => 6],
                    ['name' => 'Mangue', 'price' => 6],
                    ['name' => 'Bissap', 'price' => 6],
                    ['name' => 'Gingembre', 'price' => 6],
                    ['name' => 'Citron', 'price' => 6],
                    ['name' => 'Tomate', 'price' => 6],
                ]
            ],
            [
                'category' => 'Cocktail',
                'destination' => 'bar',
                'items' => [
                    ['name' => 'Cocktail sans alcool', 'price' => 10],
                    ['name' => 'Cocktail avec alcool', 'price' => 15],
                ]
            ],

            // IMAGE 2: Whisky et Champagne
            [
                'category' => 'Whisky',
                'destination' => 'bar',
                'items' => [
                    ['name' => 'Chivas 18 ans', 'price' => 100],
                    ['name' => 'Chivas 12 ans', 'price' => 80],
                    ['name' => 'Cardhu', 'price' => 90],
                    ['name' => 'Double Black', 'price' => 90],
                    ['name' => 'Jack Daniels', 'price' => 90],
                    ['name' => 'Black', 'price' => 80],
                    ['name' => 'JB', 'price' => 70],
                    ['name' => 'Baileys', 'price' => 70],
                    ['name' => 'Cognac', 'price' => 20],
                    ['name' => 'Vodka', 'price' => 90],
                ]
            ],
            [
                'category' => 'Champagne',
                'destination' => 'bar',
                'items' => [
                    ['name' => 'Dom Pérignon', 'price' => 350],
                    ['name' => 'Ruinart Blanc de Blancs', 'price' => 180],
                    ['name' => 'Ruinart brut', 'price' => 130],
                    ['name' => 'Veuve Clicquot', 'price' => 100],
                    ['name' => 'Moët & Chandon', 'price' => 100],
                    ['name' => 'Champagne maison', 'price' => 70],
                    ['name' => 'Demi-champagne', 'price' => 35],
                ]
            ],

            // IMAGE 3: Carte des Vins
            [
                'category' => 'Vins Rouges',
                'destination' => 'bar',
                'items' => [
                    ['name' => 'Côtes du Rhône (Verre)', 'price' => 5],
                    ['name' => 'Saint Émilion (Bouteille)', 'price' => 36],
                    ['name' => 'Saint Émilion (Verre)', 'price' => 8],
                    ['name' => 'Côte de Bourg (Bouteille)', 'price' => 36],
                    ['name' => 'Côte de Bourg (Verre)', 'price' => 8],
                    ['name' => 'Mouton Cadet (Bouteille)', 'price' => 36],
                    ['name' => 'Mouton Cadet (Verre)', 'price' => 8],
                    ['name' => 'Bordeaux Supérieur (Bouteille)', 'price' => 36],
                    ['name' => 'Bordeaux Supérieur (Verre)', 'price' => 8],
                    ['name' => 'Bordeaux simple (Bouteille)', 'price' => 32],
                    ['name' => 'Bordeaux simple (Verre)', 'price' => 7],
                    ['name' => 'Haut-Médoc (Bouteille)', 'price' => 36],
                    ['name' => 'Haut-Médoc (Verre)', 'price' => 8],
                    ['name' => 'Le Médoc (Bouteille)', 'price' => 36],
                    ['name' => 'Le Médoc (Verre)', 'price' => 8],
                    ['name' => 'Esprit (Bouteille)', 'price' => 36],
                    ['name' => 'Esprit (Verre)', 'price' => 8],
                ]
            ],
            [
                'category' => 'Vins Blancs',
                'destination' => 'bar',
                'items' => [
                    ['name' => 'Pouilly-Fumé (Bouteille)', 'price' => 36],
                    ['name' => 'Pouilly-Fumé (Verre)', 'price' => 8],
                    ['name' => 'Chardonnay', 'price' => 30],
                    ['name' => 'Chablis (Bouteille)', 'price' => 36],
                    ['name' => 'Chablis (Verre)', 'price' => 8],
                    ['name' => 'Bordeaux blanc (Bouteille)', 'price' => 36],
                    ['name' => 'Bordeaux blanc (Verre)', 'price' => 8],
                ]
            ],
            [
                'category' => 'Vins Moelleux',
                'destination' => 'bar',
                'items' => [
                    ['name' => 'Sauternes', 'price' => 30],
                    ['name' => 'Monbazillac', 'price' => 30],
                    ['name' => 'Château Haut Rayne', 'price' => 30],
                ]
            ],
            [
                'category' => 'Vins Rosés',
                'destination' => 'bar',
                'items' => [
                    ['name' => 'Rosé Minuty', 'price' => 100],
                    ['name' => 'Côte de Provence (Bouteille)', 'price' => 26],
                    ['name' => 'Côte de Provence (Verre)', 'price' => 5],
                    ['name' => 'Château Haut-Rian', 'price' => 30],
                    ['name' => 'Vin mousseux', 'price' => 30],
                ]
            ],

            // IMAGE 4: Saveurs de la Mer / Terre
            [
                'category' => 'Saveurs de la Mer',
                'destination' => 'kitchen',
                'items' => [
                    ['name' => 'Pavé de saumon', 'price' => 18],
                    ['name' => 'Gambas sautées à l\'ail', 'price' => 30],
                    ['name' => 'Roulade de sole', 'price' => 30],
                ]
            ],
            [
                'category' => 'Saveurs de la Terre',
                'destination' => 'kitchen',
                'items' => [
                    ['name' => 'Carré d\'Agneau Rôti', 'price' => 23],
                    ['name' => 'Cuisse de Canard Confite', 'price' => 23],
                    ['name' => 'Côte de porc grillée', 'price' => 18],
                    ['name' => 'Entrecôte', 'price' => 23],
                ]
            ],

            // IMAGE 5: Nos Plats
            [
                'category' => 'Plats Taro',
                'destination' => 'kitchen',
                'items' => [
                    ['name' => 'Ndolé viande', 'price' => 22],
                    ['name' => 'Ndolé viande (Grand)', 'price' => 25],
                    ['name' => 'Ndolé gambas', 'price' => 26],
                    ['name' => 'Ndolé gambas (Grand)', 'price' => 29],
                    ['name' => 'Sauce mafé poisson', 'price' => 25],
                    ['name' => 'Sauce mafé poisson (Grand)', 'price' => 28],
                    ['name' => 'Mafé viande', 'price' => 25],
                    ['name' => 'Mafé viande (Grand)', 'price' => 28],
                    ['name' => 'Ndomba poisson chat', 'price' => 30],
                    ['name' => 'Ndomba poisson chat (Grand)', 'price' => 33],
                    ['name' => 'Gombo viande', 'price' => 26],
                    ['name' => 'Gombo viande (Grand)', 'price' => 29],
                    ['name' => 'Gombo royal', 'price' => 30],
                    ['name' => 'Gombo royal (Grand)', 'price' => 33],
                    ['name' => 'Gambas', 'price' => 30],
                    ['name' => 'Gambas (Grand)', 'price' => 33],
                    ['name' => 'Épinard royal', 'price' => 30],
                    ['name' => 'Épinard royal (Grand)', 'price' => 33],
                    ['name' => 'Ndolé poisson', 'price' => 22],
                    ['name' => 'Ndolé poisson (Grand)', 'price' => 25],
                    ['name' => 'Ndolé royal', 'price' => 26],
                    ['name' => 'Ndolé royal (Grand)', 'price' => 29],
                    ['name' => 'Ndolé poulet', 'price' => 25],
                    ['name' => 'Ndolé poulet (Grand)', 'price' => 28],
                    ['name' => 'Mafé poulet', 'price' => 25],
                    ['name' => 'Mafé poulet (Grand)', 'price' => 28],
                    ['name' => 'Poulet dg', 'price' => 22],
                    ['name' => 'Tripes de Bœuf', 'price' => 30],
                    ['name' => 'Tripes de Bœuf (Grand)', 'price' => 33],
                    ['name' => 'Gombo gambas', 'price' => 30],
                    ['name' => 'Gombo gambas (Grand)', 'price' => 33],
                    ['name' => 'Gombo poisson', 'price' => 26],
                    ['name' => 'Gombo poisson (Grand)', 'price' => 29],
                    ['name' => 'Épinard', 'price' => 25],
                ]
            ],
            [
                'category' => 'Accompagnements',
                'destination' => 'kitchen',
                'items' => [
                    ['name' => 'Banane Plantain', 'price' => 5],
                    ['name' => 'Bâton de Manioc', 'price' => 5],
                    ['name' => 'Attiéké', 'price' => 5],
                    ['name' => 'Riz Blanc', 'price' => 0],
                ]
            ],

            // NEW IMAGES DATA
            
            // IMAGE: Saveurs Italiennes
            [
                'category' => 'Saveurs Italiennes',
                'destination' => 'kitchen',
                'items' => [
                    ['name' => 'Tagliatelle Carbonara', 'price' => 14],
                    ['name' => 'Spaghetti aux crevettes', 'price' => 13],
                ]
            ],

            // IMAGE: La Carte de Grillades - Viande
            [
                'category' => 'Grillades Viande',
                'destination' => 'kitchen',
                'items' => [
                    ['name' => 'Porc Braisé', 'price' => 21],
                    ['name' => 'Poulet Braisé', 'price' => 21],
                    ['name' => 'Ailes Poulet Braisé', 'price' => 15],
                    ['name' => 'Soya De Boeuf', 'price' => 20],
                    ['name' => 'Brochettes Poulet', 'price' => 20],
                    ['name' => 'Brochettes de Bœuf', 'price' => 20],
                ]
            ],

            // IMAGE: La Carte de Grillades - Poisson
            [
                'category' => 'Grillades Poisson',
                'destination' => 'kitchen',
                'items' => [
                    // Bar Braisé 29/33/43
                    ['name' => 'Bar Braisé (Petit)', 'price' => 29],
                    ['name' => 'Bar Braisé (Moyen)', 'price' => 33],
                    ['name' => 'Bar Braisé (Grand)', 'price' => 43],
                    
                    // Poisson Chat Braisé 29/33/43
                    ['name' => 'Poisson Chat Braisé (Petit)', 'price' => 29],
                    ['name' => 'Poisson Chat Braisé (Moyen)', 'price' => 33],
                    ['name' => 'Poisson Chat Braisé (Grand)', 'price' => 43],

                    // Maquereau Braisé 25/33
                    ['name' => 'Maquereau Braisé (Petit)', 'price' => 25],
                    ['name' => 'Maquereau Braisé (Grand)', 'price' => 33],

                    // Sole Braisé 43/63/83/100
                    ['name' => 'Sole Braisé (Petit)', 'price' => 43],
                    ['name' => 'Sole Braisé (Moyen)', 'price' => 63],
                    ['name' => 'Sole Braisé (Grand)', 'price' => 83],
                    ['name' => 'Sole Braisé (XL)', 'price' => 100],

                    // Capitaine Braisé 29/33/43
                    ['name' => 'Capitaine Braisé (Petit)', 'price' => 29],
                    ['name' => 'Capitaine Braisé (Moyen)', 'price' => 33],
                    ['name' => 'Capitaine Braisé (Grand)', 'price' => 43],

                    // Dorade Royal 29/33/43
                    ['name' => 'Dorade Royal (Petit)', 'price' => 29],
                    ['name' => 'Dorade Royal (Moyen)', 'price' => 33],
                    ['name' => 'Dorade Royal (Grand)', 'price' => 43],
                ]
            ],

            // IMAGE: Nos Entrées
            [
                'category' => 'Nos Entrées',
                'destination' => 'kitchen',
                'items' => [
                    ['name' => 'Salade césar au Saumon fumé', 'price' => 12],
                    ['name' => 'Mille feuilles D\'avocat crevettes', 'price' => 15],
                    ['name' => 'Salade d\'avocat', 'price' => 10],
                    ['name' => 'Assiette créole', 'price' => 20],
                ]
            ],

            // IMAGE: Desserts
            [
                'category' => 'Desserts',
                'destination' => 'kitchen',
                'items' => [
                    ['name' => 'Assiette de fruits', 'price' => 10],
                    ['name' => 'Boules de Glace', 'price' => 10],
                ]
            ],
        ];

        foreach ($menuData as $catData) {
            $category = Category::firstOrCreate(
                ['name' => $catData['category'], 'tenant_id' => $tenantId],
                ['destination' => $catData['destination']]
            );

            foreach ($catData['items'] as $item) {
                Product::updateOrCreate(
                    [
                        'name' => $item['name'],
                        'tenant_id' => $tenantId
                    ],
                    [
                        'price' => $item['price'],
                        'category_id' => $category->id,
                        'is_active' => true
                    ]
                );
            }
        }
    }
}
