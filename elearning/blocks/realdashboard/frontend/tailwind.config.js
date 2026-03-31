/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ['./src/**/*.{ts,js}', './src/**/*.{js,ts,jsx,tsx,html}'],
  theme: {
    extend: {
      // Custom colors
      colors: {
        primary: {
          DEFAULT: '#1D4ED8', // Blue
          light: '#60A5FA',
          dark: '#1E40AF',
        },
        secondary: {
          DEFAULT: '#047857', // Green
          light: '#34D399',
          dark: '#065F46',
        },
      },
      // Custom font families
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui', '-apple-system', 'Arial', 'sans-serif'],
        serif: ['Merriweather', 'ui-serif', 'Georgia', 'serif'],
        mono: ['Fira Code', 'ui-monospace', 'monospace'],
      },
      // Custom spacing, breakpoints, or other theme properties
      spacing: {
        '128': '32rem',
        '144': '36rem',
      },
      screens: {
        'xs': '480px',
        'sm': '640px',
        'md': '768px',
        'lg': '1024px',
        'xl': '1280px',
        '2xl': '1536px',
        '3xl': '1600px',
      },
    },
  },
  plugins: [],
};