import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                'primary': '#36e27b',
                'primary-dark': '#254632',
                'accent-green': '#0bda43',
                'accent-red': '#fa5538',
                'text-muted': '#95c6a9',
                'text-secondary': '#95c6a9',
                'background-light': '#f6f8f7',
                'background-dark': '#112117',
                'surface-dark': '#1a2e22',
                'surface-highlight': '#254632',
                'border-dark': '#366348',
            },
            fontFamily: {
                'display': ['Inter', 'Spline Sans', ...defaultTheme.fontFamily.sans],
                'body': ['Noto Sans', ...defaultTheme.fontFamily.sans],
                sans: ['Noto Sans', ...defaultTheme.fontFamily.sans],
            },
            borderRadius: {
                'DEFAULT': '1rem',
                '2xl': '1.5rem',
                '3xl': '2rem',
            },
        },
    },

    plugins: [forms],
};
