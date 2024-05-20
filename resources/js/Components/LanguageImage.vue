<template>
    <img
        @error="$event.target.src = fallbackImage"
        :src="localeImage"
        :alt="currentLocale.toUpperCase()"
        :title="currentLocale.toUpperCase()"
        class="h5 w-5 rounded-full"
    />
</template>

<script>
export default {
    props: {
        locale: {
            type: String,
            required: false,
            default: null,
        },
    },
    computed: {
        currentLocale() {
            return this.locale && this.locale.length ? this.locale : this.$page.props.locales.current;
        },
        localeImage() {
            return `https://hatscripts.github.io/circle-flags/flags/language/${this.currentLocale}.svg`;
        },
        fallbackImage() {
            return 'https://hatscripts.github.io/circle-flags/flags/language/xx.svg';
        }
    }
}
</script>
