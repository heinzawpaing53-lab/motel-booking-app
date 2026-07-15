/** @type {import('tailwindcss').Config} */
module.exports = {
  theme: {
    extend: {
      colors: {
        luxury: {
          50: "#FAF7F2",
          100: "#F5F1E8",
          200: "#E5D8C3",
          300: "#D2BA94",
          400: "#C8A96A",
          500: "#A87C4F",
          600: "#7A5534",
          700: "#5A3A22",
          800: "#3B2418",
          900: "#2C1810",
        },
      },
      fontFamily: {
        serif: ['"Cormorant Garamond"', '"Playfair Display"', 'Georgia', 'serif'],
        sans: ['"Inter"', '"Manrope"', 'system-ui', 'sans-serif'],
      },
    },
  },
};
