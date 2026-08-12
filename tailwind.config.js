import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/**
 * Colours are declared as HSL channel triplets in resources/css/app.css and
 * consumed here through hsl(var(--token)), which is the shadcn contract. Use
 * the semantic names (bg-card, text-muted-foreground, border-border) rather
 * than raw palette steps, so a theme change is one file.
 *
 * @type {import('tailwindcss').Config}
 */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    darkMode: 'class',

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                border: 'hsl(var(--border))',
                input: 'hsl(var(--input))',
                ring: 'hsl(var(--ring))',
                background: 'hsl(var(--background))',
                foreground: 'hsl(var(--foreground))',
                primary: {
                    DEFAULT: 'hsl(var(--primary))',
                    foreground: 'hsl(var(--primary-foreground))',
                },
                secondary: {
                    DEFAULT: 'hsl(var(--secondary))',
                    foreground: 'hsl(var(--secondary-foreground))',
                },
                muted: {
                    DEFAULT: 'hsl(var(--muted))',
                    foreground: 'hsl(var(--muted-foreground))',
                },
                accent: {
                    DEFAULT: 'hsl(var(--accent))',
                    foreground: 'hsl(var(--accent-foreground))',
                },
                card: {
                    DEFAULT: 'hsl(var(--card))',
                    foreground: 'hsl(var(--card-foreground))',
                },
                popover: {
                    DEFAULT: 'hsl(var(--popover))',
                    foreground: 'hsl(var(--popover-foreground))',
                },
                success: {
                    DEFAULT: 'hsl(var(--success))',
                    foreground: 'hsl(var(--success-foreground))',
                    muted: 'hsl(var(--success-muted))',
                    'muted-foreground': 'hsl(var(--success-muted-foreground))',
                },
                warning: {
                    DEFAULT: 'hsl(var(--warning))',
                    foreground: 'hsl(var(--warning-foreground))',
                    muted: 'hsl(var(--warning-muted))',
                    'muted-foreground': 'hsl(var(--warning-muted-foreground))',
                },
                destructive: {
                    DEFAULT: 'hsl(var(--destructive))',
                    foreground: 'hsl(var(--destructive-foreground))',
                    muted: 'hsl(var(--destructive-muted))',
                    'muted-foreground': 'hsl(var(--destructive-muted-foreground))',
                },
                info: {
                    DEFAULT: 'hsl(var(--info))',
                    foreground: 'hsl(var(--info-foreground))',
                    muted: 'hsl(var(--info-muted))',
                    'muted-foreground': 'hsl(var(--info-muted-foreground))',
                },
                sidebar: {
                    DEFAULT: 'hsl(var(--sidebar))',
                    foreground: 'hsl(var(--sidebar-foreground))',
                    muted: 'hsl(var(--sidebar-muted))',
                    accent: 'hsl(var(--sidebar-accent))',
                    'accent-foreground': 'hsl(var(--sidebar-accent-foreground))',
                    border: 'hsl(var(--sidebar-border))',
                },
            },
            borderRadius: {
                lg: 'var(--radius)',
                md: 'calc(var(--radius) - 2px)',
                sm: 'calc(var(--radius) - 4px)',
            },
            boxShadow: {
                xs: '0 1px 2px 0 rgb(0 0 0 / 0.05)',
                panel: '0 1px 3px 0 rgb(0 0 0 / 0.06), 0 1px 2px -1px rgb(0 0 0 / 0.04)',
            },
            ringColor: {
                panel: 'rgb(9 9 11 / 0.05)',
            },
            keyframes: {
                'fade-in': {
                    from: { opacity: '0', transform: 'translateY(-2px)' },
                    to: { opacity: '1', transform: 'none' },
                },
            },
            animation: {
                'fade-in': 'fade-in 150ms cubic-bezier(0.4, 0, 0.2, 1)',
            },
        },
    },

    plugins: [forms],
};
