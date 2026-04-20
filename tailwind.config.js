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
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                canvas: '#faf9f6',
                'off-black': '#111111',
                'fin-orange': {
                    DEFAULT: '#ff5600',
                    hover: '#e64d00',
                    light: '#fff0e6',
                },
                oat: '#dedbd6',
                muted: '#7b7b78',
                surface: '#ffffff',
                'warm-sand': '#d3cec6',
                report: {
                    blue: '#65b5ff',
                    green: '#0bdf50',
                    red: '#c41c1c',
                    pink: '#ff2067',
                    lime: '#b3e01c',
                    orange: '#fe4c02',
                },
            },
            borderRadius: {
                btn: '4px',
                card: '8px',
            },
            letterSpacing: {
                'display': '-0.03em',
                'heading': '-0.02em',
                'sub': '-0.015em',
                'card-title': '-0.01em',
            },
            scale: {
                '85': '0.85',
                '110': '1.1',
            },
        },
    },

    plugins: [forms],
};
