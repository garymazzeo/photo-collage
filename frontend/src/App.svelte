<script lang="ts">
  import '@spectrum-web-components/theme/sp-theme.js';
  import '@spectrum-web-components/theme/theme-light.js';
  import '@spectrum-web-components/theme/scale-medium.js';
  import '@spectrum-web-components/button/sp-button.js';
  import '@spectrum-web-components/textfield/sp-textfield.js';
  import '@spectrum-web-components/dialog/sp-dialog.js';
  import '@spectrum-web-components/progress-bar/sp-progress-bar.js';

  let ready = true;
  import Editor from '$lib/canvas/Editor.svelte';
  import { project } from '$lib/store/project';
  import { get } from 'svelte/store';
  import { login, saveProject } from '$lib/api/client';
  import '@spectrum-web-components/textfield/sp-textfield.js';
  import '@spectrum-web-components/button/sp-button.js';
  let email = '';
  let password = '';
  let loggedIn = false;
  async function doLogin() {
    loggedIn = await login(email, password);
  }
  let lastSavedId: number | null = null;
  function onCanvasJSON(e: any) {
    const p = get(project);
    const payload = { title: p.title, width_px: Math.round(p.widthIn * p.ppi), height_px: Math.round(p.heightIn * p.ppi), canvas_json: e.detail.json, id: lastSavedId ?? undefined };
    saveProject(payload).then(id => { lastSavedId = id; }).catch(() => {});
  }

  if (typeof window !== 'undefined') {
    window.addEventListener('canvas-json', onCanvasJSON);
  }
</script>

<sp-theme color="light" scale="medium">
  <div class="app-shell">
    <header class="topbar">
      <h1>Photo Collage</h1>
      <div class="actions">
        {#if !loggedIn}
          <sp-textfield placeholder="Email" type="email" value={email} on:input={(e:any)=> email=e.target.value} />
          <sp-textfield placeholder="Password" type="password" value={password} on:input={(e:any)=> password=e.target.value} />
          <sp-button variant="primary" on:click={doLogin}>Login</sp-button>
        {:else}
          <span>Signed in</span>
        {/if}
      </div>
    </header>
    <main>
      <Editor />
    </main>
  </div>
</sp-theme>

<style>
  .app-shell { display: flex; flex-direction: column; height: 100dvh; }
  .topbar { display: flex; align-items: center; justify-content: space-between; padding: 8px 12px; border-bottom: 1px solid #e5e5e5; }
  main { flex: 1; display: flex; }
  .placeholder { margin: auto; opacity: 0.7; }
  h1 { font-size: 18px; margin: 0; }
  .actions sp-button + sp-button { margin-left: 8px; }
  :global(body) { margin: 0; font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans", "Apple Color Emoji", "Segoe UI Emoji"; }
</style>


