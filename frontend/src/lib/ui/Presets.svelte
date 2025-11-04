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

  function applyPreset() {
    const p = presets.find(p => p.id === selected);
    if (!p) return;
    const cur = get(project);
    if ((cur.widthIn > p.w || cur.heightIn > p.h)) {
      // In a real app: show a warning dialog; for now, proceed
    }
    project.update(s => ({ ...s, widthIn: p.w, heightIn: p.h }));
  }
</script>

<div class="presets">
  <label>Size</label>
  <sp-picker label="Size preset" value={selected} on:change={(e:any)=> { selected=e.target.value; applyPreset(); }}>
    {#each presets as p}
      <sp-menu-item value={p.id}>{p.name}</sp-menu-item>
    {/each}
  </sp-picker>
</div>

<style>
  .presets { display: flex; align-items: center; gap: 8px; }
  label { font-size: 12px; opacity: .8; }
</style>


