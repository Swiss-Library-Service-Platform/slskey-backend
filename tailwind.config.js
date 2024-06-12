const defaultTheme = require('tailwindcss/defaultTheme');
require('@inertiajs/inertia');

module.exports = {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Source Sans Pro', ...defaultTheme.fontFamily.sans],
            },
            margin: {
                '3px': '3px',
            },
            height: {
                '18px': '18px',
            },
            width: { 
              //  'screen-3xl': '1800px',
              '18px': '18px',
              '32rem': '32rem',
              '36rem': '36rem',
            }, 
            screens: {
                '3xl': '1800px',
            },
            colors: {
                // Gray for Background and Borders
                'gray-background': '#e9e9e9',
                'gray-table': '#c9c9c9', //'#e5e7eb',
                'gray-disabled': '#d1d5db',
                // Main colors
                'color-one': '#222b55',
                'color-one-1': '#edf1f5', 
                // SLSP
                'color-slsp': '#4e4a99',
                'color-slsp-light': '#e5e5f4',
                'color-slsp-bg': '#4e4a9914',
                'color-alma': '#e7f4ff', // '#f0f8ff', // '#daefe9', //'#dcf1ec',
                // App layout
                'color-header-bg': '#343a40',
                'color-header-text': '#fff',
                // User Status - Font
                'color-active': '#047d04',
                'color-deactivated': '#424141',
                'color-blocked': '#9c3a3a',
                // User Status - Background Color
                'color-active-bg': '#eefbee',
                'color-deactivated-bg' : '#ececec',
                'color-blocked-bg': '#fbeef0',
            }
        },
    },

    plugins: [require('@tailwindcss/forms'), require('@tailwindcss/typography')],
};
