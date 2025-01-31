<?php
// ProductSeeder.php
namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            // Groenten en Fruit (subcategory_id: 1)
            [
                'subcategory_id' => 1, // Groenten
                'name' => 'Wortelen',
                'short_description' => 'Vers van het land, knapperige wortelen boordevol vitamines',
                'full_description' => 'Deze wortelen, ook wel winterpeen genoemd, zijn stevig en knapperig. De fel oranje kleur en zoete smaak maken ze perfect voor een gezonde snack of als aanvulling in diverse gerechten. Je kunt ze rauw eten, koken, roerbakken of zelfs pureren. Voor gebruik, spoel ze af en schil ze. Of je ze nu in stukjes snijdt voor een dipsaus of kookt tot ze zacht zijn, deze wortelen passen bij elke maaltijd. Ze bevatten veel vitamines die goed zijn voor je gezondheid.',
                'price' => 1.45,
                'image_path' => 'images/products/groenten_fruit/groenten/wortelen.jpg'
            ],
            [
                'subcategory_id' => 1, // Groenten
                'name' => 'Biologische pompoen',
                'short_description' => 'Heerlijke biologische pompoen, perfect voor soep, stoofpotjes en herfstgerechten.',
                'full_description' => 'Deze biologische pompoen is niet alleen heerlijk maar ook supergezond! Het oranje vruchtvlees zit vol vezels en betacaroteen, wat goed is voor je ogen en huid. De pompoen kan in allerlei gerechten worden gebruikt, van soep tot geroosterde schijfjes in de oven. Met zijn stevige schil is hij perfect om zowel te bakken als te koken. Verwerk het vruchtvlees in stoofpotjes of maak er een heerlijke herfstsoep van. Bewaar hele pompoenen op een koele plek, en geniet wekenlang van hun smaak.',
                'price' => 2.99,
                'image_path' => 'images/products/groenten_fruit/groenten/biologischePompoen.jpg'
            ],
            [
                'subcategory_id' => 1, // Groenten
                'name' => 'Knolselderij',
                'short_description' => 'Heerlijke, stevige knolselderij met een rijke smaak',
                'full_description' => 'Knolselderij is een veelzijdige groente met een unieke smaak en een stevige textuur. De knol wordt vaak gebruikt in soepen, stoofschotels of als basis voor purees. De bladeren zijn trouwens ook eetbaar en kunnen rauw aan salades worden toegevoegd. Eenmaal gekookt of gebakken, krijg je een zachte, licht nootachtige smaak die perfect past bij andere herfstgroenten. Deze knol is het geheime wapen voor een diepe, hartige smaak in jouw gerechten.',
                'price' => 1.49,
                'image_path' => 'images/products/groenten_fruit/groenten/knolselderij.jpg'
            ],
            [
                'subcategory_id' => 1, // Groenten
                'name' => 'Pastinaak',
                'short_description' => 'Zoete, aromatische pastinaak, perfect voor puree of ovengerechten',
                'full_description' => 'Pastinaak heeft een licht zoete, anijsachtige smaak die goed past in herfst- en wintergerechten. Deze crème-witte wortel kan worden geroosterd, gekookt of gestampt voor een romige puree. Het lekkerste is om ze te oogsten na de eerste vorst, want dat maakt de smaak nog zoeter. Door zijn stevige penwortelstructuur is pastinaak niet alleen lekker, maar ook voedzaam en een ideale aanvulling op een winterse maaltijd.',
                'price' => 1.99,
                'image_path' => 'images/products/groenten_fruit/groenten/pastinaak.jpg'
            ],
            [
                'subcategory_id' => 1, // Groenten
                'name' => 'Zoete aardappels',
                'short_description' => 'Verfrissende komkommer, perfect voor salades of snacks',
                'full_description' => 'Zoete aardappels, ook wel bataat genoemd, hebben een zoete, aardse smaak en een zachte, romige textuur wanneer ze gekookt of gebakken zijn. Deze veelzijdige groente is rijk aan vitamines, vooral vitamine A, en past perfect bij allerlei gerechten. Gebruik ze als frietjes, in een salade of puree, of bak ze tot ze goudbruin en knapperig zijn. Ze voegen een heerlijk zoete toets toe aan je maaltijd en zijn ook nog eens gezond!',
                'price' => 1.49,
                'image_path' => 'images/products/groenten_fruit/groenten/zoeteAardappels.jpg'
            ],
            // Groenten en Fruit (subcategory_id: 2)
            [
                'subcategory_id' => 2, // Fruit
                'name' => 'Granny Smith appels',
                'short_description' => 'Heerlijke frisse en knapperige appels, perfect voor een gezonde snack of in een salade.',
                'full_description' => 'Deze Granny Smith appels staan bekend om hun knapperige, frisse textuur en pittige smaak. Ze zijn niet alleen heerlijk als snack, maar ook perfect voor salades of om mee te bakken. Of je nu houdt van een gezonde hap tussendoor of een smaakvolle toevoeging aan je appeltaart zoekt, deze groene appels zijn altijd een verfrissende keuze.',
                'price' => 2.99,
                'image_path' => 'images/products/groenten_fruit/fruit/grannySmithAppels.jpg'
            ],
            [
                'subcategory_id' => 2, // Fruit
                'name' => 'Handsinaasappels',
                'short_description' => 'Zonnig en sappig, deze Spaanse sinaasappels brengen de Mediterrane zon op je bord.',
                'full_description' => 'Met hun sappige vruchtvlees en frisse, zoete smaak zijn deze Spaanse sinaasappels een must voor elke fruitliefhebber. Ze brengen de zonnige Mediterrane sfeer direct op je bord. Heerlijk als snack, in een fruitsalade of als basis voor een vers glas sinaasappelsap. Een bron van vitamine C, perfect om je dag gezond te beginnen!',
                'price' => 3.29,
                'image_path' => 'images/products/groenten_fruit/fruit/handSinaasappels.jpg'
            ],
            [
                'subcategory_id' => 2, // Fruit
                'name' => 'Chiquita bananen family pack',
                'short_description' => 'Zoete en voedzame bananen, ideaal voor smoothies of als tussendoortje voor de hele familie.',
                'full_description' => 'Dit family pack biedt voldoende bananen voor het hele gezin. Elke banaan is zorgvuldig geselecteerd op rijpheid en smaak, zodat je altijd kunt genieten van hun zachte textuur en zoete smaak. Bananen zijn rijk aan kalium, wat goed is voor je spieren en hart. Ze zijn niet alleen een gezonde snack, maar ook ideaal voor smoothies, desserts of als snelle energieboost!',
                'price' => 2.19,
                'image_path' => 'images/products/groenten_fruit/fruit/chiquitaBananen.jpeg'
            ],
            // Bakkerij en Brood (subcategory_id: 3)
            [
                'subcategory_id' => 3, // Brood
                'name' => 'Wit brood',
                'short_description' => 'Versgebakken wit brood met een knapperige korst',
                'full_description' => 'Dit wit brood is versgebakken met een knapperige korst en een zachte, luchtige binnenkant. Het is ideaal voor een stevig ontbijt of een lekkere lunch. Belegd met zoet of hartig, het blijft een klassieker op de eettafel. Dit brood wordt gebakken bij een temperatuur van 200-230 graden Celsius, zodat elke hap knapperig en smaakvol is.',
                'price' => 1.99,
                'image_path' => 'images/products/bakkerij_brood/brood/witBrood.jpg'
            ],
            [
                'subcategory_id' => 3, // Brood
                'name' => 'Bruin brood',
                'short_description' => 'Gezond bruin brood, rijk aan vezels',
                'full_description' => 'Ons bruine brood zit boordevol vezels en heeft een heerlijke, stevige korst. Het is perfect voor wie houdt van een gezond en voedzaam brood bij het ontbijt of de lunch. Dit brood wordt met zorg gebakken voor een volle, rijke smaak die je dag voedzaam en smakelijk maakt.',
                'price' => 2.99,
                'image_path' => 'images/products/bakkerij_brood/brood/bruinBrood.jpg'
            ],
            [
                'subcategory_id' => 3, // Brood
                'name' => 'Croissant',
                'short_description' => 'Luchtige croissants, warm en vers uit de oven',
                'full_description' => 'Deze luchtige croissants zijn versgebakken en smelten in je mond. Ideaal voor bij het ontbijt of als tussendoortje. Hun zachte, boterachtige smaak in combinatie met de knapperige buitenkant zorgt voor een authentieke Franse eetervaring. Lekker met jam of gewoon zo!',
                'price' => 1.49,
                'image_path' => 'images/products/bakkerij_brood/brood/croissants.jpg'
            ],
            // Bakkerij en Brood (subcategory_id: 4)
            [
                'subcategory_id' => 4, // Gebak
                'name' => 'Red Velvet Muffins',
                'short_description' => 'Luchtige muffins met de klassieke red velvet smaak, afgewerkt met een romige topping',
                'full_description' => 'Deze muffins brengen de luxe van red velvet naar jouw bord. Ze zijn heerlijk luchtig en afgewerkt met een romige topping van roomkaas. Met een subtiele cacaosmaak en hun dieprode kleur zijn ze perfect voor een speciale traktatie. Of je ze nu serveert bij de koffie of als dessert, deze muffins zijn altijd een hit!',
                'price' => 1.69,
                'image_path' => 'images/products/bakkerij_brood/gebak/redVelvetMuffin.jpg'
            ],
            [
                'subcategory_id' => 4, // Gebak
                'name' => 'Luxe Blueberry Muffins',
                'short_description' => 'Zachte muffins boordevol sappige blauwe bessen, een perfecte zoete traktatie',
                'full_description' => 'Boordevol sappige blauwe bessen zijn deze muffins een absolute verwennerij. Ze zijn zacht en luchtig, en de bessen zorgen voor een heerlijke frisse smaakexplosie bij elke hap. Perfect als tussendoortje of als zoete traktatie bij een kop koffie of thee.',
                'price' => 1.69,
                'image_path' => 'images/products/bakkerij_brood/gebak/luxeBlueberryMuffin.jpg'
            ],
            [
                'subcategory_id' => 4, // Gebak
                'name' => 'Espresso Brownies',
                'short_description' => 'Chocoladerijke brownies met een shot espresso voor een intense herfstboost',
                'full_description' => 'Voor de echte chocoholics en koffieliefhebbers zijn deze espresso brownies een droom. Ze combineren de rijke smaak van chocolade met de intensiteit van espresso. Elke hap is vol van smaak en smelt heerlijk weg in je mond. Deze brownies zijn de ultieme zoete oppepper!',
                'price' => 3.99,
                'image_path' => 'images/products/bakkerij_brood/gebak/espressoBrownies.jpg'
            ],
            // Zuivel en Eieren (subcategory_id: 5)
            [
                'subcategory_id' => 5, // Zuivel
                'name' => 'Campina volle melk',
                'short_description' => 'Romige, volle melk, perfect voor een gezond ontbijt of koffie',
                'full_description' => 'Campina volle melk is een romige, volle melk die perfect past bij een gezond ontbijt of in de koffie. Het zit boordevol calcium en essentiële voedingsstoffen die bijdragen aan sterke botten en een evenwichtige levensstijl. Geniet dagelijks van de rijke, romige smaak.',
                'price' => 1.99,
                'image_path' => 'images/products/zuivel_eieren/melk/campinaVolleMelk.jpg'
            ],
            [
                'subcategory_id' => 5, // Zuivel
                'name' => 'Campina halfvolle melk',
                'short_description' => 'Frisse, lichte melk, een goede balans tussen romig en mager',
                'full_description' => 'Campina halfvolle melk biedt een lichte en verfrissende smaak die de perfecte balans vormt tussen romigheid en minder vet. Rijk aan calcium en andere belangrijke voedingsstoffen, deze melk is ideaal voor iedereen die gezond wil leven zonder in te leveren op smaak.',
                'price' => 1.79,
                'image_path' => 'images/products/zuivel_eieren/melk/campinaHalfvolleMelk.jpg'
            ],
            [
                'subcategory_id' => 5, // Zuivel
                'name' => 'Campina volle yoghurt',
                'short_description' => 'Rijke, romige yoghurt, ideaal voor smoothies of ontbijt',
                'full_description' => 'Campina volle yoghurt is een romige en volle yoghurt, ideaal voor je ontbijt, tussendoor of in smoothies. Deze yoghurt is rijk aan calcium en helpt bij het ondersteunen van een gezonde spijsvertering en een evenwichtige levensstijl.',
                'price' => 2.49,
                'image_path' => 'images/products/zuivel_eieren/melk/campinaVolleYogurt.jpg'
            ],
            [
                'subcategory_id' => 5, // Zuivel
                'name' => 'Campina halfvolle yoghurt',
                'short_description' => 'Lichte, frisse yoghurt, perfect als tussendoortje of dessert',
                'full_description' => 'Campina halfvolle yoghurt is een lichte, frisse yoghurt die perfect past bij een tussendoortje of dessert. Het bevat veel calcium en voedingsstoffen die bijdragen aan een gezonde levensstijl, zonder dat het zwaar op de maag ligt.',
                'price' => 2.29,
                'image_path' => 'images/products/zuivel_eieren/melk/campinaHalfvolleYogurt.jpg'
            ],
            [
                'subcategory_id' => 5, // Zuivel
                'name' => 'Campina magere yoghurt',
                'short_description' => 'Verfrissende, magere yoghurt met een volle smaak zonder schuldgevoel',
                'full_description' => 'Campina magere yoghurt biedt een verfrissende smaak zonder de schuldgevoelens van vet. Rijk aan calcium en essentiële voedingsstoffen, deze yoghurt helpt je gezond te blijven terwijl je geniet van de volle smaak van yoghurt.',
                'price' => 1.99,
                'image_path' => 'images/products/zuivel_eieren/melk/campinaMagereYogurt.jpg'
            ],
            [
                'subcategory_id' => 5, // Zuivel
                'name' => 'Campina Griekse Stijl 5%',
                'short_description' => 'Romige Griekse yoghurt, perfect voor gezonde snacks',
                'full_description' => 'Campina Griekse yoghurt met 5% vet biedt de perfecte balans tussen romigheid en gezondheid. Deze yoghurt is rijk aan calcium en is ideaal als tussendoortje, voor in salades, of als basis voor smoothies.',
                'price' => 2.99,
                'image_path' => 'images/products/zuivel_eieren/melk/campinaGriekseYogurt5.jpg'
            ],
            [
                'subcategory_id' => 5, // Zuivel
                'name' => 'Campina Griekse Stijl 10%',
                'short_description' => 'Extra romige Griekse yoghurt, rijk van smaak',
                'full_description' => 'Deze extra romige Griekse yoghurt van Campina heeft een volle, rijke smaak. Met 10% vet is het de perfecte keuze voor liefhebbers van een meer indulgente yoghurt die toch voedzaam blijft, boordevol calcium en essentiële voedingsstoffen.',
                'price' => 3.49,
                'image_path' => 'images/products/zuivel_eieren/melk/campinaGriekseYogurt10.jpg'
            ],
            // Zuivel en Eieren (subcategory_id: 6)
            [
                'subcategory_id' => 6, // Kaas
                'name' => 'Beemster Jong',
                'short_description' => 'Zachte, romige Gouda kaas met 6 plakken.',
                'full_description' => 'Beemster Jong is een zachte, romige Gouda kaas die rijk is aan eiwitten en calcium. De milde smaak en romige textuur maken het een perfecte keuze voor op brood of als snack. Deze kaas draagt bij aan een gezonde levensstijl met zijn rijke voedingswaarde.',
                'price' => 1.99,
                'image_path' => 'images/products/zuivel_eieren/kaas/beemsterJongKaas.jpg'
            ],
            [
                'subcategory_id' => 6, // Kaas
                'name' => 'Beemster Extra Oud',
                'short_description' => 'Volle oude Gouda kaas met 5 plakken.',
                'full_description' => 'Beemster Extra Oud is een krachtige Gouda kaas met een uitgesproken smaak. Het biedt een romige textuur en is rijk aan eiwitten en calcium, wat het een voedzame keuze maakt voor elk moment van de dag.',
                'price' => 2.49,
                'image_path' => 'images/products/zuivel_eieren/kaas/beemsterExtraOudKaas.jpg'
            ],
            [
                'subcategory_id' => 6, // Kaas
                'name' => 'Old Amsterdam',
                'short_description' => 'Pittige oude kaas met 8 plakken.',
                'full_description' => 'Old Amsterdam is een pittige, oude kaas die intens van smaak is. Deze kaas heeft een romige textuur en is een bron van hoogwaardige eiwitten en calcium, wat bijdraagt aan een gezonde levensstijl. Ideaal als snack of in gerechten.',
                'price' => 3.99,
                'image_path' => 'images/products/zuivel_eieren/kaas/oldAmsterdamKaas.jpg'
            ],
            [
                'subcategory_id' => 6, // Kaas
                'name' => 'Salakis Feta',
                'short_description' => 'Ziltige Griekse feta kaas.',
                'full_description' => 'Salakis Feta is een ziltige, Griekse feta kaas met een romige textuur en pittige smaak. Deze kaas is een rijke bron van eiwitten en calcium, perfect voor salades, wraps of gewoon als tussendoortje.',
                'price' => 2.99,
                'image_path' => 'images/products/zuivel_eieren/kaas/salakisFetaKaas.jpg'
            ],
            [
                'subcategory_id' => 6, // Kaas
                'name' => 'Président Le Brie',
                'short_description' => 'Romige zachte brie',
                'full_description' => 'Président Le Brie is een zachte, romige kaas met een delicaat karakter. Deze brie smelt in de mond en is ideaal voor op brood, crackers of als onderdeel van een kaasplankje. Rijk aan eiwitten en calcium, een voedzame en smaakvolle keuze.',
                'price' => 1.49,
                'image_path' => 'images/products/zuivel_eieren/kaas/presidentLeBrieKaas.jpg'
            ],
            // Zuivel en Eieren (subcategory_id: 7)
            [
                'subcategory_id' => 7, // Eieren
                'name' => 'Scharreleieren',
                'short_description' => 'Vers geplukte scharreleieren van blije kippen',
                'full_description' => 'Scharreleieren komen van kippen die vrij kunnen rondlopen, wat bijdraagt aan hun welzijn en de kwaliteit van de eieren. Deze eieren zijn rijk aan eiwitten en voedingsstoffen die essentieel zijn voor een gebalanceerd dieet. Perfect voor koken, bakken of als gezond tussendoortje.',
                'price' => 1.99,
                'image_path' => 'images/products/zuivel_eieren/eieren/scharreleieren.jpg'
            ]
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
