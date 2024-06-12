require('./bootstrap');

import { createApp, h } from 'vue';
import { createInertiaApp, Head, Link } from '@inertiajs/inertia-vue3';
import { InertiaProgress } from '@inertiajs/progress';
import { createI18n } from 'vue-i18n/index';
import { defaultLocale, languages } from '../lang';
import VueNumberInput from '@chenfengyuan/vue-number-input';
import moment from 'moment'; // Import Moment.js

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
            .component("Link", Link)
            .component("Head", Head)
            .component(VueNumberInput.name, VueNumberInput)
            .mixin({ methods: { route } })
            .mount(el);
    },
});

InertiaProgress.init({ color: '#4B5563' });
