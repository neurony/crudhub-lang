<template>
    <Form columns="6" :class="[request.ongoing ? 'opacity-50 pointer-events-none' : '']">
        <FormSection col-span="6" class="rounded-none-important">
            <div class="col-span-6 xl:col-span-1">
                <label class="text-sm font-medium leading-6 text-gray-900 mr-3 mt-2">
                    Default language
                </label>
                <InputSelect
                    v-model="languages.default"
                    :options="$page.props.options.languages"
                    track-by="name"
                    value-prop="id"
                    name="default_language"
                />
            </div>
            <div class="col-span-6 xl:col-span-5">
                <label class="text-sm font-medium leading-6 text-gray-900 mr-3 mt-2">
                    Active languages
                </label>
                <div class="xl:flex">
                    <InputMultiselect
                        v-model="languages.active"
                        :options="$page.props.options.languages"
                        track-by="name"
                        value-prop="id"
                        name="active_languages"
                        class="flex-grow xl:mr-6"
                    />
                    <div class="mt-6 xl:mt-auto">
                        <button @click.prevent="saveLanguages()" type="button" :disabled="request.ongoing" :class="[request.ongoing ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer']" class="transition-all inline-flex items-center rounded-md bg-white px-4 py-2.5 text-sm font-semibold text-gray-600 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                            <CheckIcon class="-ml-0.5 mr-2 h-5 w-5" aria-hidden="true" />
                            Save
                        </button>
                    </div>
                </div>
            </div>
        </FormSection>
    </Form>
</template>

<script>
import {
    CheckIcon,
} from '@heroicons/vue/20/solid';

import {
    find,
    map,
    filter,
} from 'lodash';

import {
    useForm
} from '@inertiajs/vue3';

import {
    request
} from "crudhub/constants.js";

import ButtonSaveStay from "crudhub/Components/ButtonSaveStay.vue";
import Form from "crudhub/Components/Form.vue";
import FormSection from "crudhub/Components/FormSection.vue";
import InputMultiselect from "crudhub/Components/InputMultiselect.vue";
import InputSelect from "crudhub/Components/InputSelect.vue";

export default {
    components: {
        ButtonSaveStay,
        CheckIcon,
        Form,
        FormSection,
        InputMultiselect,
        InputSelect,
    },
    data() {
        return {
            request: request,
            languages: {
                default: this.getDefaultLanguage(),
                active: this.getActiveLanguages(),
            },
        }
    },
    methods: {
        find,
        map,
        filter,
        getDefaultLanguage() {
            const languages = this.$page?.props?.options?.languages ?? [];

            return this.find(languages, { default: true })?.id ?? null;
        },
        getActiveLanguages() {
            const languages = this.$page?.props?.options?.languages ?? [];

            return this.map(this.filter(languages, { active: true }), 'id');
        },
        saveLanguages() {
            let form = useForm({
                default_language: this.languages.default,
                active_languages: this.languages.active,
            })

            form.post(route('admin.languages.save'), {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    this.languages.default = this.getDefaultLanguage();
                    this.languages.active = this.getActiveLanguages();
                }
            });
        },
    },
    watch: {
        'languages.default': function(newVal, oldVal) {
            if (oldVal && this.languages.active.includes(oldVal)) {
                this.languages.active = this.languages.active.filter(item => item !== oldVal);
            }

            if (newVal && !this.languages.active.includes(newVal)) {
                this.languages.active.push(newVal);
            }
        },
    }
}
</script>

<style scoped>
.rounded-none-important {
    border-radius: 0 !important;
}
</style>