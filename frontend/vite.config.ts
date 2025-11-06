import { defineConfig } from 'vite';
import { svelte } from '@sveltejs/vite-plugin-svelte';
import { vitePreprocess } from '@sveltejs/vite-plugin-svelte';
import { resolve } from 'node:path';

export default defineConfig({
  plugins: [svelte({
    preprocess: vitePreprocess(),
  })],
  resolve: {
    alias: {
      '$lib': resolve(__dirname, './src/lib'),
    },
  },
  root: '.',
  build: {
    outDir: resolve(__dirname, '../public/assets'),
    emptyOutDir: true,
    sourcemap: false,
    rollupOptions: {
      output: {
        manualChunks: undefined,
      },
    },
  },
  base: '/assets/',
  server: {
    port: 5173,
    strictPort: true,
  },
  optimizeDeps: {
    include: [
      '@spectrum-web-components/button',
      '@spectrum-web-components/textfield',
      '@spectrum-web-components/dialog',
      '@spectrum-web-components/progress-bar',
      '@spectrum-web-components/toast',
      '@spectrum-web-components/number-field',
      '@spectrum-web-components/color-area',
      '@spectrum-web-components/picker',
      '@spectrum-web-components/alert-dialog',
      '@spectrum-web-components/action-group',
      '@spectrum-web-components/action-button',
      '@spectrum-web-components/theme',
    ],
  },
});


