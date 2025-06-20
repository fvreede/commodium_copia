<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/outline';
import { ref, computed } from 'vue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    // Add return_to parameter if it exists
    const data = {...form.data() };
    if (returnTo.value) {
        data.return_to = returnTo.value;
    }

    form.post(route('login'), {
        data: data,
        onFinish: () => form.reset('password'),
    });
};

// Toggle password visibility methods
const passwordVisible = ref(false);

const togglePasswordVisibility = () => {
    passwordVisible.value = !passwordVisible.value;
}

const returnTo = computed(() => {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('returnTo');
});
</script>

<template>
    <GuestLayout>
        <Head title="Inloggen" />

        <div class="max-w-md mx-auto text-center p-8 bg-white">
            <h2 class="text-xl font-bold text-gray-900">Even inloggen</h2>
        </div>

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
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

            <div class="mt-4 relative">
                <InputLabel for="password" value="Wachtwoord" />

                <TextInput
                    id="password"
                    :type="passwordVisible ? 'text' : 'password'"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
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

            <div class="mt-4 block">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-sm text-gray-600">Onthoud mij</span>
                </label>
            </div>

            <!-- Forgot password link -->
            <div class="mt-4 flex justify-end">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Wachtwoord vergeten?
                </Link>
            </div>

            <!-- Login button -->
            <div class="mt-6">
                <PrimaryButton
                    class="w-full justify-center"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Inloggen
                </PrimaryButton>
            </div>

            <!-- Registration link - separate section with proper spacing -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Nog geen account?
                    <Link 
                        :href="route('register')" 
                        class="ml-1 text-blue-600 hover:text-blue-500 hover:underline font-medium"
                    >
                        Registreer hier
                    </Link>
                </p>
            </div>
        </form>
    </GuestLayout>
</template>