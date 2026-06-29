<script setup>
import { ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    apiConnection: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close']);

const isEditing = ref(!props.apiConnection);
const showSecret = ref(false);
const isDeleting = ref(false);

const form = useForm({
    api_key: '',
    api_secret: '',
});

const resetCredentialForm = () => {
    form.reset();
    form.clearErrors();
    showSecret.value = false;
};

const showConnectedState = () => {
    resetCredentialForm();
    isEditing.value = false;
};

const startCredentialEntry = () => {
    resetCredentialForm();
    isEditing.value = true;
};

const cancelEditing = () => {
    resetCredentialForm();

    if (props.apiConnection) {
        isEditing.value = false;
        return;
    }

    emit('close');
};

watch(
    () => props.show,
    (isVisible) => {
        if (!isVisible) {
            return;
        }

        resetCredentialForm();
        isEditing.value = !props.apiConnection;
    }
);

watch(
    () => props.apiConnection,
    (connection) => {
        if (props.show && connection) {
            isEditing.value = false;
        }
    }
);

const saveCredentials = () => {
    form.post(route('settings.api-key.store'), {
        preserveScroll: true,

        onSuccess: () => {
            resetCredentialForm();
            isEditing.value = false;

            window.alert(
                'The Luno connection was verified and saved successfully.'
            );
        },

        onError: () => {
            isEditing.value = true;
        },
    });
};

const disconnectExchange = () => {
    const confirmed = window.confirm(
        'Disconnect Luno from FinSight? Your stored API credentials will be deleted. Existing portfolio history will remain available.'
    );

    if (!confirmed) {
        return;
    }

    isDeleting.value = true;

    router.delete(route('settings.api-key.destroy'), {
        preserveScroll: true,

        onSuccess: () => {
            resetCredentialForm();
            isEditing.value = true;
            emit('close');

            window.alert(
                'The Luno exchange connection was disconnected successfully.'
            );
        },

        onFinish: () => {
            isDeleting.value = false;
        },
    });
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
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm transition-opacity"
            @click="emit('close')"
        >
            <div
                class="bg-gray-800 rounded-lg shadow-2xl overflow-hidden w-full max-w-lg m-4 border border-gray-700"
                @click.stop
            >
                <!-- HEADER -->
                <div
                    class="bg-gradient-to-r from-teal-500 to-cyan-600 px-4 py-3 flex justify-between items-center"
                >
                    <h3 class="text-base font-bold text-white">
                        Exchange Connection
                    </h3>

                    <button
                        type="button"
                        class="text-gray-200 hover:text-white transition"
                        @click="emit('close')"
                    >
                        ✕
                    </button>
                </div>

                <!-- BODY -->
                <div class="p-6">
                    <!-- CONNECTED STATE -->
                    <div
                        v-if="apiConnection && !isEditing"
                        class="space-y-5"
                    >
                        <div
                            class="flex items-center p-3 bg-emerald-900/20 rounded-lg border border-emerald-500/30"
                        >
                            <div
                                class="flex-shrink-0 h-10 w-10 rounded-full bg-emerald-800/50 flex items-center justify-center text-emerald-400 mr-4 border border-emerald-500/50"
                            >
                                ✓
                            </div>

                            <div>
                                <p class="text-sm font-bold text-emerald-400">
                                    Connection Active
                                </p>

                                <p class="text-xs text-emerald-200/70">
                                    Your Luno credentials are encrypted and stored securely.
                                </p>
                            </div>
                        </div>

                        <div>
                            <label
                                class="block text-xs font-bold text-gray-400 uppercase tracking-wide"
                            >
                                Exchange
                            </label>

                            <div
                                class="mt-1 text-sm font-bold text-white bg-gray-900 p-3 rounded border border-gray-600"
                            >
                                {{ apiConnection.exchange }}
                            </div>
                        </div>

                        <div>
                            <label
                                class="block text-xs font-bold text-gray-400 uppercase tracking-wide"
                            >
                                API Key
                            </label>

                            <div
                                class="mt-1 font-mono text-sm font-bold text-white bg-gray-900 p-3 rounded border border-gray-600 tracking-wider break-all"
                            >
                                {{ apiConnection.masked_api_key }}
                            </div>
                        </div>

                        <div>
                            <label
                                class="block text-xs font-bold text-gray-400 uppercase tracking-wide"
                            >
                                Secret Key
                            </label>

                            <div
                                class="mt-1 font-mono text-sm font-bold text-gray-400 bg-gray-900 p-3 rounded border border-gray-600 tracking-wider"
                            >
                                {{ apiConnection.masked_secret }}
                            </div>

                            <p class="mt-1 text-xs text-gray-500">
                                The secret key cannot be displayed after it has been saved.
                            </p>
                        </div>

                        <div v-if="apiConnection.updated_at">
                            <label
                                class="block text-xs font-bold text-gray-400 uppercase tracking-wide"
                            >
                                Last Updated
                            </label>

                            <p class="mt-1 text-sm text-gray-300">
                                {{ apiConnection.updated_at }}
                            </p>
                        </div>

                        <div
                            class="flex flex-col sm:flex-row gap-3 pt-2"
                        >
                            <button
                                type="button"
                                class="flex-1 bg-teal-600 hover:bg-teal-500 text-white font-bold py-2.5 px-4 rounded-lg transition"
                                @click="startCredentialEntry"
                            >
                                Replace Credentials
                            </button>

                            <button
                                type="button"
                                :disabled="isDeleting"
                                class="flex-1 border border-red-500/50 text-red-400 hover:bg-red-500 hover:text-white font-bold py-2.5 px-4 rounded-lg transition disabled:opacity-50"
                                @click="disconnectExchange"
                            >
                                {{
                                    isDeleting
                                        ? 'Disconnecting...'
                                        : 'Disconnect Exchange'
                                }}
                            </button>
                        </div>
                    </div>

                    <!-- CREDENTIAL FORM -->
                    <div v-else class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-300">
                                {{
                                    apiConnection
                                        ? 'Enter new Luno credentials to replace the current connection.'
                                        : 'Enter your Luno read-only API credentials.'
                                }}
                            </p>

                            <p class="mt-1 text-xs text-yellow-400">
                                Use read-only credentials. Do not enable trading or withdrawal permissions.
                            </p>
                        </div>

                        <div>
                            <label
                                for="api_key"
                                class="block text-sm font-medium text-gray-300"
                            >
                                API Key
                            </label>

                            <input
                                id="api_key"
                                v-model.trim="form.api_key"
                                type="text"
                                autocomplete="off"
                                spellcheck="false"
                                placeholder="Paste API Key here..."
                                class="mt-1 block w-full bg-gray-900 border-gray-600 rounded-md shadow-sm text-white placeholder-gray-500 focus:border-teal-500 focus:ring-teal-500 sm:text-sm"
                            />

                            <div
                                v-if="form.errors.api_key"
                                class="text-red-400 text-xs mt-1"
                            >
                                {{ form.errors.api_key }}
                            </div>
                        </div>

                        <div>
                            <label
                                for="api_secret"
                                class="block text-sm font-medium text-gray-300"
                            >
                                Secret Key
                            </label>

                            <div class="relative mt-1">
                                <input
                                    id="api_secret"
                                    v-model.trim="form.api_secret"
                                    :type="showSecret ? 'text' : 'password'"
                                    autocomplete="new-password"
                                    spellcheck="false"
                                    placeholder="Paste Secret Key here..."
                                    class="block w-full bg-gray-900 border-gray-600 rounded-md shadow-sm text-white placeholder-gray-500 focus:border-teal-500 focus:ring-teal-500 sm:text-sm pr-10"
                                />

                                <button
                                    v-if="form.api_secret"
                                    type="button"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-300 hover:text-white"
                                    @click="showSecret = !showSecret"
                                >
                                    {{ showSecret ? 'Hide' : 'Show' }}
                                </button>
                            </div>

                            <div
                                v-if="form.errors.api_secret"
                                class="text-red-400 text-xs mt-1"
                            >
                                {{ form.errors.api_secret }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FOOTER -->
                <div
                    class="bg-gray-900 px-4 py-3 sm:px-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 border-t border-gray-700"
                >
                    <button
                        v-if="isEditing"
                        type="button"
                        class="w-full sm:w-auto rounded-md border border-gray-600 px-4 py-2 bg-gray-800 text-gray-300 hover:bg-gray-700 hover:text-white"
                        @click="cancelEditing"
                    >
                        {{ apiConnection ? 'Back' : 'Cancel' }}
                    </button>

                    <button
                        v-if="isEditing"
                        type="button"
                        :disabled="form.processing"
                        class="w-full sm:w-auto rounded-md px-4 py-2 bg-gradient-to-r from-teal-500 to-cyan-500 font-bold text-white hover:from-teal-600 hover:to-cyan-600 disabled:opacity-50"
                        @click="saveCredentials"
                    >
                        {{
                            form.processing
                                ? 'Verifying...'
                                : apiConnection
                                    ? 'Replace Securely'
                                    : 'Connect Securely'
                        }}
                    </button>

                    <button
                        v-else
                        type="button"
                        class="w-full sm:w-auto rounded-md border border-gray-600 px-4 py-2 bg-gray-800 text-gray-300 hover:bg-gray-700 hover:text-white"
                        @click="emit('close')"
                    >
                        Done
                    </button>
                </div>
            </div>
        </div>
    </transition>
</template>

<style scoped>
input[type='password']::-ms-reveal,
input[type='password']::-ms-clear {
    display: none;
}
</style>