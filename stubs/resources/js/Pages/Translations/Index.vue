<template>
    <PageHeader title="Translations" subtitle="Manage your translations">
        <Actions />
    </PageHeader>

    <Languages />

    <PageContent>
        <div class="overflow-hidden rounded-md shadow ring-1 ring-gray-600/5 ring-opacity-5">
            <Filter />

            <div class="overflow-x-auto">
                <table :class="[request.ongoing ? 'opacity-50 pointer-events-none' : '']" class="table-fixed min-w-full divide-y divide-gray-200 transition-opacity">
                    <thead class="bg-gray-50">
                    <tr v-if="!isEmpty(data.items)" class="py-4">
                        <TableTh :sticky-start="true">
                            Translation
                        </TableTh>
                        <TableTh v-for="locale in $page.props.locales.active">
                            <div class="flex items-center">
                                <LanguageImage :locale="locale" class="h-6 w-6" />
                                <span v-if="locale && locale.length" class="ml-2 text-xs text-gray-400">
                                    {{ locale.toUpperCase() }}
                                </span>
                            </div>
                        </TableTh>
                    </tr>
                    <tr v-else class="py-4">
                        <TableTh colspan="50" class="py-[26px]"></TableTh>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                    <tr v-if="!isEmpty(data.items)" v-for="(items, key) in data.items" :key="key">
                        <TableTd :sticky-start="true">
                            <div class="flex items-center">
                                <div>
                                    <div class="font-medium text-gray-900">{{ getTranslationKey(key) }}</div>
                                    <div class="text-gray-500">{{ getTranslationGroup(key) }}</div>
                                </div>
                            </div>
                        </TableTd>
                        <TableTd v-for="(item, locale) in items">
                            <div class="bg-gray-100 rounded-lg min-w-96 flex items-center justify-between p-4">
                                <div class="flex-1">
                                    {{ getTranslationValue(item.value) }}
                                </div>
                                <button @click.prevent="showEdit(item)" type="button" class="ml-2 text-indigo-600 text-sm cursor-pointer hover:text-indigo-500">
                                    <PencilSquareIcon class="h-5 w-5" aria-hidden="true" />
                                </button>
                            </div>
                        </TableTd>
                    </tr>
                    <tr v-else>
                        <TableTd colspan="50">
                                <span class="text-gray-500">
                                    No records found
                                </span>
                        </TableTd>
                    </tr>
                    </tbody>
                </table>
            </div>

            <Pagination
                v-if="!isEmpty(data.items) && !isEmpty(data.paginator)"
                :per-page="data.paginator.per_page"
                :total-records="data.paginator.total"
                :from-record="data.paginator.from"
                :to-record="data.paginator.to"
                :page-links="data.paginator.links"
            />
        </div>
    </PageContent>

    <TransitionRoot as="template" :show="edit.modal.show">
        <Dialog @close="hideEdit()" as="div" class="relative z-50">
            <TransitionChild as="div" enter="ease-in duration-200" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in transition-opacity duration-100" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 opacity-50 transition-opacity bg-gray-500" />
            </TransitionChild>

            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <TransitionChild as="template" enter="ease-out duration-100" enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" enter-to="opacity-75 translate-y-0 sm:scale-100" leave="ease-in duration-200" leave-from="opacity-75 translate-y-0 sm:scale-100" leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                        <DialogPanel class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl">
                            <div class="bg-white mb-2 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                <div class="flex justify-between">
                                    <div class="flex-shrink-0 mr-4">
                                        <LanguageImage :locale="edit.form.locale" class="h-9 w-9" />
                                    </div>
                                    <div class="ml-auto text-left flex-grow">
                                        <InputTextarea
                                            v-model="edit.form.value"
                                            v-model:error="errors.value"
                                            @input-changed="errors.value = null"
                                            :rows="8"
                                            name="value" />
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                <button @click.prevent="editTranslation()" type="button" :disabled="request.ongoing" :class="[request.ongoing ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer']" class="inline-flex w-full justify-center rounded-md text-white bg-indigo-600 hover:bg-indigo-500 px-3 py-2 text-sm font-semibold shadow-sm sm:ml-3 sm:w-auto">
                                    <ArrowPathIcon v-if="request.ongoing" class="-ml-0.5 mr-2.5 h-5 w-5 animate animate-spin" aria-hidden="true" />
                                    <ArrowDownTrayIcon v-else class="-ml-0.5 mr-2.5 h-5 w-5" aria-hidden="true" />
                                    Save
                                </button>
                                <button @click="hideEdit()" type="button" :disabled="request.ongoing" :class="[request.ongoing ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer']" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-gray-900 hover:bg-gray-50 text-sm font-semibold shadow-sm ring-1 ring-inset ring-gray-300 sm:mt-0 sm:w-auto">
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
    isEmpty,
    truncate,
    pick,
} from 'lodash';

import {
    useForm
} from '@inertiajs/vue3';

import {
    ArrowDownTrayIcon,
    ArrowPathIcon,
    PencilSquareIcon,
} from '@heroicons/vue/20/solid';

import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionChild,
    TransitionRoot,
} from '@headlessui/vue';

import {
    request
} from "crudhub/constants.js";

import Actions from "./Actions.vue";
import Filter from "./Filter.vue";
import InputTextarea from "crudhub/Components/InputTextarea.vue";
import LanguageImage from "crudhub-lang/Components/LanguageImage.vue";
import Languages from "../Languages/Save.vue";
import PageContent from "crudhub/Components/PageContent.vue";
import PageHeader from "crudhub/Components/PageHeader.vue";
import Pagination from "crudhub/Components/Pagination.vue";
import TableTd from "crudhub/Components/TableTd.vue";
import TableTh from "crudhub/Components/TableTh.vue";

export default {
    props: {
        query: Object,
        data: Object,
        errors: Object,
    },
    components: {
        ArrowDownTrayIcon,
        ArrowPathIcon,
        PencilSquareIcon,
        Dialog,
        DialogPanel,
        DialogTitle,
        TransitionChild,
        TransitionRoot,
        Actions,
        Filter,
        InputTextarea,
        LanguageImage,
        Languages,
        PageContent,
        PageHeader,
        Pagination,
        TableTd,
        TableTh,
    },
    data() {
        return {
            request: request,
            edit: {
                modal: {
                    show: false,
                },
                form: {
                    id: null,
                    value: null,
                    locale: null,
                },
            },
        }
    },
    methods: {
        isEmpty,
        truncate,
        pick,
        getTranslationKey(text) {
            let str = text.includes('.') ? text.split('.').slice(1).join('.') : text;

            return truncate(str, {
                'length': 25,
                'omission': '...',
            });
        },
        getTranslationGroup(text) {
            let str = text.includes('.') ? text.split('.')[0] : text;

            return truncate(str, {
                'length': 25,
                'omission': '...',
            });
        },
        getTranslationValue(text) {
            return truncate(text, {
                'length': 45,
                'omission': '...',
            });
        },
        editTranslation() {
            let form = useForm(this.pick(this.edit.form, [
                'value',
            ]));

            form.put(route('admin.translations.update', this.edit.form.id), {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    this.hideEdit();
                },
            });
        },
        showEdit(item) {
            this.edit = {
                modal: {
                    show: true,
                },
                form: {
                    id: item.id,
                    value: item.value,
                    locale: item.locale,
                },
            };
        },
        hideEdit(item) {
            this.edit = {
                modal: {
                    show: false,
                },
                form: {
                    id: null,
                    value: null,
                    locale: null,
                },
            };
        },
    },
}
</script>
