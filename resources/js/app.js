require('./bootstrap');

import { createApp, h } from 'vue';
import { createInertiaApp, Head, Link } from '@inertiajs/inertia-vue3';
import { createI18n } from 'vue-i18n';
import { defaultLocale, languages } from '../lang';
import VueNumberInput from '@chenfengyuan/vue-number-input';
import VueDatePicker from '@vuepic/vue-datepicker';
import moment from 'moment'; // Import Moment.js
import Notifications from '@kyvg/vue3-notification';
import '@vuepic/vue-datepicker/dist/main.css';
const appName = 'SLSKey';

const i18n = createI18n({
    locale: defaultLocale,
    fallbackLocale: 'en',
    messages: languages
})

const momentPlugin = {
    install(app) {
        app.config.globalProperties.$moment = moment
    }
}

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: async (name) => (await import(`./Pages/${name}.vue`)).default,
    setup({ el, app, props, plugin }) {
        const locale = props.initialPage.props.locale || defaultLocale;
        i18n.global.locale = locale;
        moment.locale(locale);

        return createApp({ render: () => h(app, props) })
            .use(plugin)
            .use(i18n)
            .use(momentPlugin)
            .use(Notifications)
            .component("Link", Link)
            .component("Head", Head)
            .component(VueNumberInput.name, VueNumberInput)
            .component(VueDatePicker.name, VueDatePicker)
            .mixin({ methods: { route } })
            .mount(el);
    },
});