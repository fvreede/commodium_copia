{{--
/**
 * Bestandsnaam: app.blade.php (resources/views)
 * Auteur: Fabio Vreede
 * Versie: v1.0.2   
 * Datum: 2025-01-03
 * Tijd: 14:34:22
 * Doel: Hoofd HTML template voor Laravel Inertia.js applicatie. Bevat basis HTML structuur, meta tags,
 *       CSRF beveiliging, font loading, Vite asset management, en Inertia.js integratie. Dient als
 *       foundation template voor alle pagina's met responsive viewport, SEO optimalisatie, en
 *       moderne build tool integratie voor optimal performance en developer experience.
 */
--}}

<!DOCTYPE html>
{{-- HTML document met dynamische locale uit Laravel config --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        {{-- Character encoding voor Unicode ondersteuning --}}
        <meta charset="utf-8">
        
        {{-- Responsive viewport meta tag voor mobile optimization --}}
        <meta name="viewport" content="width=device-width, initial-scale=1">
        
        {{-- CSRF token voor Laravel security (gebruikt door Axios en forms) --}}
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        {{-- Dynamic page title met Inertia integration en fallback --}}
        <title inertia>{{ config('app.name', 'Commodium Copia') }}</title>

        {{-- ========== FONT LOADING OPTIMISATIE ========== --}}
        {{-- Preconnect voor snellere font loading van Bunny Fonts CDN --}}
        <link rel="preconnect" href="https://fonts.bunny.net">
        
        {{-- Figtree font family loading met display=swap voor performance --}}
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        {{-- ========== LARAVEL INERTIA.JS INTEGRATIE ========== --}}
        {{-- Laravel routes helper voor JavaScript toegang tot named routes --}}
        @routes
        
        {{-- Vite asset bundling voor development en production builds --}}
        {{-- Laadt main JavaScript entry point en component-specific Vue file --}}
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        
        {{-- Inertia head manager voor dynamic meta tags, titles, en head content --}}
        @inertiaHead
    </head>
    
    {{-- Body met Tailwind CSS utility classes voor consistent typography --}}
    <body class="font-sans antialiased">
        {{-- Inertia.js mount point - hier wordt de Vue.js applicatie ge-render --}}
        @inertia
    </body>
</html>