<script lang="ts">
  import '@spectrum-web-components/textfield/sp-textfield.js';
  import '@spectrum-web-components/number-field/sp-number-field.js';
  import '@spectrum-web-components/color-area/sp-color-area.js';
  import '@spectrum-web-components/button/sp-button.js';
  import type { EditorCanvas } from '$lib/canvas/fabricSetup';
  import { fabric } from 'fabric';

  export let canvas: EditorCanvas;
  let text = 'Text';
  let size = 36;
  let color = '#000000';

  function addText() {
    const t = new fabric.Textbox(text, {
      fontSize: size,
      fill: color,
      left: canvas.getWidth() / 2,
      top: canvas.getHeight() / 2,
      originX: 'center',
      originY: 'center',
      editable: true,
    });
    canvas.add(t);
    canvas.setActiveObject(t);
    canvas.requestRenderAll();
  }
</script>

<div class="text-tool">
  <sp-textfield value={text} on:input={(e:any)=> text=e.target.value} placeholder="Add text"></sp-textfield>
  <sp-number-field min="6" max="200" value={size} on:input={(e:any)=> size=parseInt(e.target.value)}></sp-number-field>
  <input type="color" value={color} on:input={(e:any)=> color=e.target.value} />
  <sp-button variant="primary" on:click={addText}>Add Text</sp-button>
</div>

<style>
  .text-tool { display: flex; align-items: center; gap: 8px; }
  .text-tool input[type="color"] { height: 32px; width: 32px; padding: 0; border: none; background: none; }
  sp-textfield { min-width: 200px; }
  sp-number-field { width: 100px; }
  sp-button { white-space: nowrap; }
</style>


