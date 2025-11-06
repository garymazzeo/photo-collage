<script lang="ts">
  import '@spectrum-web-components/picker/sp-picker.js';
  import { project } from '$lib/store/project';
  import { get } from 'svelte/store';

  type Preset = { id: string; name: string; w: number; h: number };
  const presets: Preset[] = [
    { id: '13x10', name: '13×10 in', w: 13, h: 10 },
    { id: '8x10', name: '8×10 in', w: 8, h: 10 },
    { id: '11x14', name: '11×14 in', w: 11, h: 14 },
  ];
  let selected = '13x10';
  let previous = selected;
  let showConfirm = false;
  let pending: string | null = null;

  function applyPreset() {
    const p = presets.find(p => p.id === selected);
    if (!p) return;
    const cur = get(project);
    if ((cur.widthIn > p.w || cur.heightIn > p.h)) {
      // ask for confirmation
      pending = selected;
      selected = previous;
      showConfirm = true;
      return;
    }
    project.update(s => ({ ...s, widthIn: p.w, heightIn: p.h }));
    previous = selected;
  }

  function confirmDownscale() {
    if (!pending) { showConfirm = false; return; }
    const p = presets.find(x => x.id === pending);
    if (!p) { showConfirm = false; return; }
    project.update(s => ({ ...s, widthIn: p.w, heightIn: p.h }));
    selected = pending;
    previous = pending;
    pending = null;
    showConfirm = false;
  }
  function cancelDownscale() {
    pending = null;
    showConfirm = false;
  }
</script>

<div class="presets">
  <label>Size</label>
  <sp-picker label="Size preset" value={selected} on:change={(e)=> { selected=e.target.value; applyPreset(); }}>
    {#each presets as p}
      <sp-menu-item value={p.id}>{p.name}</sp-menu-item>
    {/each}
  </sp-picker>
</div>

{#if showConfirm}
  <div class="modal">
    <div class="dialog">
      <h3>Change size?</h3>
      <p>Downscaling may crop or reduce quality. Continue?</p>
      <div class="actions">
        <button on:click={cancelDownscale}>Cancel</button>
        <button on:click={confirmDownscale}>Continue</button>
      </div>
    </div>
  </div>
{/if}

<style>
  .presets { display: flex; align-items: center; gap: 8px; }
  label { font-size: 12px; opacity: .8; }
  .modal { position: fixed; inset: 0; background: rgba(0,0,0,.3); display: flex; align-items: center; justify-content: center; z-index: 1000; }
  .dialog { background: #fff; padding: 16px; border-radius: 8px; width: 320px; box-shadow: 0 6px 30px rgba(0,0,0,.2); }
  .dialog h3 { margin: 0 0 8px; font-size: 16px; }
  .dialog p { margin: 0 0 16px; font-size: 14px; }
  .actions { display: flex; justify-content: flex-end; gap: 8px; }
  .actions button { padding: 6px 12px; }
</style>



