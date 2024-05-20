<template>
    <div class="inline-flex rounded-md shadow-sm">
        <Menu as="div" class="relative -ml-px block">
            <MenuButton class="relative inline-flex items-center rounded-md bg-indigo-600 px-4 py-2.5 text-white text-sm font-semibold hover:bg-indigo-500 focus:z-10">
                Actions
                <ChevronDownIcon class="-mr-0.5 ml-2 h-5 w-5" aria-hidden="true" />
            </MenuButton>
            <transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
                <MenuItems class="absolute right-0 z-10 -mr-1 mt-2 w-56 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                    <div class="py-1">
                        <MenuItem v-slot="{ active }">
                            <a @click.prevent="dialog.import = true" :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'cursor-pointer block px-4 py-2 text-sm']">
                                Import translations
                            </a>
                        </MenuItem>
                        <MenuItem v-slot="{ active }">
                            <a @click.prevent="dialog.export = true" :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'cursor-pointer block px-4 py-2 text-sm']">
                                Export translations
                            </a>
                        </MenuItem>
                    </div>
                </MenuItems>
            </transition>
        </Menu>
    </div>

    <TransitionRoot as="template" :show="dialog.import">
        <Dialog @close="dialog.import = false" as="div" class="relative z-50">
            <TransitionChild as="div" enter="ease-in duration-200" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in transition-opacity duration-100" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 opacity-50 transition-opacity bg-gray-500" />
            </TransitionChild>

            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <TransitionChild as="template" enter="ease-out duration-100" enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" enter-to="opacity-75 translate-y-0 sm:scale-100" leave="ease-in duration-200" leave-from="opacity-75 translate-y-0 sm:scale-100" leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                        <DialogPanel class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                                        <ArrowDownTrayIcon class="h-6 w-6 text-indigo-600" aria-hidden="true" />
                                    </div>
                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                        <DialogTitle as="h3" class="text-base font-semibold leading-6 text-gray-900">
                                            Import translations?
                                        </DialogTitle>
                                        <div class="mt-2">
                                            <p class="text-sm text-gray-500">
                                                Are you sure you want to import all translations?
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                <button @click.prevent="importTranslations()" type="button" :disabled="request.ongoing" :class="[request.ongoing ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer']" class="inline-flex w-full justify-center rounded-md text-white bg-indigo-600 hover:bg-indigo-500 px-3 py-2 text-sm font-semibold shadow-sm sm:ml-3 sm:w-auto">
                                    <ArrowPathIcon v-if="request.ongoing" class="-ml-0.5 mr-2.5 h-5 w-5 animate animate-spin" aria-hidden="true" />
                                    <ArrowDownTrayIcon v-else class="-ml-0.5 mr-2.5 h-5 w-5" aria-hidden="true" />
                                    Import
                                </button>
                                <button @click="dialog.import = false" type="button" :disabled="request.ongoing" :class="[request.ongoing ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer']" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-gray-900 hover:bg-gray-50 text-sm font-semibold shadow-sm ring-1 ring-inset ring-gray-300 sm:mt-0 sm:w-auto">
                                    Cancel
                                </button>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>

    <TransitionRoot as="template" :show="dialog.export">
        <Dialog @close="dialog.export = false" as="div" class="relative z-50">
            <TransitionChild as="div" enter="ease-in duration-200" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in transition-opacity duration-100" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 opacity-50 transition-opacity bg-gray-500" />
            </TransitionChild>

            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <TransitionChild as="template" enter="ease-out duration-100" enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" enter-to="opacity-75 translate-y-0 sm:scale-100" leave="ease-in duration-200" leave-from="opacity-75 translate-y-0 sm:scale-100" leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                        <DialogPanel class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                                        <ArrowUpTrayIcon class="h-6 w-6 text-indigo-600" aria-hidden="true" />
                                    </div>
                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                        <DialogTitle as="h3" class="text-base font-semibold leading-6 text-gray-900">
                                            Export translations?
                                        </DialogTitle>
                                        <div class="mt-2">
                                            <p class="text-sm text-gray-500">
                                                Are you sure you want to export all translations?
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                <button @click.prevent="exportTranslations()" type="button" :disabled="request.ongoing" :class="[request.ongoing ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer']" class="inline-flex w-full justify-center rounded-md text-white bg-indigo-600 hover:bg-indigo-500 px-3 py-2 text-sm font-semibold shadow-sm sm:ml-3 sm:w-auto">
                                    <ArrowPathIcon v-if="request.ongoing" class="-ml-0.5 mr-2.5 h-5 w-5 animate animate-spin" aria-hidden="true" />
                                    <ArrowUpTrayIcon v-else class="-ml-0.5 mr-2.5 h-5 w-5" aria-hidden="true" />
                                    Export
                                </button>
                                <button @click="dialog.export = false" type="button" :disabled="request.ongoing" :class="[request.ongoing ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer']" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-gray-900 hover:bg-gray-50 text-sm font-semibold shadow-sm ring-1 ring-inset ring-gray-300 sm:mt-0 sm:w-auto">
                                    Cancel
                                </button>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script>
import {
    router,
} from '@inertiajs/vue3';

import {
    Dialog,
    DialogPanel,
    DialogTitle,
    Menu,
    MenuButton,
    MenuItem,
    MenuItems,
    TransitionChild,
    TransitionRoot,
} from '@headlessui/vue';

import {
    ArrowDownTrayIcon,
    ArrowUpTrayIcon,
    ArrowPathIcon,
    ChevronDownIcon,
} from '@heroicons/vue/20/solid'

import {
    request
} from "crudhub/constants.js";

export default {
    components: {
        Dialog,
        DialogPanel,
        DialogTitle,
        Menu,
        MenuButton,
        MenuItem,
        MenuItems,
        TransitionChild,
        TransitionRoot,
        ArrowDownTrayIcon,
        ArrowUpTrayIcon,
        ArrowPathIcon,
        ChevronDownIcon,
    },
    data() {
        return {
            request: request,
            dialog: {
                import: false,
                export: false,
            },
        }
    },
    methods: {
        importTranslations() {
            router.post(route('admin.translations.import'), {}, {
                preserveState: true,
                preserveScroll: true,
                onFinish: () => {
                    this.dialog.import = false;
                },
            });
        },
        exportTranslations() {
            router.post(route('admin.translations.export'), {}, {
                preserveState: true,
                preserveScroll: true,
                onFinish: () => {
                    this.dialog.export = false;
                },
            });
        },
    },
}
</script>
