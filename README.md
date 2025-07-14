# Commodium Copia

## Inhoudsopgave

<details>
  <summary><strong>Inhoudsopgave</strong></summary>

1. [Inleiding](#inleiding)  
2. [Projectbeschrijving](#projectbeschrijving)  
3. [Gebruikte tools](#gebruikte-tools)  
   - [Frontend](#frontend)  
   - [Backend](#backend-tools)  
4. [Projectstructuur](#projectstructuur)  
   - [Frontend](#frontend)  
     - [Componenten](#componenten-resourcesjscomponents)  
       - [Admin/Layout](#adminlayout)  
       - [Checkout](#checkout)  
       - [Editor/Layout](#editorlayout)  
     - [Layouts](#layouts-resourcesjslayouts)  
     - [Pages](#pages-resourcesjspages)  
       - [Admin](#admin-pages)  
       - [Checkout](#checkout-pages)  
       - [Editor](#editor-pages)  
     - [Composables](#composables-resourcesjscomposables)  
     - [Stores](#stores-resourcesjsstores)  
     - [Blade Templates](#blade-templates-resourcesviews)  
   - [Backend](#backend)  
     - [Controllers](#controllers-apphttpcontrollers)  
       - [Admin](#admin-controllers)  
       - [Editor](#editor-controllers)  
       - [Checkout](#checkout-controllers)  
       - [Customer](#customer-controllers)  
       - [Overig](#overig-controllers)  
     - [Models](#models-appmodels)  
     - [Services](#services-appservices)  
     - [Routes](#routes)  
   - [Database](#database)  
     - [Migrations](#migrations-databasemigrations)  
5. [Installatie en Setup](#installatie-en-setup)  
   - [Vereisten](#vereisten)  
   - [Installatiestappen](#installatiestappen)  
   - [.env Configuratie](#environment-configuratie)  
   - [Database Setup met Testdata](#database-setup-met-testdata)  
   - [Frontend Dependencies (Vue.js)](#frontend-dependencies-vuejs)  
   - [Start Development Servers](#start-development-servers)  
6. [Gebruikersaccounts](#gebruikersaccounts)  
7. [Ingebouwde data](#ingebouwde-data)  
8. [Gerealiseerde backend](#gerealiseerde-backend)  
9. [Troubleshooting](#troubleshooting)  
10. [Feedback](#feedback)

</details>

---

## Inleiding {#inleiding}

Dit is mijn complete huiswerkopdracht voor de MBO4 Softwareontwikkeling aan de LOI. Het project combineert Vue.js en TailwindCSS voor een gebruiksvriendelijke en responsieve frontend met Laravel als backend. Samen vormen ze een volledig en dynamisch systeem voor mijn e-commerce website, Commodium Copia.

**Live Demo (frontend-only)**: [Commodium Copia](https://commodium-copia-fe-67wm.vercel.app)

## Projectbeschrijving {#projectbeschrijving}

De supermarkt Commodium Copia, vanaf hier genoemd 'klant', wil een nieuwe website laten ontwikkelen om haar concurrentiepositie in de huidige markt te behouden. Zij richt hiervoor een aparte bv op voor de online verkoop, die integreert met de huidige distributie- en ICT-systemen.

Het belangrijkste onderdeel van deze nieuwe website is het thuis kunnen laten bezorgen van producten bij klanten. Andere supermarktketens lopen op dit moment voor in deze ontwikkeling: Halbert Eijn, Jombu en Vamor bieden allemaal al mogelijkheden om hun producten thuis te laten bezorgen.

## Gebruikte tools {#gebruikte-tools}

### Frontend {#frontend}

1. Vue.js 3.4.0
2. Tailwind.css 3.4.1
3. Heroicons/Headless UI
4. Postcss 8.4.38
5. Autoprefixer 0.4.19
6. Fontawesome brands icons (for Social media icons)
7. Pinia 2.2.4 for state management
8. TinyMCE 7.6.1

### Backend-tools {#backend-tools}

1. Laravel 11.31 (PHP v8.2)
2. MariaDB
3. Laravel Breeze
4. Carbon
5. Inertia
6. Spatie

---

## Projectstructuur {#projectstructuur}

Hier volgt een overzicht van de belangrijkste bestanden en mappen binnen het project, inclusief een korte beschrijving van hun functies:

## Frontend {#frontend-1}

### Components {#componenten-resourcesjscomponents}

#### resources/js/Components
- [**NavBar.vue**](resources/js/Components/NavBar.vue)  
  De navigatiebalk voor eenvoudige toegang tot de belangrijkste pagina's van de website.

- [**PromoSection.vue**](resources/js/Components/PromoSection.vue)  
  Bevat een seizoensgebonden promotie met een visuele achtergrond en een CTA-knop. Momenteel ligt de focus op herfstproducten, en er is een actieknop om direct naar de winkelpagina te gaan en een korting te claimen.

- [**ProductGrid.vue**](resources/js/Components/ProductGrid.vue)  
  Een gridweergave van de producten, met afbeeldingen, namen en prijzen.

- [**NewsSection.vue**](resources/js/Components/NewsSection.vue)  
  Deze sectie toont nieuws en updates van Commodium Copia.

- [**Footer.vue**](resources/js/Components/Footer.vue)  
  De voettekst die op elke pagina verschijnt, samengesteld uit de volgende componenten:
  - [**NewsLetterSignup.vue**](resources/js/Components/NewsLetterSignup.vue): Een compact inschrijfformulier voor de nieuwsbrief van Commodium Copia.
  - [**CustomerService.vue**](resources/js/Components/CustomerService.vue): Bevat de contactgegevens van de klantenservice, inclusief telefoonnummer, openingstijden en e-mailadres. Deze sectie biedt klanten een gemakkelijke manier om contact op te nemen met de klantenservice en om te weten wanneer ze bereikbaar zijn.

- [**ApplicationLogo**](resources/js/Components/ApplicationLogo.vue)  
  Toont de merknaam van Commodium Copia in tekstvorm, met een compacte variant ("ComCopia") op mobiel en de volledige naam op grotere schermen. Past zich responsief aan met TailwindCSS classes.

- [**ShoppingCart.vue**](resources/js/Components/ShoppingCart.vue)  
  Een responsieve, slide-in winkelwagen die opent vanaf de rechterzijde van het scherm. Maakt gebruik van ```Headless UI``` transitions en ```Pinia``` store (```useCartStore```) voor dynamisch laden, sorteren en beheren van winkelwagenitems. Ondersteunt mobiele optimalisatie, live voorraadcontrole, sorteeropties, en interactieve besturingsknoppen zoals quantity up/down, verwijderen, en een duidelijke checkout-flow. Volledig mobielvriendelijk en visueel afgestemd op TailwindCSS.

#### Admin/Layout {#adminlayout}

- [**Navbar.vue**](resources/js/Components/Admin/Layout/Navbar.vue)   
  Navigatiebalk voor het admin dashboard, geoptimaliseerd voor mobiel en desktop. Bevat een hamburgermenu, een logo/titel, een knop om het publieke gedeelte te bekijken en een uitlogknop. Gebruikt ```Headless UI``` voor de Disclosure wrapper en ```Heroicons``` voor visuele iconen. De logout() methode post naar de backend via ```Inertia.js```.

- [**Sidebar.vue**](resources/js/Components/Admin/Layout/Sidebar.vue)  
  Zijbalknavigatie voor het admin dashboard. Ondersteunt zowel statische weergave op desktop als een slide-in versie op mobiel via ```Headless UI```. Bevat links naar dashboard, gebruikersbeheer, catalogusstructuur en instellingen. Routes worden opgezet via Inertia.js, en actieve links worden visueel gemarkeerd.

#### Checkout {#checkout}

- [**DeliverySlotSelector.vue**](resources/js/Components/Checkout/DeliverySlotSelector.vue)   
  Interactieve component om bezorgdagen en tijdslots te selecteren tijdens het afrekenen. Toont beschikbaarheid per dag, laat gebruikers een bezorgmoment kiezen en verwerkt fouten en bevestigingen. Integreert real-time feedback, automatische verversing en fallback-states bij netwerkproblemen. Stuur functionaliteit via props en emits door naar de checkoutpagina.

- [**OrderSummary.vue**](resources/js/Components/Checkout/OrderSummary.vue)   
  Toont een samenvatting van de bestelling tijdens het afrekenproces. Inclusief productinformatie, subtotaal, bezorgkosten, kortingen en totaalbedrag. Integreert dynamisch de geselecteerde bezorgslot en het afleveradres. Bevat foutmeldingen bij voorraadproblemen, optionele opmerkingenvelden en actieknoppen om door te gaan of terug te keren naar de winkelwagen.

#### Editor/Layout {#editorlayout}

- [**Navbar.vue**](resources/js/Components/Editor/Layout/Navbar.vue)    
  Navigatiebalk voor het editor dashboard, geoptimaliseerd voor zowel mobiel als desktop. Bevat een hamburgermenu, dashboardtitel, knop om de publieke site te openen en een uitlogknop. Gebruikt ```Headless UI``` en ```Heroicons``` voor interactieve en visuele elementen. Logout verloopt via Inertia.js.

- [**Sidebar.vue**](resources/js/Components/Editor/Layout/Sidebar.vue)    
  Zijbalknavigatie voor het editor dashboard. Ondersteunt zowel een vaste weergave op desktop als een slide-in paneel op mobiel via ```Headless UI```. Bevat links naar homepagebeheer (aanbiedingen, nieuws), catalogusbeheer (producten, banners) en instellingen. Actieve routes worden dynamisch gemarkeerd via Inertia.js.

### Composables {#composables-resourcesjscomposables}

- [**useSearch.js**](resources/js/Composables/useSearch.js)   
  Een herbruikbare Vue composable voor het beheren van zoekfunctionaliteit, inclusief debounce-zoekopdrachten, suggesties ophalen via ```axios```, localStorage voor recente zoekopdrachten, en caching van populaire resultaten. Werkt met Inertia.js voor navigatie en biedt fallback bij netwerkfouten.

### Layouts {#layouts-resourcesjslayouts}

- [***AuthenticatedLayout.vue***](resources/js/Layouts/AuthenticatedLayout.vue)   
    Layout voor ingelogde gebruikers. Bevat een responsieve navigatiebalk met dropdownmenu's en profielacties. Ondersteunt mobiel en desktop, en toont gebruikersinformatie via Inertia props.

- [***GuestLayout.vue***](resources/js/Layouts/GuestLayout.vue)   
    Layout voor gastpagina's zoals login of registratie. Bevat centraal logo en gestileerde contentcontainer. Werkt met een slot om formulieren dynamisch te laden.

#### resources/js/Layouts/Admin

- [***AdminLayout.vue***](resources/js/Layouts/Admin/AdminLayout.vue)   
  Layoutcomponent voor alle adminpagina's, inclusief ```Navbar.vue``` en ```Sidebar.vue```. Detecteert automatisch mobiel of desktop en past weergave aan. Bevat logica voor het openen/sluiten van de ```Sidebar.vue``` en gebruikt Vue's Composition API.

#### resources/js/Layouts/Checkout

- [***CheckoutLayout.vue***](resources/js/CheckoutLayout.vue)   
  Layout voor de checkout-pagina met stap-indicator, sessiebeheer, en fallback bij sessieverloop. Ondersteunt een driedelig bestelproces: bezorgmoment kiezen, bestelling controleren en bevestigen. Werkt met ```Pinia```, ```axios```, en ```Inertia router```.

#### resources/js/Layouts/Editor

- [***EditorLayout.vue***](resources/js/Layouts/Editor/EditorLayout.vue)    
  Layoutcomponent voor de Editor-omgeving. Bevat een dynamisch responsieve ```Sidebar.vue``` (mobiel vs desktop) en integreert een aangepaste ```Navbar.vue```. Ondersteunt het sloten van sidebar via props en emits.

### Pages {#pages-resourcesjspages}

- [**HomePage.vue**](resources/js/Pages/Homepage.vue)  
  De layout van de startpagina van Commodium Copia, samengesteld uit:
  - **NavBar.vue**
  - **PromoSection.vue**
  - **ProductGrid.vue**
  - **NewsSection.vue**
  - **Footer.vue**

- [**CategoryPage.vue**](resources/js/Pages/CategoryPage.vue), [**SubcategoryPage.vue**](resources/js/Pages/SubcategoryPage.vue), [**ProductPage.vue**](resources/js/Pages/ProductPage.vue)  
  Deze drie views zorgen voor de navigatie tussen productcategorieën, subcategorieën en productdetails, met koppelingen via `router-link` voor een naadloze gebruikerservaring.

- [**Dashboard.vue**](resources/js/Pages/Dashboard.vue)   
    Klant-dashboard met overzicht van actieve bestellingen en bestelgeschiedenis. Gebruikt een tabsysteem en toont details per bestelling zoals status, aflevermoment, items en totaalbedrag.

- [**SearchPage.vue**](resources/js/Pages/SearchPage.vue)   
  Resultatenpagina voor zoekopdrachten. Toont dynamisch de producten op basis van een zoekterm, inclusief toast-notificatie bij toevoegen aan de winkelwagen en diverse UI-states (geen input, geen resultaten, wel resultaten).

#### Admin Pages {#admin-pages}

##### resources/js/Pages/Admin/Categories
- [**Create.vue**](resources/js/Pages/Admin/Categories/Create.vue)    
  Pagina voor het aanmaken van categorieën, inclusief formulier voor naam, beschrijving en afbeelding. Bevat een ingebouwde editor om de afbeelding te verslepen en in te zoomen met automatische uitlijning binnen een vierkante preview.

- [**Edit.vue**](resources/js/Pages/Admin/Categories/Edit.vue)    
  Pagina voor het bewerken van categorieën met een geavanceerde afbeeldingseditor. Ondersteunt in- en uitzoomen, slepen en live preview. Slaat zoom- en positiedata op samen met naam en beschrijving. Waarschuwt bij het verlaten van de pagina met niet-opgeslagen wijzigingen.

- [**Index.vue**](resources/js/Pages/Admin/Categories/Index.vue)    
  Overzichtspagina van alle categorieën met live zoekfunctionaliteit, mobiele en desktopweergave, en een bevestigingsmodal voor verwijderen. Ondersteunt bewerken en aanmaken van nieuwe categorieën via knoppen of lege-status prompts.

##### resources/js/Pages/Admin/Dashboard

- [**Index.vue**](resources/js/Pages/Admin/Dashboard/Index.vue)   
  Startpagina van het admin-dashboard met een welkomsttekst. Voorbereid op toekomstige uitbreidingen via een grid-layout voor dashboardcomponenten.

##### resources/js/Pages/Admin/Settings

- [**Index.vue**](resources/js/Pages/Admin/Settings/Index.vue)    
  Beheerdersinstellingenpagina voor het wijzigen van het wachtwoord. Bevat formulier met validatie, zichtbaarheidstoggles voor wachtwoordvelden, succesnotificaties en een extra beveiligingswaarschuwing.

##### resources/js/Pages/Admin/Subcategories

- [**Create.vue**](resources/js/Pages/Admin/Subcategories/Create.vue)   
  Formulierpagina om een nieuwe subcategorie aan te maken. Bevat validatie, waarschuwing bij verlaten zonder opslaan, en dropdown om een bijbehorende categorie te selecteren. Gebruiksvriendelijk en visueel afgestemd op de admin-layout.

- [**Edit.vue**](resources/js/Pages/Admin/Subcategories/Edit.vue)   
  Bewerkpagina voor bestaande subcategorieën. Het formulier toont de huidige naam en bijbehorende categorie, en waarschuwt bij het verlaten met niet-opgeslagen wijzigingen. Verwerkt updates via een PUT-verzoek.

- [**Index.vue**](resources/js/Pages/Admin/Subcategories/Index.vue)   
  Overzichtspagina van alle subcategorieën. Toont naam, bijbehorende categorie en aantal producten. Bevat knoppen om subcategorieën te bewerken of verwijderen, en een knop om een nieuwe subcategorie toe te voegen.

##### resources/js/Pages/Admin/Users

- [**Create.vue**](resources/js/Pages/Admin/Users/Create.vue)   
  Modale component voor het aanmaken van een nieuwe gebruiker in het beheerpaneel. Bevat een formulier met velden voor naam, e-mailadres, wachtwoord, bevestiging en rolkeuze. Toont validatiefouten en sluit automatisch bij succes.

- [**Edit.vue**](resources/js/Pages/Admin/Users/Edit.vue)   
  Modale component voor het bewerken van bestaande gebruikersaccounts. Laadt gebruikersgegevens in het formulier, toont validatiefouten, beschermt het hoofdbeheeraccount tegen rolwijzigingen, en sluit automatisch na succesvolle update.

- [**Index.vue**](resources/js/Pages/Admin/Users/Index.vue)   
  Beheerpagina voor gebruikersaccounts. Ondersteunt zoeken, toevoegen, bewerken, blokkeren/activeren en verwijderen van accounts via modale vensters. Responsief ontworpen met aparte weergaves voor mobiel (kaarten) en desktop (tabel). Accounts met de rol 'admin' zijn beschermd tegen wijzigingen.

#### Checkout Pages {#checkout-pages}

- [**OrderSuccess.vue**](resources/js/Pages/Checkout/OrderSuccess.vue)    
  Toont een visuele bevestiging van de bestelling na afronden van het bestelproces. Bevat details zoals bestelnummer, afleveradres, gekozen tijdslot, betaalmethode en status. De gebruiker kan acties uitvoeren zoals bestelling volgen, printen, delen of een bevestigingsmail opnieuw verzenden.

- [**Step1Delivery.vue**](resources/js/Pages/Checkout/Step1Delivery.vue)    
  Eerste stap in het bestelproces. Gebruikers kiezen een bezorgadres en bezorgmoment via een interactieve tijdslot-selector. Valideert invoer en toont samenvatting van de bezorgopties en kosten.

- [**Step2Review.vue**](resources/js/Pages/Checkout/Step2Review.vue)    
  Tweede stap in het bestelproces. De gebruiker controleert bezorgadres, bezorgmoment en winkelwageninhoud. Toont validatieproblemen zoals voorraadtekorten of ontbrekende gegevens, met navigatie naar vorige stappen.

- [**Step3Confirm.vue**](resources/js/Pages/Checkout/Step3Confirm.vue)    
  Laatste stap in het bestelproces. De gebruiker bevestigt de bestelling, kiest een betaalmethode, kan opmerkingen toevoegen en moet akkoord gaan met de voorwaarden. Toont validatieproblemen en verstuurt de bestelling naar de backend via een axios-request. Bevat fallback redirect en foutafhandeling bij mislukte bestellingen.

#### Editor Pages {#editor-pages}

##### resources/js/Pages/Editor/Banners

- [**Edit.vue**](resources/js/Pages/Editor/Banners/Edit.vue)    
    Bewerkpagina voor de banner van een categorie. Toont een preview van de huidige banner en laat editors een nieuwe afbeelding uploaden. Ondersteunt bestandsvalidatie, aanbevolen afmetingen (1920x400) en waarschuwt bij het verlaten van de pagina met niet-opgeslagen wijzigingen.

- [**Index.vue**](resources/js/Pages/Editor/Banners/Index.vue)    
    Overzichtspagina van alle categorieën met hun huidige banners. Toont een visuele preview per banner (of een lege status), met knoppen om banners te bewerken. Ondersteunt responsieve grid-weergave voor mobiel en desktop.

##### resources/js/Pages/Editor/Dashboard

- [**Index.vue**](resources/js/Pages/Editor/Dashboard/Index.vue)    
    Startpagina van het editor-dashboard met een welkomsttekst. Voorbereid op toekomstige uitbreidingen via een grid-layout voor dashboardcomponenten.

##### resources/js/Pages/Editor/News

- [**Create.vue**](resources/js/Pages/Editor/News/Create.vue)   
    Formulierpagina voor het aanmaken van nieuwsartikelen met ondersteuning voor titel, TinyMCE-WYSIWYG-editor, afbeelding en publicatietijd. Biedt directe of geplande publicatie en waarschuwt bij niet-opgeslagen wijzigingen.

- [**Edit.vue**](resources/js/Pages/Editor/News/Edit.vue)   
    Pagina voor het bewerken van bestaande nieuwsartikelen. Gebruikt een TinyMCE-editor voor inhoudsbewerking, ondersteunt afbeeldingvervanging en tijdgestuurde publicatie. Toont huidige afbeelding en voorkomt dat wijzigingen verloren gaan zonder bevestiging.

- [**Index.vue**](resources/js/Pages/Editor/News/Index.vue)   
    Overzichtspagina van nieuwsartikelen met titel, publicatiestatus en publicatiedatum. Biedt knoppen voor bewerken en verwijderen, en een knop om een nieuw artikel aan te maken.

##### resources/js/Pages/Editor/Products

- [**Create.vue**](resources/js/Pages/Editor/Products/Create.vue)   
    Formulierpagina voor het toevoegen van een nieuw product, met velden voor naam, beschrijvingen, prijs, voorraad, afbeelding en dynamisch gefilterde subcategorieën op basis van de geselecteerde categorie.

- [**Edit.vue**](resources/js/Pages/Editor/Products/Edit.vue)   
    Formulierpagina voor het bewerken van een bestaand product, inclusief naam, beschrijvingen, prijs, voorraad, subcategorie, en het wijzigen van de afbeelding. Subcategorieën worden gegroepeerd per categorie.

- [**Index.vue**](resources/js/Pages/Editor/Products/Index.vue)   
    Overzichtspagina voor producten met een tabelweergave, inclusief beknopte info, voorraadstatus, bewerk- en verwijderknoppen en een knop om nieuwe producten toe te voegen. Bevat ook een bevestigingsmodal voor verwijdering.

##### resources/js/Pages/Editor/Promotions
- [**Create.vue**](resources/js/Pages/Editor/Promotions/Create.vue)   
    Creëert een nieuwe promotie met titel, beschrijving, afbeelding, geldigheid en geselecteerde producten inclusief aanbiedingsprijzen. Inclusief zoekfilter en validatie bij verlaten van de pagina.

- [**Edit.vue**](resources/js/Pages/Editor/Promotions/Edit.vue)   
    Bewerk bestaande promoties met titel, beschrijving, geldigheidsdatum, afbeelding en bijbehorende producten inclusief aanbiedingsprijzen. Ondersteunt zoeken/filteren en validatie bij het verlaten van de pagina.

- [**Index.vue**](resources/js/Pages/Editor/Promotions/Index.vue)    
    Overzichtspagina van alle promoties met tabelweergave (desktop) en kaartweergave (mobiel). Bevat knoppen voor bewerken en verwijderen van aanbiedingen, en toont de status (actief/inactief) en geldigheidsdatum.

##### resources/js/Pages/Editor/Settings

- [**Index.vue**](resources/js/Pages/Editor/Settings/Index.vue)    
    Instellingenpagina voor editors om veilig hun wachtwoord te wijzigen. Inclusief formulier met validatie, toggle voor zichtbaarheid van wachtwoorden en een flashbericht bij succes.

##### resources/js/Pages/Editor/Orders

- [**Index.vue**](resources/js/Pages/Orders/Index.vue)    
    Klantoverzicht van bestellingen met statusfilters, zoekfunctie en mogelijkheid om bestellingen te volgen, annuleren of details te bekijken. Mobiel geoptimaliseerde paginatie inbegrepen.

- [**Show.vue**](resources/js/Pages/Orders/Show.vue)    
    Gedetailleerd overzicht van een bestelling, inclusief producten, bezorg- en betaalinformatie, totaalprijs en acties zoals printen, volgen, annuleren of bevestigingsmail verzenden.

- [**Track.vue**](resources/js/Pages/Orders/Track.vue)    
    Toonpagina om een bestelling in real-time te volgen met statusbanner, voortgangsbalk, trackingstappen en bezorginformatie. Inclusief actieknoppen om te vernieuwen of terug te keren.

### Stores {#stores-resourcesjsstores}

- [**Cart.js**](resources/js/Stores/cart.js)    
    Beheer van winkelwagenlogica met Pinia: bevat producttoevoeging, validatie, voorraadcontrole, sortering, orderstatussen en geavanceerde debounce/queue-mechanismen voor realtime updates en foutafhandeling.

### Blade Templates {#blade-templates-resourcesviews}

- [**app.blade.php**](resources/views/app.blade.php)  
  Hoofdsjabloon voor de Laravel-app met Inertia.js. Bevat de CSRF-token, fonts, dynamische paginatitel, routes via Ziggy (```@routes```), en Vue-component via Vite (```@vite```). Dit bestand zorgt ervoor dat de frontend correct wordt ingeladen als een single-page application (SPA).

## Backend {#backend}

### Controllers {#controllers-apphttpcontrollers}

#### Admin Controllers {#admin-controllers}

- [**CategoryStructureController.php**](App/Http/Controllers/Admin/CategoryStructureController.php)   
  Controller voor het beheren van categorieën in het admin dashboard. Verwerkt CRUD-acties met validatie en afbeeldingsbewerking (croppen en optimaliseren via Spatie Image). Verbindt met de frontend via Inertia.js.

- [**SubcategoryStructureController.php**](App/Http/Controllers/Admin/SubcategoryStructureController.php)   
  Controller voor het beheren van subcategorieën in het admin dashboard. Verzorgt CRUD-functionaliteit met validatie, unieke naam per categorie, en soft delete-controle. Integreert met Inertia.js voor frontend-rendering.

- [**UsersController.php**](App/Http/Controllers/Admin/UsersController.php)   
  Deze controller beheert alle gebruikersfunctionaliteiten voor de admin: gebruikers aanmaken, bewerken, verwijderen, blokkeren en zoeken. Rollenbeheer wordt afgehandeld via Spatie Permission. Beveiliging is ingebouwd tegen wijzigingen aan systeembeheerders. Gebruikt Inertia.js voor frontend-koppeling.

#### Editor Controllers {#editor-controllers}

- [**BannerController.php**](App/Http/Controllers/Editor/BannerController.php)    
  Deze controller laat de editor banners beheren voor categorieën. Via een overzichtspagina kan een editor een categorie selecteren, een banner uploaden en deze vervangen. De oude banner wordt veilig verwijderd uit de opslag en de nieuwe afbeelding wordt opgeslagen en gekoppeld aan de categorie. Werkt met Inertia en gebruikt bestandsvalidatie.

- [**CategoryController.php**](App/Http/Controllers/Editor/CategoryController.php)    
  Deze controller stelt editors in staat om productcategorieën te beheren. De editor kan categorieën aanmaken, bewerken of verwijderen. Bij het aanmaken of bijwerken van een categorie kan een bannerafbeelding worden geüpload, die veilig wordt opgeslagen in de publieke disk. Oude banners worden automatisch verwijderd bij updates of verwijdering van een categorie. De controller maakt gebruik van Inertia voor frontend-rendering.

- [**NewsController.php**](App/Http/Controllers/Editor/NewsController.php)    
  Deze controller geeft editors volledige controle over nieuwsartikelen op de website. Ze kunnen artikelen aanmaken, bewerken, publiceren of verwijderen. Bij het aanmaken of bijwerken van een artikel wordt een afbeelding geüpload en opgeslagen op de publieke disk. De controller valideert invoer, ondersteunt geplande of directe publicatie, en verwijdert oude afbeeldingen bij updates. Artikelen worden weergegeven via Inertia op het editor-dashboard.

- [**ProductController.php**](App/Http/Controllers/Editor/ProductController.php)    
  Deze controller stelt editors in staat om producten te beheren binnen het CMS. Ze kunnen nieuwe producten toevoegen, bestaande producten bewerken of verwijderen, en productdetails weergeven op de frontend. Elk product wordt gekoppeld aan een subcategorie en automatisch in de juiste mapstructuur opgeslagen op basis van de bijbehorende categorie- en subcategorienaam. De `show()` methode wordt bovendien gebruikt om productdetails op de publieke productpagina te tonen, inclusief subcategorie- en categorie-informatie.

- [**PromotionController.php**](App/Http/Controllers/Editor/PromotionController.php)   
  Deze controller biedt editors de mogelijkheid om promotiecampagnes te beheren. Elke promotie bevat een afbeelding, een titel, beschrijving, CTA-tekst, geldigheidsdatum en een lijst van producten met aangepaste aanbiedingsprijzen. De controller ondersteunt het aanmaken, bewerken, verwijderen en koppelen van producten met kortingsprijzen. Afbeeldingen worden opgeslagen in de map `images/promotions`, en relaties tussen promoties en producten worden beheerd via een pivot-tabel met extra metadata zoals `discount_price`.

- [**SubcategoryController.php**](App/Http/Controllers/Editor/SubcategoryController.php)    
  Deze controller stelt editors in staat om subcategorieën te beheren die gekoppeld zijn aan hoofd-categorieën. Het bevat functies om subcategorieën te tonen (samen met bijbehorende producten en categorieën), aan te maken, bij te werken en te verwijderen. Validatie garandeert dat elke subcategorie een geldige naam en een bestaande `category_id` heeft. De subcategorieën worden weergegeven in de `Editor/Subcategories/Index` view via Inertia.

#### Checkout Controllers {#checkout-controllers}

- [**CheckoutController.php**](App/Http/Controllers/CheckoutController.php)   
  Deze controller regelt het volledige afrekenproces in drie stappen (bezorgmoment, controle, bevestiging) en maakt gebruik van `CartService`. Het beheert validatie van sessiegegevens, bezorgslots en gebruikersinformatie. Daarnaast verwerkt het bestellingen, vermindert voorraden, genereert unieke ordernummers en toont een orderbevestigingspagina. Inclusief API-routes voor sessiechecks, winkelwageninfo en real-time bezorgslotbeschikbaarheid.

#### Customer Controllers {#customer-controllers}

- [**CustomerDashboardController.php**](App/Http/Controllers/CustomerDashboardController.php)   
  Deze controller beheert het dashboard voor klanten. Het haalt actieve bestellingen (met status 'pending' of 'processing') en afgeronde bestellingen ('completed') op voor de ingelogde gebruiker. Alle relevante relaties zoals `items.product` en `deliverySlot` worden meegegeven. De resultaten worden gerenderd via Inertia in de `Dashboard.vue` view.

#### Overig Controllers {#overig-controllers}

- [**AdminController.php**](App/Http/Controllers/AdminController.php)   
  Deze controller beheert het hoofddashboard voor beheerders (admins). De `dashboard()`-methode haalt statistieken op zoals het totaal aantal gebruikers en stuurt deze via Inertia door naar de `Admin/Dashboard/Index`-view. Deze data wordt gebruikt om een overzichtelijk adminpaneel te tonen.

- [**CartController.php**](App/Http/Controllers/CartController.php)   
  Deze backend-controller regelt alle API-functionaliteit voor de winkelwagen. Hij maakt gebruik van een aparte serviceklasse (`CartService`) om logica gescheiden te houden. De controller ondersteunt het ophalen van winkelwagenitems, toevoegen, bijwerken, verwijderen van producten en het leegmaken van de hele winkelwagen. Alle responses worden in JSON teruggegeven, ideaal voor SPA-integratie met Vue.js via Inertia.

- [**EditorController.php**](App/Http/Controllers/EditorController.php)   
  Controller voor het beheren van het editor-dashboard en het genereren van XML-exports van producten, categorieën en promoties voor externe systemen of documentatie.

- [**OrderController.php**](App/Http/Controllers/OrderController.php)   
  Controller voor het beheren van bestellingen aan de klantzijde. Bevat logica voor orderoverzicht, detailpagina, annuleren, volgen, bevestiging opnieuw versturen en statusweergave.

- [**ProfileController.php**](App/Http/Controllers/ProfileController.php)   
  Controller voor het beheren van gebruikersprofielen en adressen. Ondersteunt profielwijzigingen, accountverwijdering en CRUD-functionaliteit voor klantadressen.

- [**SearchController.php**](App/Http/Controllers/SearchController.php)   
  Controller voor het uitvoeren van zoekopdrachten op producten. Biedt zowel een paginaweergave als API-endpoints voor live suggesties en populaire producten.

- [**SessionExpiredController.php**](App/Http/Controllers/SessionExpiredController.php)   
  Controller die sessie-verlopen afhandelt. Toont een melding of modal, en stuurt de gebruiker door op basis van hun keuze (zoals opnieuw inloggen of terug naar de winkel).

- [**SettingsController.php**](App/Http/Controllers/SettingsController.php)   
  Controller voor het tonen van instellingenpagina's voor admins en editors, en het wijzigen van het wachtwoord via validatie van het huidige wachtwoord.

### Models {#models-appmodels}

- [**CartItem.php**](App/Models/CartItem.php)   
  Model dat individuele winkelwagenitems vertegenwoordigt. Het bevat relaties naar `User` en `Product`, cast de prijs en hoeveelheid correct, en voegt een dynamisch `total` attribuut toe dat de totale prijs per item berekent.

- [**Category.php**](App/Models/Category.php)   
  Model dat een productcategorie voorstelt. Maakt gebruik van soft deletes en bevat relaties naar meerdere `Subcategory`-modellen. Velden zoals `name`, `banner_path`, `description` en `image_path` zijn mass assignable.

- [**DeliverySlot.php**](App/Models/DeliverySlot.php)   
  Model dat bezorgmomenten beheert voor bestellingen. Bevat informatie over datum, tijd, prijs en beschikbare plekken.  

  Relatie: één bezorgmoment hoort bij meerdere `Order`-objecten.  

  Extra logica:
  - `isAvailable()`: controleert of het slot nog geldig en beschikbaar is.
  - `getCurrentAvailableSlots()`: berekent real-time het aantal resterende plekken.
  - `scopeAvailable()`: query scope voor het ophalen van actuele, beschikbare bezorgslots.

- [**NewsArticle.php**](App/Models/NewsArticle.php)   
  Model voor het beheren van nieuwsartikelen op het platform. 
   
  Bevat velden zoals titel, inhoud, afbeelding, publicatiestatus en publicatiedatum.  

  Cast automatisch `is_published` naar een boolean en `published_at` naar een datetime-object.

- [**Order.php**](App/Models/Order.php)   
  Model voor het beheren van bestellingen in het systeem.  

  Bevat relaties met gebruikers, orderitems en bezorgmomenten. 

  Ondersteunt statusbeheer (bijv. bevestigd, onderweg, geannuleerd), betaalstatussen, totalen, schattingen en diverse scopes (zoals `active`, `completed`, `searchByOrderNumber`).  

  Automatiseert orderdatum bij aanmaak en biedt logica voor voorraad- en slotrestauratie bij annulering.

- [**OrderItem.php**](App/Models/OrderItem.php)   
  Model dat individuele producten binnen een bestelling vertegenwoordigt.  

  Bevat relaties met `Order` en `Product`, en slaat een kopie van de productnaam (`product_name`) op voor historische referentie, zelfs als het product later verwijderd wordt.  

  De eigenschap `line_total` berekent automatisch de totale prijs per besteld item (aantal × prijs).

- [**Product.php**](App/Models/Product.php)   
  Model voor individuele producten in de supermarkt.  

  Bevat relaties met `Subcategory`, `OrderItem`, en `Promotion`.  

  Ondersteunt soft deletes en houdt voorraad (`stock_quantity`) en beschikbaarheid (`is_active`) bij.  

  De methode `getCurrentPrice()` retourneert de actuele prijs, met eventuele actieve promotiekorting toegepast.  

  Gebruik `isInStock()` om voorraadstatus te controleren.

- [**Promotion.php**](App/Models/Promotion.php)   
  Model voor promoties binnen de supermarkt.  

  Bevat velden zoals titel, omschrijving, CTA-tekst, afbeelding, activatiestatus (`is_active`) en geldigheidsdatum (`valid_until`).  

  Ondersteunt soft deletes voor veilig verwijderen.  

  Heeft een many-to-many relatie met producten, inclusief een kortingsprijs (`discount_price`) per gekoppeld product.

- [**Subcategory.php**](App/Models/Subcategory.php)   
  Model voor subcategorieën binnen de webshop.  

  Bevat velden zoals `category_id`, `name` en een `banner`.  

  Ondersteunt soft deletes voor veilig verwijderen.  

  Heeft een belongs-to relatie met `Category` en een has-many relatie met `Product`.

- [**User.php**](App/Models/User.php)   
  Model voor gebruikers in het systeem met authenticatie en rolbeheer.  

  Bevat standaardvelden zoals `name`, `email`, `password` en `status`.  

  Ondersteunt rollen via Spatie's `HasRoles` trait (admin, editor, customer).  

  Automatisch toewijzen van de rol `customer` bij het aanmaken van een nieuwe gebruiker.  

  Relaties: heeft meerdere `orders`, en één `address` (leveringsadres).  

  Inclusief handige methods om snel te checken of een gebruiker admin, editor of klant is.  

  Logging bij aanmaken van gebruikers voor betere traceerbaarheid.

- [**UserAddress.php**](App/Models/UserAddress.php)   
  Model voor het adres van een gebruiker.  

  Bevat velden voor straat, huisnummer, postcode, stad en land.  

  Relatie met `User` via een `belongsTo`-koppeling.  

  Heeft een accessormethode `getFormattedAddressAttribute()` die een nette, geformatteerde adresregel teruggeeft, inclusief land, behalve als dat Nederland is.  

  Geschikt voor weergave in frontend of e-mails.

### Services {#services-appservices}

- [**CartService.php**](App/Services/CartService.php)   
  Beheert winkelwagenitems voor gastgebruikers (sessie) en ingelogde gebruikers (database).  

  - Voeg producten toe, update aantallen en verwijder items met voorraadcontrole.  

  - Synchroniseert sessie-items naar database bij inloggen.  

  - Berekent subtotaal, totaal en aantal artikelen.   

  - Logt belangrijke acties en fouten voor monitoring en debugging.

### Routes {#routes}

- [**auth.php**](routes/auth.php)   
  
  Bevat de standaard authenticatieroutes van Laravel Breeze. Hier worden routes voor registratie, login, wachtwoordherstel, e-mailverificatie en uitloggen gedefinieerd. Deze routes zijn opgesplitst in twee groepen: gastgebruikers (```guest``` middleware) en ingelogde gebruikers (```auth``` middleware).

- [**web.php**](routes/web.php)   
  Bevat alle aangepaste routes voor frontendpagina's, klantfunctionaliteit en dashboards voor admin en editors. Dit omvat onder andere de routing voor categorieën, subcategorieën, producten, bestellingen, checkout, winkelwagen en sessiebeheer. Ook bevat dit bestand middleware voor toegangscontrole per gebruikersrol.

## Database {#database}

### Migrations {#migrations-databasemigrations}

- [**0001_01_01_000000_create_users_table.php**](database/migrations/0001_01_01_000000_create_users_table.php)    
  Creëert de volgende tabellen:  
  - `users`: Bevat gebruikersgegevens zoals naam, e-mail, wachtwoord en verificatiestatus.  
  - `password_reset_tokens`: Ondersteunt wachtwoordherstel via e-mail.  
  - `sessions`: Houdt actieve gebruikerssessies bij inclusief IP-adres en user agent.

  Deze migratie ondersteunt standaard Laravel-authenticatie en sessiebeheer.

- [**2024_12_25_172333_create_categories_table.php**](database/migrations/2024_12_25_172333_create_categories_table.php)   
  Maakt de `categories`-tabel aan met de volgende kolommen:  
  - `id`: Primaire sleutel  
  - `name`: Naam van de categorie  
  - `banner_path`: Pad naar de bannerafbeelding  
  - Timestamps (`created_at`, `updated_at`)  
  - Soft deletes (`deleted_at`) voor herstelmogelijkheden

- [**2024_12_25_173125_create_subcategories_table.php**](database/migrations/2024_12_25_173125_create_subcategories_table.php)    
  Bevat subcategorieën die gekoppeld zijn aan `categories`.  

  Kolommen:  
  - `id` (PK)  
  - `category_id` (FK → categories, cascade on delete)  
  - `name`  
  - `created_at`, `updated_at`, `deleted_at`

- [**2024_12_25_174650_create_products_table.php**](database/migrations/2024_12_25_174650_create_products_table.php)   
  Bevat individuele producten binnen een `subcategory`.  

  Kolommen:  
  - `id` (PK)  
  - `subcategory_id` (FK → subcategories, cascade on delete)  
  - `name`  
  - `short_description`, `full_description`  
  - `price` (decimaal, max 999999.99)  
  - `image_path`  
  - `created_at`, `updated_at`, `deleted_at`

- [**2024_12_25_174650_create_products_table.php**](database/migrations/2024_12_25_174650_create_products_table.php)   
  Maakt de volgende tabellen aan voor role-based access control:

  - `permissions`: definieert permissies  
  - `roles`: definieert rollen (optioneel met teams)  
  - `model_has_permissions`: polymorfe koppeling van modellen aan permissies  
  - `model_has_roles`: polymorfe koppeling van modellen aan rollen  
  - `role_has_permissions`: koppelt rollen aan permissies

- [**2025_01_08_163328_add_status_to_users.php**](database/migrations/2025_01_08_163328_add_status_to_users.php)   
  Voegt een `status` kolom toe aan de `users` tabel (`active` of `suspended`) om gebruikersaccounts te beheren.  

  Standaardwaarde is `active`. Kan teruggedraaid worden via `down()` door de kolom te verwijderen.

- [**2025_01_25_124550_create_promotions_table.php**](database/migrations/2025_01_25_124550_create_promotions_table.php)   
  Maakt twee tabellen aan:

  - `promotions`: Bevat actieve aanbiedingen met titel, beschrijving, afbeelding, geldigheid en status.
  - `promotion_products`: Koppelt promoties aan producten met een specifieke kortingsprijs.

  Ondersteunt soft deletes en cascade deletes bij promotie- of productverwijdering.

- [**2025_01_25_124620_create_news_articles_table.php**](database/migrations/2025_01_25_124620_create_news_articles_table.php)    
  Maakt de `news_articles`-tabel aan voor beheer van nieuwsberichten.

  Bevat velden voor titel, inhoud, optionele afbeelding, publicatiestatus en publicatiedatum.  

  Ondersteunt soft deletes en timestamps voor publicatiebeheer.

- [**2025_02_01_215118_cleanup_duplicate_categories_and_add_unique_constraint.php**](database/migrations/2025_02_01_215118_cleanup_duplicate_categories_and_add_unique_constraint.php)    
  Deze migratie zorgt ervoor dat categorienamen uniek zijn binnen de database.

  - **Stap 1:** Verwijdert dubbele categorieën met dezelfde naam. Alleen de oudste blijft behouden. Subcategorieën van de duplicaten worden gekoppeld aan de bewaarde categorie.
  - **Stap 2:** Een unieke database-constraint op `name` wordt toegevoegd om toekomstige duplicaten te voorkomen.

  De `down`-methode verwijdert deze unieke constraint weer.

- [**2025_02_01_231248_cleanup_duplicate_subcategories_and_add_unique_constraint.php**](database/migrations/2025_02_01_231248_cleanup_duplicate_subcategories_and_add_unique_constraint.php)    
  Deze migratie waarborgt dat subcategorieën binnen eenzelfde categorie unieke namen hebben.

  - **Stap 1:** Verwijdert dubbele subcategorieën (zelfde naam + categorie). Alleen de oudste blijft behouden. Producten worden herplaatst naar de overgebleven subcategorie.
  - **Stap 2:** Voegt een unieke constraint toe op `name` en `category_id` om herhaling te voorkomen.

  De `down`-methode verwijdert de constraint weer.

- [**2025_02_02_112653_add_image_path_to_categories.php**](database/migrations/2025_02_02_112653_add_image_path_to_categories.php)    
  Deze migration voegt twee optionele kolommen toe aan de categories tabel:

  - ```description```: een korte tekstuele omschrijving van de categorie.
  - ```image_path```: een pad naar een afbeelding die de categorie visueel kan ondersteunen.

  Deze velden verbeteren de presentatie en context van categorieën binnen de applicatie.

  Bij het terugdraaien van de migration worden deze kolommen verwijderd.

- [**2025_02_04_150205_create_delivery_slots_table.php**](database/migrations/2025_02_04_150205_create_delivery_slots_table.php)    
  Deze migration maakt een `delivery_slots` tabel aan voor het beheren van bezorgmomenten in de webshop.  

  Elke bezorgslot bevat een datum, start- en eindtijd, een prijs en het aantal beschikbare plekken.  
  Dit stelt het systeem in staat om leveringen in tijdvakken in te plannen en te controleren op beschikbaarheid.  

  Bij het terugdraaien van de migration wordt de tabel verwijderd.

- [**2025_02_04_150228_create_orders_table.php**](database/migrations/2025_02_04_150228_create_orders_table.php)   
  Deze migratie maakt de `orders`-tabel aan.

  - Elke bestelling is gekoppeld aan een gebruiker (`user_id`) en optioneel aan een bezorgslot (`delivery_slot_id`).
  - De kolom `status` geeft de voortgang van de bestelling aan: `pending`, `processing`, `completed`, of `cancelled`.
- `total` slaat het totaalbedrag van de bestelling op.

  De `down`-methode verwijdert de tabel.

- [**2025_02_04_150246_create_order_items_table.php**](database/migrations/2025_02_04_150246_create_order_items_table.php)    
  Deze migratie maakt de `order_items`-tabel aan.

  - Elke regel vertegenwoordigt een product in een bestelling, gekoppeld via `order_id` en `product_id`.
  - `quantity` geeft het aantal stuks aan, `price` de prijs per stuk op het moment van bestellen.

  De `down`-methode verwijdert de tabel weer.

- [**025_05_30_183757_enhance_orders_table.php**](database/migrations/2025_05_30_183757_enhance_orders_table.php)    
  Deze migratie voegt extra velden toe aan de `orders`-tabel:

  - `delivery_address` (JSON): optioneel afleveradres.
  - `notes` (TEXT): optionele opmerkingen van de klant.
  - `order_number` (STRING): uniek ordernummer voor externe referentie.

  De `down`-methode verwijdert deze kolommen weer.

- [**2025_05_30_183959_enhance_order_items_table.php**](database/migrations/2025_05_30_183959_enhance_order_items_table.php)   
  Deze migratie voegt de kolom `product_name` toe aan de `order_items`-tabel.

  - Hiermee wordt de naam van het product vastgelegd ten tijde van de bestelling, zodat wijzigingen aan het product later geen invloed hebben op historische ordergegevens.

  De `down`-methode verwijdert deze kolom weer.

- [**2025_05_30_184014_enhance_products_table.php**](database/migrations/2025_05_30_184014_enhance_products_table.php)    
  Deze migratie voegt twee kolommen toe aan de `products`-tabel:

  - `stock_quantity`: een geheel getal dat aangeeft hoeveel voorraad beschikbaar is (standaard 0).
  - `is_active`: een boolean die bepaalt of het product actief zichtbaar is in de winkel (standaard `true`).

  De `down`-methode verwijdert beide kolommen weer.

- [**2025_06_05_151152_create_cart_items_table.php**](database/migrations/2025_06_05_151152_create_cart_items_table.php)    
  Deze migratie maakt de `cart_items`-tabel aan, waarin winkelwagenregels per gebruiker worden opgeslagen.

  - Elke regel bevat een verwijzing naar de gebruiker en het product, met het aantal en de prijs op het moment van toevoegen.
  - Een unieke combinatie van `user_id` en `product_id` voorkomt dubbele regels voor hetzelfde product in één winkelwagen.

  De `down`-methode verwijdert de tabel weer.

- [**2025_06_06_211118_modify_stock_quantity_column_on_products_table.php**](database/migrations/2025_06_06_211118_modify_stock_quantity_column_on_products_table.php)    
  Deze migratie wijzigt de `stock_quantity`-kolom in de `products`-tabel:

  - In de `up`-methode wordt de defaultwaarde van `0` verwijderd zodat `null` mogelijk is.
  - In de `down`-methode wordt de defaultwaarde van `0` weer ingesteld.

  Dit is handig wanneer producten een onbekende of niet-toegepaste voorraad kunnen hebben.

- [**2025_06_12_184707_create_user_addresses_table.php**](database/migrations/2025_06_12_184707_create_user_addresses_table.php)    
  Deze migratie voegt een nieuwe tabel `user_addresses` toe.

  - Elke gebruiker (`user_id`) kan één of meerdere adressen hebben.
  - De tabel bevat velden voor straat, stad, postcode en land (standaard: Netherlands).
  - Bij het verwijderen van een gebruiker worden bijbehorende adressen automatisch verwijderd (`cascade`).

  Deze structuur ondersteunt gebruikers met meerdere leverings- of factuuradressen.

- [**2025_06_23_192442_add_delivery_fee_to_orders_table.php**](database/migrations/2025_06_23_192442_add_delivery_fee_to_orders_table.php)    
  Deze migratie voegt twee extra velden toe aan de `orders`-tabel:

  - **subtotal**: Het totaalbedrag van de bestelling exclusief bezorgkosten.
  - **delivery_fee**: De kosten voor bezorging (standaard: €0.00).

  Deze aanpassing maakt een duidelijker onderscheid mogelijk tussen productkosten en verzendkosten in het orderoverzicht.

- [**2025_06_24_163029_add_house_number_to_user_addresses_table.php**](database/migrations/2025_06_24_163029_add_house_number_to_user_addresses_table.php)    
  Deze migratie voegt het veld `house_number` toe aan de `user_addresses`-tabel.  
  Het maakt het adres vollediger door het huisnummer apart op te slaan, optioneel en geplaatst na het straatveld.

- [**2025_06_30_190512_enhance_orders_and_delivery_slots_for_checkout.php**](database/migrations/2025_06_30_190512_enhance_orders_and_delivery_slots_for_checkout.php)    
  Deze migratie breidt de `orders`-tabel uit met extra velden:

  - `payment_method`: type betaalmethode (bijv. iDEAL, Creditcard).
  - `payment_status`: status van betaling (`pending`, `processing`, `completed`, etc.).
  - `order_notes`: klantnotities, hernoemd vanaf `notes` indien aanwezig.
  - `order_date`: timestamp van de bestelling.
  - `order_number`: uniek bestelnummer, als dit nog niet bestond.

  Daarnaast voegt deze migratie de kolom `current_available` toe aan de `delivery_slots`-tabel om real-time beschikbaarheid bij te houden.

- [**2025_06_30_225941_fix_orders_status_enum.php**](database/migrations/2025_06_30_225941_fix_orders_status_enum.php)    
  Deze migratie actualiseert de `status`-kolom in de `orders`-tabel:

  - Verandert bestaande statuswaarden, waarbij `completed` wordt vervangen door `delivered`.
  - Breidt de toegestane ENUM-waarden uit met `confirmed`, `out_for_delivery` en `delivered` om het volledige bezorgproces te ondersteunen.
  - Voegt een index toe op `status` voor snellere zoekopdrachten.

  De `down`-methode herstelt de oude ENUM-waarden en verwijdert de index.

## Installatie en Setup {#installatie-en-setup}

### Vereisten {#vereisten}
Voor het draaien van dit project zijn de volgende vereisten nodig:

- [**PHP**](https://www.php.net) (v8.3.13 of hoger)
- [**Composer**](https://getcomposer.org/download/) (v2.5.0 of hoger)
- [**Node.js**](https://www.nodejs.org/en) (v16.0.0 of hoger)
- [**npm**](https://www.npmjs.com) (v8.0.0 of hoger)
- **Database**: [MariaDB](https://mariadb.org/download) of [MySQL](https://www.mysql.com/downloads)

Controleer of deze zijn geïnstalleerd met de volgende commando's:
```bash
php -v
composer --version
node -v
npm -v
mariadb --version # of mysql --version
```

---

### Installatiestappen {#installatiestappen}

1. **Pak het project uit**
   - Download en pak het projectbestand uit in de gewenste map.

2. **Backend afhankelijkheden (Laravel)**
    - Voer het volgende commando uit om de PHP-afhankelijkheden te installeren:
      ```bash
      composer install
      ```

3. **Environment Configuratie {#environment-configuratie}**
    - Kopieer environment template:
      ```bash
      cp .env.example .env
      ```
    - Genereer applicatie sleutel:
      ```bash
      php artisan key:generate
      ```

      **BELANGRIJK**: Pas de database instellingen aan in .env:
      ```bash
      DB_CONNECTION=mariadb
      DB_HOST=127.0.0.1
      DB_PORT=3306
      DB_DATABASE=commodium_copia
      DB_USER=root
      DB_PASSWORD=your_password # Pas dit aan naar uw eigen wachtwoord
      ```

4. **Database Setup met Testdata {#database-setup-met-testdata}**
    - Maak eerst de database aan:
    ```bash
    mariadb -u root -p -e "CREATE DATABASE commodium_copia;"

    # Of gebruik: mysql -u root -p -e "CREATE DATABASE commodium_copia;"
    ```

    - Importeer complete database inclusief testdata:
    ```bash
    mariadb -u root -p -D commodium_copia < database/commodium_copia_export.sql

    # Of gebruik: mysql -u root -p -D commodium_copia < database/commodium_copia_export.sql
    ```

    - Maak storage symlink voor afbeeldingen:
    ```bash
    php artisan storage:link
    ```

5. **Frontend dependencies (Vue.js) {#frontend-dependencies-vuejs}**
   - Open een terminal in de root-directory van het project en voer het volgende commando uit:
     ```bash
     npm install
     ```
   - Dit installeert alle noodzakelijke afhankelijkheden die zijn vermeld in het `package.json`-bestand.  
   - TinyMCE assets worden automatisch gekopieerd.

6. **Start Development Servers {#start-development-servers}**
   - Om de ontwikkelserver te starten:
     ```bash
     # Terminal 1: Frontend build
     npm run dev

     # Terminal 2: Laravel backend
     php artisan serve
     ```
   - De server zal starten op `http://localhost:8000`. Open deze URL in je webbrowser.

## Gebruikersaccounts {#gebruikersaccounts}
De database bevat de volgende gebruikersaccounts

| Rol | Email | Wachtwoord | Beschrijving |
|-----|-----|-----|-----|
| **Admin** | admin@cc.nl | password | Volledige toegang tot admin panel |
| **Editor** | editor@cc.nl | password | Content beheer en product management |
| **Klant** | Registreer nieuwe account | - | Klanten kunnen zich zelf registreren en inloggen op hun eigen account |

## Ingebouwde data {#ingebouwde-data}
  - 3 Categorieën: Groenten & Fruit, Bakkerij & Brood, Zuivel & Eieren
  - 27 Producten met afbeeldingen, realistische prijzen, beschrijving en producttitels
  - Gebruikersrollen en permissies volledig geconfigureerd

## Gerealiseerde backend {#gerealiseerde-backend}

- Laravel-integratie
- Authenticatie (Breeze)
- Autorisatie via rollen (Spatie)
- Inertia API-koppeling
- CMS & Winkelwagensysteem

## Troubleshooting {#troubleshooting}

| Probleem | Oplossing |
|----------|-----------|
| ```composer: Command not found``` | Installeer composer |
| ```npm: command not found``` | Installeer Node.js |
| ```mariadb: command not found``` | Gebruik ```mysql``` commando's in plaats van ```mariadb``` |
| Database connection error | Controleer ```.env``` database instellingen en zorg dat MariaDB draait |
| TinyMCE assets ontbreken | Voer ```npm run postinstall``` handmatig uit |
| Storage/afbeeldingen laden niet | Voer ```php artisan storage:link``` uit |

## Feedback {#feedback}
De feedback van de docent heeft geleid tot de volgende aanpassingen:
- **Headers en Commentaar**: Toegevoegd in alle componenten voor betere documentatie en leesbaarheid.
- **Bestandsgrootte**: Gecontroleerd en beperkt tot een maximale grootte van 200 MB.
- **Projectstructuur**: De front-end is gestructureerd met 4 views: een homepage en drie productgerelateerde pagina's.
- **Leesmijbestand**: Beschrijving van alle gebruikte bestanden, installatie-instructies, en projectstructuur toegevoegd.