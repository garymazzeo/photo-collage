<script lang="ts">
  import '@spectrum-web-components/picker/sp-picker.js';
  import type { EditorCanvas } from '$lib/canvas/fabricSetup';
  import { fabric } from 'fabric';
  export let canvas: EditorCanvas;

  // Minimal curated list (can be replaced with real assets under public/borders)
  const borders = [
    { id: 'none', name: 'None', url: null },
    { id: 'simple-black', name: 'Simple Black', url: null },
  ];

  let selected = 'none';
  let borderObject: fabric.Rect | fabric.Image | null = null;

  function applyBorder() {
    if (borderObject) { canvas.remove(borderObject); borderObject = null; }
    if (selected === 'none') { canvas.requestRenderAll(); return; }
    if (selected === 'simple-black') {
      const rect = new fabric.Rect({
        left: 0, top: 0,
        width: canvas.getWidth(), height: canvas.getHeight(),
        fill: 'transparent', stroke: '#000', strokeWidth: 8,
        selectable: false, evented: false
      });
      borderObject = rect;
      canvas.add(rect);
      rect.moveTo(9999); // ensure on top
      canvas.requestRenderAll();
    }
  }
</script>

<div class="borders">
  <label>Border</label>
  <sp-picker label="Select border" value={selected} on:change={(e:any)=>{ selected=e.target.value; applyBorder(); }}>
    {#each borders as b}
      <sp-menu-item value={b.id}>{b.name}</sp-menu-item>
    {/each}
  </sp-picker>
</div>

<style>
  .borders { display: flex; align-items: center; gap: 8px; }
  label { font-size: 12px; opacity: .8; }
</style>


