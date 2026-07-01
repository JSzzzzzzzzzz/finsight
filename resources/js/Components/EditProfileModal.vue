<script setup>
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },

    user: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['close']);

const form = useForm({
    name: '',
    email: '',
});

/*
|--------------------------------------------------------------------------
| Load the latest profile information whenever the modal opens
|--------------------------------------------------------------------------
*/
watch(
    () => props.show,
    (isOpen) => {
        if (isOpen && props.user) {
            form.name = props.user.name ?? '';
            form.email = props.user.email ?? '';
            form.clearErrors();
        }
    },
    {
        immediate: true,
    }
);

/*
|--------------------------------------------------------------------------
| Save profile information through Laravel Fortify
|--------------------------------------------------------------------------
*/
const save = () => {
    form.put(route('user-profile-information.update'), {
        errorBag: 'updateProfileInformation',
        preserveScroll: true,

        onSuccess: () => {
            emit('close');
        },
    });
};

const closeModal = () => {
    if (form.processing) {
        return;
    }

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
                <form @submit.prevent="save">
                    <!-- Header -->
                    <div
                        class="bg-gradient-to-r from-teal-500 to-cyan-600 px-4 py-3 flex justify-between items-center"
                    >
                        <h3 class="text-base font-bold text-white">
                            Edit Profile Information
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

                    <!-- Form -->
                    <div class="p-6 space-y-4">
                        <!-- Name -->
                        <div>
                            <label
                                for="profile-name"
                                class="block text-sm font-medium text-gray-300"
                            >
                                Full Name
                            </label>

                            <input
                                id="profile-name"
                                v-model.trim="form.name"
                                type="text"
                                required
                                autocomplete="name"
                                class="mt-1 block w-full bg-gray-900 border-gray-600 rounded-md shadow-sm text-white focus:ring-teal-500 focus:border-teal-500 sm:text-sm"
                            />

                            <p
                                v-if="form.errors.name"
                                class="mt-1 text-xs text-red-400"
                            >
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Email -->
                        <div>
                            <label
                                for="profile-email"
                                class="block text-sm font-medium text-gray-300"
                            >
                                Email Address
                            </label>

                            <input
                                id="profile-email"
                                v-model.trim="form.email"
                                type="email"
                                required
                                autocomplete="email"
                                class="mt-1 block w-full bg-gray-900 border-gray-600 rounded-md shadow-sm text-white focus:ring-teal-500 focus:border-teal-500 sm:text-sm"
                            />

                            <p
                                v-if="form.errors.email"
                                class="mt-1 text-xs text-red-400"
                            >
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <p
                            v-if="form.recentlySuccessful"
                            class="text-sm text-emerald-400"
                        >
                            Profile updated successfully.
                        </p>
                    </div>

                    <!-- Footer -->
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
                                    ? 'Saving...'
                                    : 'Save Changes'
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </transition>
</template>