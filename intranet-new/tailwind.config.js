import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Calibri', 'Candara', 'Segoe', 'Segoe UI', 'Optima', 'Arial', ...defaultTheme.fontFamily.sans],
                heading: ['Arial', 'Helvetica', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                blue: {
                    50: '#EBF1FF',
                    100: '#D6E4FF',
                    200: '#ADC8FF',
                    300: '#85ADFF',
                    400: '#5C91FF',
                    500: '#0066FF',
                    600: '#0052CC',
                    700: '#003D99',
                    800: '#002966',
                    900: '#001433',
                    950: '#000A1A',
                },
                indigo: {
                    50: '#EBF1FF',
                    100: '#D6E4FF',
                    200: '#ADC8FF',
                    300: '#85ADFF',
                    400: '#5C91FF',
                    500: '#0066FF',
                    600: '#0052CC',
                    700: '#003D99',
                    800: '#002966',
                    900: '#001433',
                    950: '#000A1A',
                },
                orange: {
                    50: '#FFF7E6',
                    100: '#FFEFCC',
                    200: '#FFDF99',
                    300: '#FFCF66',
                    400: '#FFBF33',
                    500: '#FFB000',
                    600: '#F4A000',
                    700: '#C28000',
                    800: '#916000',
                    900: '#614000',
                    950: '#302000',
                },
            },
        },
    },

    plugins: [forms],
};
