export default {
    data() {
        return {
            defaultLocale: this.$page?.props?.locales?.default ?? null,
            currentLocale: this.$page?.props?.locales?.current ?? null,
            activeLocales: this.$page?.props?.locales?.active ?? [],
        }
    },
    methods: {
        switchLocale(locale) {
            this.currentLocale = locale;
        }
    },
}
