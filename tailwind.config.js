import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';
const colors = require('tailwindcss/colors');

/** @type {import('tailwindcss').Config} */
export default {
		    presets: [ 
        require('./vendor/tallstackui/tallstackui/tailwind.config.js') 
    ],
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
		'./app/Livewire/**/*Table.php',
		'./vendor/power-components/livewire-powergrid/resources/views/**/*.php',
		'./vendor/power-components/livewire-powergrid/src/Themes/Tailwind.php',
		'./vendor/tallstackui/tallstackui/src/**/*.php'
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
			 colors: {
        'pg-primary': colors.green,
      },
        },
    },

    plugins: [forms, typography],
};
