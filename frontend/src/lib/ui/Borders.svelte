<script lang="ts">
  import '@spectrum-web-components/picker/sp-picker.js';
  import type { EditorCanvas } from '$lib/canvas/fabricSetup';
  import { Image, Rect } from 'fabric';
  export let canvas: EditorCanvas;

  // Curated list under /public/borders
  const borders = [
    { id: 'none', name: 'None', url: null as string | null },
    { id: 'classic', name: 'Classic', url: '/borders/frame-classic.svg' },
    { id: 'ornate', name: 'Ornate', url: '/borders/frame-ornate.svg' },
  ];

  let selected = 'none';
  let borderObject: Rect | Image | null = null;

  function applyBorder() {
    if (borderObject) { canvas.remove(borderObject); borderObject = null; }
    if (selected === 'none') { canvas.requestRenderAll(); return; }
    const def = borders.find(b => b.id === selected);
    if (def && def.url) {
      Image.fromURL(def.url, (img) => {
        const cw = canvas.getWidth();
        const ch = canvas.getHeight();
        img.set({ left: 0, top: 0, selectable: false, evented: false, originX: 'left', originY: 'top' });
        const scaleX = cw / (img.width || cw);
        const scaleY = ch / (img.height || ch);
        img.scaleX = scaleX;
        img.scaleY = scaleY;
        borderObject = img;
        canvas.add(img);
        img.moveTo(9999);
        canvas.requestRenderAll();
      }, { crossOrigin: 'anonymous' });
    }
  }
</script>

<div class="borders">
  <label>Border</label>
  <sp-picker label="Select border" value={selected} on:change={(e)=>{ selected=e.target.value; applyBorder(); }}>
    {#each borders as b}
      <sp-menu-item value={b.id}>{b.name}</sp-menu-item>
    {/each}
  </sp-picker>
</div>

<style>
  .borders { display: flex; align-items: center; gap: 8px; }
  label { font-size: 12px; opacity: .8; }
</style>


