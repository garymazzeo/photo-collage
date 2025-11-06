import { defineConfig } from 'vite';
import { svelte } from '@sveltejs/vite-plugin-svelte';
import { vitePreprocess } from '@sveltejs/vite-plugin-svelte';
import { resolve } from 'node:path';

export default defineConfig({
  plugins: [svelte({
    preprocess: vitePreprocess(),
  })],
  root: '.',
  build: {
    outDir: resolve(__dirname, '../public/assets'),
    emptyOutDir: true,
    sourcemap: false,
  },
  base: '/assets/',
  server: {
    port: 5173,
    strictPort: true,
  },
});


