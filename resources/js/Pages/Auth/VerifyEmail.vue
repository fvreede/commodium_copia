<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Email verificatie" />

        <div class="max-w-md mx-auto text-center p-8 bg-white">
            <h2 class="text-xl font-bold text-gray-900">Verifieer je emailadres</h2>
        </div>

        <div class="mb-6 text-sm text-gray-600 text-center">
            Bedankt voor het registreren! Voordat je kunt beginnen, kun je je 
            emailadres verifiëren door op de link te klikken die we je hebben gemaild? 
            Als je de email niet hebt ontvangen, sturen we je graag een nieuwe.
        </div>

        <div
            class="mb-6 text-sm font-medium text-green-600 text-center"
            v-if="verificationLinkSent"
        >
            Een nieuwe verificatielink is verstuurd naar het emailadres dat je 
            hebt opgegeven tijdens de registratie.
        </div>

        <form @submit.prevent="submit">
            <!-- Resend verification button -->
            <div class="mt-6">
                <PrimaryButton
                    class="w-full justify-center"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Verstuur nieuwe verificatie-email
                </PrimaryButton>
            </div>

            <!-- Logout link -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Verkeerd emailadres gebruikt?
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="ml-1 text-blue-600 hover:text-blue-500 hover:underline font-medium"
                    >
                        Uitloggen
                    </Link>
                </p>
            </div>
        </form>
    </GuestLayout>
</template>