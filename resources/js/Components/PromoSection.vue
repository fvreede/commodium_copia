/**
 * Bestandsnaam: PromoSection.vue
 * Auteur: Fabio Vreede
 * Versie: v1.3.0
 * Datum: 31-10-2024
 * Tijd: 16:42:58
 * Doel: Deze component toont een promotie met een achtergrondafbeelding, overlay, en een call-to-action knop.
 */

<template>
  <div class="relative min-h-screen">
    <!-- Achtergrondafbeelding voor de promotie -->
    <img src="@/assets/images/herfstPromotion.jpg" alt="Herfst promotie" class="absolute inset-0 h-full w-full object-cover"/>

    <!-- Overlay om tekst leesbaar te maken -->
    <div class="absolute inset-0 bg-black opacity-30"></div>

    <!-- Content -->
    <div class="relative z-10 flex flex-col items-center justify-center min-h-screen text-center px-4 sm:px-6 lg:px-8 pt-16 sm:pt-20 md:pt-24">
      <h1 class="h_text text-3xl sm:text-4xl md:text-5xl font-bold tracking-tight">
        De herfst is gearriveerd - vers van het land op jouw bord!
      </h1>
      <p class="p_text mt-4 text-base sm:text-lg md:text-xl">
        Deze week kun je genieten van de beste herfstproducten, sappige appels, pompoenen, en alles wat je nodig hebt om die cozy gerechten op tafel te zetten. Oh, en psst... we hebben ook iets lekkers voor bij de warme choco!
      </p>
      <a href="#" class="cta_button mt-6 sm:mt-8 inline-block bg-orange-600 hover:bg-orange-700 text-white text-base sm:text-lg font-medium py-2 sm:py-3 px-6 sm:px-8 rounded-md shadow-lg transition duration-300 hover:-translate-y-1 hover:shadow-xl">
        Shop nu en krijg 15% korting
      </a>
    </div>
  </div>
</template>

<style scoped>
.h_text {
  color: #F6EBD8;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
  font-family: 'Trebuchet MS', 'Lucida Sans Unicode', 'Lucida Grande', 'Lucida Sans', Arial, sans-serif;
}

.p_text {
  color: #EDE4D9;
  text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.6);
  font-family: 'Trebuchet MS', 'Lucida Sans Unicode', 'Lucida Grande', 'Lucida Sans', Arial, sans-serif;
  line-height: 1.5;
  max-width: 100%;
  margin-left: auto;
  margin-right: auto;
}

@media (min-width: 640px) {
  .p_text {
    max-width: 80%;
  }
}

@media (min-width: 768px) {
  .p_text {
    max-width: 600px;
  }
}
</style>