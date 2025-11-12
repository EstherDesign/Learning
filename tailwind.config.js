/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        hope: {
          primary: '#4A90E2',
          secondary: '#7B68EE',
          accent: '#FFB6C1',
          warm: '#FFF8DC',
          calm: '#E6F3FF',
        }
      }
    },
  },
  plugins: [],
}
