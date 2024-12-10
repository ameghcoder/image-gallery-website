import tailwindcssAnimate from 'tailwindcss-animate';
import aspectRatio from '@tailwindcss/aspect-ratio';

const config = {
  darkMode: ["class"],
  content: [
    "./src/Views/**/*.{twig,php,html}",
    "./src/**/**/*.{twig,php,html}",
    "./public/assets/js/**/*.{twig,php,html}",
  ],
  theme: {
    extend: {
      transformStyle: ["responsive"],
      perspective: {
        none: "none",
        "500": "500px",
        "1000": "1000px",
        "1500": "1500px",
      },
      colors: {
        background: "hsl(var(--background))",
        foreground: "hsl(var(--foreground))",
        card: {
          DEFAULT: "hsl(var(--card))",
          foreground: "hsl(var(--card-foreground))",
        },
        popover: {
          DEFAULT: "hsl(var(--popover))",
          foreground: "hsl(var(--popover-foreground))",
        },
        primary: {
          DEFAULT: "hsl(var(--primary))",
          foreground: "hsl(var(--primary-foreground))",
        },
        secondary: {
          DEFAULT: "hsl(var(--secondary))",
          foreground: "hsl(var(--secondary-foreground))",
        },
        muted: {
          DEFAULT: "hsl(var(--muted))",
          foreground: "hsl(var(--muted-foreground))",
        },
        accent: {
          DEFAULT: "hsl(var(--accent))",
          foreground: "hsl(var(--accent-foreground))",
        },
        destructive: {
          DEFAULT: "hsl(var(--destructive))",
          foreground: "hsl(var(--destructive-foreground))",
        },
        border: "hsl(var(--border))",
        input: "hsl(var(--input))",
        ring: "hsl(var(--ring))",
        chart: {
          "1": "hsl(var(--chart-1))",
          "2": "hsl(var(--chart-2))",
          "3": "hsl(var(--chart-3))",
          "4": "hsl(var(--chart-4))",
          "5": "hsl(var(--chart-5))",
        },
      },
      borderRadius: {
        lg: "var(--radius)",
        md: "calc(var(--radius) - 2px)",
        sm: "calc(var(--radius) - 4px)",
      },
    },
  },
  plugins: [
    tailwindcssAnimate,
    aspectRatio,
    function ({ addUtilities }) {
      const newUtilities = {
        ".preserve-3d": {
          "transform-style": "preserve-3d",
        },
        ".perspective-none": {
          perspective: "none",
        },
        ".perspective-500": {
          perspective: "500px",
        },
        ".perspective-1000": {
          perspective: "1000px",
        },
        ".perspective-1500": {
          perspective: "1500px",
        },
        ".transform-perspective-1000": {
          transform: "perspective(1000px)",
        },
        ".transform-perspective-500": {
          transform: "perspective(500px)",
        },
      };
      addUtilities(newUtilities);
    },
  ],
};
export default config;