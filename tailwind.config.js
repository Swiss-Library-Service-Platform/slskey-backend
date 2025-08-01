const defaultTheme = require('tailwindcss/defaultTheme');
require('@inertiajs/inertia');

module.exports = {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            minWidth: {
               '0': '0',
               '1/4': '25%',
               '1/2': '50%',
               '3/4': '75%',
               'full': '100%',
            },
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
              '76': '19rem',
            }, 
            screens: {
                '3xl': '1800px',
            },
            colors: {
                // Gray for Background and Borders
                'gray-background': '#e9e9e9', // '#e9e9e991', // '#e9e9e9',
                'gray-table': '#c9c9c9', //'#e5e7eb',
                'gray-disabled': '#d1d5db',
                // Main colors
                'color-one': '#222b55',
                'color-one-1': '#edf1f5', 
                // SLSP
                'color-slsp': '#4e4a99',
                'color-slsp-bg': '#4e4a9914',
                'color-slsp-bg-lighter': '#4e4a990a',
                'color-slsp-bg-logo': '#2418fd0f',
                'color-alma': '#e2f5ff', // '#e7f4ff', // '#f0f8ff', // '#daefe9', //'#dcf1ec',
                // App layout
                'color-header-bg': '#343a40', // Test: '#700d0ecf',
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

    plugins: [
        require('@tailwindcss/forms'), 
        require('@tailwindcss/typography'),
        function ({ addBase, theme }) {
            addBase({
                // this is needed to use the colors in the css
              ':root': {
                '--color-one': theme('colors.color-one'),
                '--color-one-1': theme('colors.color-one-1'),
                '--color-slsp': theme('colors.color-slsp'),
                '--color-slsp-bg': theme('colors.color-slsp-bg'),
                '--color-alma': theme('colors.color-alma'),
                '--color-header-bg': theme('colors.color-header-bg'),
                '--color-header-text': theme('colors.color-header-text'),
                '--color-active': theme('colors.color-active'),
                '--color-deactivated': theme('colors.color-deactivated'),
                '--color-blocked': theme('colors.color-blocked'),
                '--color-active-bg': theme('colors.color-active-bg'),
                '--color-deactivated-bg': theme('colors.color-deactivated-bg'),
                '--color-blocked-bg': theme('colors.color-blocked-bg'),

              },
            });
          },
    ],
};
