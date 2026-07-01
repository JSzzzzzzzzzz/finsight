<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);

const currentPasswordInput = ref(null);
const passwordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

watch(
    () => props.show,
    (isOpen) => {
        if (isOpen) {
            form.clearErrors();
        } else {
            form.reset();
            form.clearErrors();
        }
    }
);

const updatePassword = () => {
    form.put(route('user-password.update'), {
        errorBag: 'updatePassword',
        preserveScroll: true,

        onSuccess: () => {
            form.reset();
            emit('close');
        },

        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');

                setTimeout(() => {
                    passwordInput.value?.focus();
                }, 0);
            }

            if (form.errors.current_password) {
                form.reset('current_password');

                setTimeout(() => {
                    currentPasswordInput.value?.focus();
                }, 0);
            }
        },
    });
};

const closeModal = () => {
    if (form.processing) {
        return;
    }

    form.reset();
    form.clearErrors();
    emit('close');
};
</script>

<template>
    <transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="show"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm"
            @click="closeModal"
        >
            <div
                class="bg-gray-800 rounded-lg shadow-2xl overflow-hidden w-full max-w-md m-4 border border-gray-700"
                @click.stop
            >
                <form @submit.prevent="updatePassword">
                    <div
                        class="bg-gradient-to-r from-teal-500 to-cyan-600 px-4 py-3 flex justify-between items-center"
                    >
                        <h3 class="text-base font-bold text-white">
                            Change Password
                        </h3>

                        <button
                            type="button"
                            class="text-teal-100 hover:text-white transition"
                            :disabled="form.processing"
                            @click="closeModal"
                        >
                            ✕
                        </button>
                    </div>

                    <div class="p-6 space-y-4">
                        <div>
                            <label
                                for="current-password"
                                class="block text-sm font-medium text-gray-300"
                            >
                                Current Password
                            </label>

                            <input
                                id="current-password"
                                ref="currentPasswordInput"
                                v-model="form.current_password"
                                type="password"
                                required
                                autocomplete="current-password"
                                class="mt-1 block w-full bg-gray-900 border-gray-600 rounded-md shadow-sm text-white focus:ring-teal-500 focus:border-teal-500 sm:text-sm"
                            />

                            <p
                                v-if="form.errors.current_password"
                                class="mt-1 text-xs text-red-400"
                            >
                                {{ form.errors.current_password }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="new-password"
                                class="block text-sm font-medium text-gray-300"
                            >
                                New Password
                            </label>

                            <input
                                id="new-password"
                                ref="passwordInput"
                                v-model="form.password"
                                type="password"
                                required
                                autocomplete="new-password"
                                class="mt-1 block w-full bg-gray-900 border-gray-600 rounded-md shadow-sm text-white focus:ring-teal-500 focus:border-teal-500 sm:text-sm"
                            />

                            <p
                                v-if="form.errors.password"
                                class="mt-1 text-xs text-red-400"
                            >
                                {{ form.errors.password }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="password-confirmation"
                                class="block text-sm font-medium text-gray-300"
                            >
                                Confirm New Password
                            </label>

                            <input
                                id="password-confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                required
                                autocomplete="new-password"
                                class="mt-1 block w-full bg-gray-900 border-gray-600 rounded-md shadow-sm text-white focus:ring-teal-500 focus:border-teal-500 sm:text-sm"
                            />

                            <p
                                v-if="form.errors.password_confirmation"
                                class="mt-1 text-xs text-red-400"
                            >
                                {{ form.errors.password_confirmation }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="bg-gray-900 px-4 py-3 sm:px-6 flex justify-end border-t border-gray-700"
                    >
                        <button
                            type="button"
                            class="mr-3 px-4 py-2 text-sm font-bold text-gray-300 hover:text-white transition disabled:opacity-50"
                            :disabled="form.processing"
                            @click="closeModal"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            class="px-4 py-2 bg-gradient-to-r from-teal-500 to-cyan-500 text-white text-sm font-bold rounded shadow hover:from-teal-600 hover:to-cyan-600 transition disabled:opacity-50"
                            :disabled="form.processing"
                        >
                            {{
                                form.processing
                                    ? 'Updating...'
                                    : 'Update Password'
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </transition>
</template>