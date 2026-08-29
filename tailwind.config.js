import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import flowbite from 'flowbite/plugin';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './node_modules/flowbite/**/*.js',
    ],

    theme: {
        extend: {
            colors: {
                /* Tokens del design system (referencian CSS variables) */
                base: 'var(--color-bg-base)',
                page: 'var(--bg-page)',
                surface: {
                    DEFAULT: 'var(--color-bg-surface)',
                    hover: 'var(--color-bg-surface-hover)',
                },
                line: {
                    DEFAULT: 'var(--color-border)',
                    strong: 'var(--color-border-strong)',
                },
                primary: 'var(--color-text-primary)',
                secondary: 'var(--color-text-secondary)',
                muted: 'var(--text-muted)',
                warm: 'var(--color-accent-warm)',
                gold: 'var(--color-accent-gold)',
                cool: 'var(--color-accent-cool)',
                success: 'var(--color-success)',
                warning: 'var(--color-warning)',
                danger: 'var(--color-danger)',
                heading: 'var(--text-heading)',
                body: 'var(--text-body)',
                'accent-warm': 'var(--accent-warm)',
                'accent-gold': 'var(--accent-gold)',
                'accent-cool': 'var(--accent-cool)',
                neutral: {
                    primary: {
                        soft: 'var(--bg-neutral-primary-soft)',
                    },
                    secondary: {
                        medium: 'var(--bg-neutral-secondary-medium)',
                    },
                    tertiary: {
                        DEFAULT: 'var(--bg-neutral-tertiary)',
                        medium: 'var(--bg-neutral-tertiary-medium)',
                    },
                },
                'accent-gold-soft': 'var(--bg-accent-gold-soft)',
                'accent-gold-medium': 'var(--bg-accent-gold-medium)',
                /* Alias legacy → tokens (migración incremental) */
                night: {
                    bg: 'var(--color-bg-base)',
                    surface: 'var(--color-bg-surface)',
                    border: 'var(--color-border)',
                    text: 'var(--color-text-primary)',
                    secondary: 'var(--color-text-secondary)',
                    accent: 'var(--color-accent-warm)',
                    highlight: 'var(--color-accent-gold)',
                },
                ink: {
                    DEFAULT: 'var(--color-text-primary)',
                    secondary: 'var(--color-text-secondary)',
                    muted: 'var(--color-text-muted)',
                },
                brand: {
                    DEFAULT: 'var(--color-accent-warm)',
                    strong: 'var(--color-accent-warm)',
                    medium: 'rgba(240, 58, 71, 0.35)',
                },
                fg: {
                    brand: 'var(--color-accent-warm)',
                    'brand-strong': 'var(--color-accent-warm)',
                    'danger-strong': 'var(--color-danger)',
                    'success-strong': 'var(--color-success)',
                    warning: 'var(--color-warning)',
                },
            },
            backgroundImage: {
                'cta-gradient': 'var(--color-cta-gradient)',
                'hero-glow': 'radial-gradient(circle, rgba(33, 158, 188, 0.18) 0%, transparent 70%)',
            },
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
                display: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },
            maxWidth: {
                container: '1280px',
            },
            borderColor: {
                default: 'var(--border-default)',
                'default-medium': 'var(--border-default-medium)',
                'accent-gold-soft': 'var(--border-accent-gold-soft)',
            },
            borderRadius: {
                base: 'var(--rounded-base)',
                card: 'var(--rounded-base)',
                pill: '999px',
            },
            boxShadow: {
                card: '0 4px 24px rgba(2, 48, 71, 0.08)',
                'card-hover': '0 8px 32px rgba(2, 48, 71, 0.12)',
                nav: '0 1px 0 rgba(2, 48, 71, 0.08)',
                xs: 'var(--shadow-xs)',
                cta: '0 4px 20px rgba(33, 158, 188, 0.22)',
            },
            ringColor: {
                default: 'var(--border-default)',
                'neutral-tertiary': 'var(--bg-neutral-tertiary)',
            },
            fontSize: {
                h1: ['3rem', { lineHeight: '1.2', fontWeight: '700' }],
                h2: ['2rem', { lineHeight: '1.2', fontWeight: '600' }],
                h3: ['1.5rem', { lineHeight: '1.2', fontWeight: '600' }],
                caption: ['0.8125rem', { lineHeight: '1.5', fontWeight: '400' }],
            },
        },
    },

    plugins: [forms, flowbite],
};
