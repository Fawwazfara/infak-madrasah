/** @type {import('tailwindcss').Config} */
export default {
  future: {
    hoverOnlyWhenSupported: true,
  },
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        canvas: {
          DEFAULT: "#F5F3EE",
          muted: "#ECE8DF",
          subtle: "#E2DDD2",
        },
        surface: {
          DEFAULT: "#FDFCF9",
          elevated: "#FDFCF9",
          inset: "#F7F4ED",
        },
        ink: {
          DEFAULT: "#1C1917",
          secondary: "#44403C",
          muted: "#78716C",
          faint: "#A8A29E",
        },
        charcoal: {
          DEFAULT: "#292524",
          deep: "#1C1917",
          soft: "#F2EFE8",
        },
        primary: {
          DEFAULT: "#A8551E",
          hover: "#8D4415",
          soft: "#F9EFE7",
        },
        bronze: {
          DEFAULT: "#A8551E",
          soft: "#F9EFE7",
          border: "#E9C4A7",
        },
        ochre: {
          DEFAULT: "#9A6B16",
          soft: "#FAF4E4",
        },
        crimson: {
          DEFAULT: "#9F1239",
          soft: "#FDF2F4",
        },
        border: {
          DEFAULT: "#E6E1D6",
          strong: "#D6D0C4",
        },
      },
      fontFamily: {
        sans: ['"Onest"', 'ui-sans-serif', 'system-ui', '-apple-system', 'sans-serif'],
        mono: ['"JetBrains Mono"', 'ui-monospace', 'SFMono-Regular', 'monospace'],
      },
      transitionTimingFunction: {
        'out-expo': 'cubic-bezier(0.16, 1, 0.3, 1)',
        'out-ui': 'cubic-bezier(0.2, 0, 0, 1)',
      },
    },
  },
  plugins: [],
};
