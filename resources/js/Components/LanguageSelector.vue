<template>
    <div :class="[request.ongoing ? 'opacity-50': '']" class="mb-8 rounded-md bg-white shadow xl:col-span-2">
        <div class="bg-white px-4 py-5 sm:p-6 rounded-t-md  rounded-b-md">
            <div class="-my-1 flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <div v-for="activeLocale in activeLocales" class="relative h-full flex flex-col-reverse flex-1">
                        <div class="flex items-center h-full" aria-expanded="false">
                            <button
                                @click.prevent="switchLanguage(activeLocale)"
                                :class="[activeLocale === locale ? 'bg-gray-100 text-indigo-600' : '']"
                                class="transition-all rounded-md flex py-1.5 px-3 items-center text-sm uppercase leading-5 hover:cursor-pointer text-gray-500 hover:bg-gray-100"
                            >
                                <span class="flex items-center gap-2">
                                    <LanguageImage :locale="activeLocale" /> {{ activeLocale }}
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import {
    request
} from "crudhub/constants.js";

import LanguageImage from "./LanguageImage.vue";
import SwitchesLocale from "../Mixins/SwitchesLocale.js";

export default {
    props: {
        locale: {
            type: String,
            required: true,
        },
    },
    emits: [
        'language-selected',
    ],
    mixins: [
        SwitchesLocale,
    ],
    components: {
        LanguageImage,
    },
    data() {
        return {
            request: request
        }
    },
    methods: {
        switchLanguage(locale) {
            this.$emit('language-selected', locale);
        }
    },
}
</script>
