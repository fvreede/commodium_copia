/**
 * Bestandsnaam: ApplicationLogo.vue
 * Auteur: Fabio Vreede
 * Versie: v1.0.2
 * Datum: 2025-01-03
 * Tijd: 14:34:22
 * Doel: Responsive applicatie logo component voor merk identificatie met adaptieve tekst weergave. Toont verkorte merknaam op mobile ("ComCopia") en volledige merknaam op desktop ("Commodium Copia") voor optimale ruimtebenutting en brand herkenning. Gecentreerd met flexbox layout en consistent typography systeem.
 */


<template>
    <!-- 
        LOGO CONTAINER
        Flexible container voor centrering en responsive layout
    -->
    <div class="flex flex-shrink-0 items-center justify-center">
        
        <!-- Mobile Merkweergave -->
        <!-- Verkorte merknaam voor compacte mobile interface -->
        <span class="text-2xl font-bold text-gray-700 block lg:hidden">ComCopia</span>
        
        <!-- Desktop Merkweergave -->
        <!-- Volledige merknaam voor desktop branding impact -->
        <span class="hidden lg:block text-2xl font-bold text-gray-700">Commodium Copia</span>
    </div>
</template>