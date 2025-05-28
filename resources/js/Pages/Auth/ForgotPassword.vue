<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Wachtwoord vergeten" />

        <div class="max-w-md mx-auto text-center p-8 bg-white">
            <h2 class="text-xl font-bold text-gray-900">Wachtwoord vergeten</h2>
        </div>

        <div class="mb-6 text-sm text-gray-600 text-center">
            Wachtwoord vergeten? Geen probleem. Voer je emailadres in en 
            we sturen je een link om een nieuw wachtwoord in te stellen.
        </div>

        <div
            v-if="status"
            class="mb-4 text-sm font-medium text-green-600 text-center"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Emailadres" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <!-- Send reset link button -->
            <div class="mt-6">
                <PrimaryButton
                    class="w-full justify-center"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Verstuur resetlink
                </PrimaryButton>
            </div>

            <!-- Back to login link -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Weet je het weer?
                    <Link 
                        :href="route('login')" 
                        class="ml-1 text-blue-600 hover:text-blue-500 hover:underline font-medium"
                    >
                        Terug naar inloggen
                    </Link>
                </p>
            </div>
        </form>
    </GuestLayout>
</template>