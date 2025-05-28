<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/outline';
import { ref } from 'vue';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

// Toggle password visibility methods
const passwordVisible = ref(false);
const confirmPasswordVisible = ref(false);

const togglePasswordVisibility = () => {
    passwordVisible.value = !passwordVisible.value;
}

const toggleConfirmPasswordVisibility = () => {
    confirmPasswordVisible.value = !confirmPasswordVisible.value;
}
</script>

<template>
    <GuestLayout>
        <Head title="Wachtwoord resetten" />

        <div class="max-w-md mx-auto text-center p-8 bg-white">
            <h2 class="text-xl font-bold text-gray-900">Nieuw wachtwoord instellen</h2>
        </div>

        <div class="mb-6 text-sm text-gray-600 text-center">
            Stel een nieuw wachtwoord in voor je account.
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

            <div class="mt-4 relative">
                <InputLabel for="password" value="Nieuw wachtwoord" />

                <TextInput
                    id="password"
                    :type="passwordVisible ? 'text' : 'password'"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
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

            <div class="mt-4 relative">
                <InputLabel
                    for="password_confirmation"
                    value="Herhaal nieuw wachtwoord"
                />

                <TextInput
                    id="password_confirmation"
                    :type="confirmPasswordVisible ? 'text' : 'password'"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <button
                    type="button"
                    @click="toggleConfirmPasswordVisibility"
                    class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 mt-6"
                >
                    <EyeIcon v-if="!confirmPasswordVisible" class="h-5 w-5"/>
                    <EyeSlashIcon v-else class="h-5 w-5"/>
                </button>

                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <!-- Reset password button -->
            <div class="mt-6">
                <PrimaryButton
                    class="w-full justify-center"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Wachtwoord resetten
                </PrimaryButton>
            </div>

            <!-- Back to login link -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Toch niet resetten?
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