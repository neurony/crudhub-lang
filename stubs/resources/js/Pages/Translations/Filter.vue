<template>
    <Filter @apply="applyFilters" @clear="clearFilters">
        <div class="sm:col-span-6">
            <InputMultiselect
                v-model="form.groups"
                name="groups"
                label="Groups"
                placeholder="All"
                :options="$page.props.options.translation_groups"
            ></InputMultiselect>
        </div>
    </Filter>
</template>

<script>
import {
    router,
    useForm,
} from '@inertiajs/vue3';

import Filter from "crudhub/Components/Filter.vue";

import InputDate from "crudhub/Components/InputDate.vue";
import InputMultiselect from "crudhub/Components/InputMultiselect.vue";

export default {
    components: {
        Filter,
        InputDate,
        InputMultiselect,
    },
    data() {
        return {
            form: useForm({
                keyword: this.$page.props.query.keyword ?? null,
                sort_by: this.$page.props.query.sort_by ?? null,
                sort_dir: this.$page.props.query.sort_dir ?? null,
                groups: this.$page.props.query.groups ?? null,
            }),
        }
    },
    methods: {
        applyFilters() {
            this.form.keyword = this.$page.props.query.keyword ?? null;
            this.form.sort_by = this.$page.props.query.sort_by ?? null;
            this.form.sort_dir = this.$page.props.query.sort_dir ?? null;

            this.form.get(route('admin.translations.index'));
        },
        clearFilters() {
            router.get(route('admin.translations.index'));
        },
    },
}
</script>
