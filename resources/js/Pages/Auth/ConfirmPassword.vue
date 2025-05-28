<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/outline';
import { ref } from 'vue';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};

// Toggle password visibility methods
const passwordVisible = ref(false);

const togglePasswordVisibility = () => {
    passwordVisible.value = !passwordVisible.value;
}
</script>

<template>
    <GuestLayout>
        <Head title="Wachtwoord bevestigen" />

        <div class="max-w-md mx-auto text-center p-8 bg-white">
            <h2 class="text-xl font-bold text-gray-900">Wachtwoord bevestigen</h2>
        </div>

        <div class="mb-6 text-sm text-gray-600 text-center">
            Dit is een beveiligd deel van de applicatie. Bevestig je 
            wachtwoord voordat je doorgaat.
        </div>

        <form @submit.prevent="submit">
            <div class="relative">
                <InputLabel for="password" value="Wachtwoord" />
                
                <TextInput
                    id="password"
                    :type="passwordVisible ? 'text' : 'password'"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    autofocus
                />

                <button
                    type="button"
                    @click="togglePasswordVisibility"
                    class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 mt-6"
                >
                    <EyeIcon v-if="!passwordVisible" class="h-5 w-5"/>
                    <EyeSlashIcon v-else class="h-5 w-5"/>
                </button>

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <!-- Confirm button -->
            <div class="mt-6">
                <PrimaryButton
                    class="w-full justify-center"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Bevestigen
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>