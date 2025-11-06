<script lang="ts">
  import { onMount } from "svelte"
  import { createEditorCanvas, addImageFromFile } from "./fabricSetup"
  import "@spectrum-web-components/button/sp-button.js"
  import "@spectrum-web-components/action-group/sp-action-group.js"
  import "@spectrum-web-components/action-button/sp-action-button.js"
  import TextTool from "$lib/ui/TextTool.svelte"
  import Borders from "$lib/ui/Borders.svelte"
  import Presets from "$lib/ui/Presets.svelte"
  import { project } from "$lib/store/project"
  import { saveDraft } from "$lib/local/db"
  import { get } from "svelte/store"
  import { exportHighResPNG } from "$lib/export/render"

  let hostEl: HTMLDivElement
  let canvasEl: HTMLCanvasElement
  let canvas: ReturnType<typeof createEditorCanvas>
  let width = 1200 // viewport canvas for editing (not export size)
  let height = 800

  function fitCanvas() {
    const rect = hostEl.getBoundingClientRect()
    const pad = 16
    width = Math.max(300, rect.width - pad * 2)
    height = Math.max(300, rect.height - 80)
    if (canvas) {
      canvas.setWidth(width)
      canvas.setHeight(height)
      canvas.requestRenderAll()
    }
  }

  onMount(() => {
    canvas = createEditorCanvas(canvasEl, width, height)
    fitCanvas()
    const obs = new ResizeObserver(() => fitCanvas())
    obs.observe(hostEl)
    const interval = setInterval(async () => {
      const p = get(project)
      const json = canvas.toJSON()
      await saveDraft({
        id: p.id,
        title: p.title,
        widthPx: Math.round(p.widthIn * p.ppi),
        heightPx: Math.round(p.heightIn * p.ppi),
        canvasJSON: json,
        updatedAt: Date.now(),
        assetIds: [],
      })
    }, 5000)
    return () => obs.disconnect()
  })

  async function onFilesChange(event: Event) {
    const input = event.target as HTMLInputElement
    const files = input.files
    if (!files) return
    for (const f of Array.from(files)) {
      await addImageFromFile(canvas, f)
    }
    ;(event.target as HTMLInputElement).value = ""
  }

  function bringForward() {
    canvas.bringForward(canvas.getActiveObject() as any)
    canvas.requestRenderAll()
  }
  function sendBackwards() {
    canvas.sendBackwards(canvas.getActiveObject() as any)
    canvas.requestRenderAll()
  }
  function deleteActive() {
    const o = canvas.getActiveObject()
    if (o) {
      canvas.remove(o)
      canvas.requestRenderAll()
    }
  }

  async function exportPng() {
    const p = get(project)
    const blob = await exportHighResPNG(canvas, p.widthIn, p.heightIn, p.ppi)
    const a = document.createElement("a")
    a.href = URL.createObjectURL(blob)
    a.download = `${p.title || "collage"}-${p.widthIn}x${p.heightIn}-${p.ppi}ppi.png`
    a.click()
    URL.revokeObjectURL(a.href)
  }

  function emitCanvasJSON() {
    const detail = { json: canvas.toJSON() }
    const ev = new CustomEvent("canvas-json", { detail })
    window.dispatchEvent(ev)
  }
</script>

<div class="editor" bind:this={hostEl}>
  <div class="toolbar">
    <input
      id="fileinput"
      type="file"
      accept="image/*"
      multiple
      on:change={onFilesChange}
    />
    <sp-action-group>
      <sp-action-button on:click={bringForward}>Bring forward</sp-action-button>
      <sp-action-button on:click={sendBackwards}>Send backward</sp-action-button
      >
      <sp-action-button on:click={deleteActive}>Delete</sp-action-button>
    </sp-action-group>
    <div class="sep"></div>
    <TextTool {canvas} />
    <div class="sep"></div>
    <Borders {canvas} />
    <div class="sep"></div>
    <Presets />
  </div>
  <div class="stage">
    <canvas bind:this={canvasEl}></canvas>
  </div>
  <div class="status">
    <sp-button on:click={emitCanvasJSON}>Save to Server</sp-button>
    <sp-button variant="primary" on:click={exportPng}
      >Export PNG (300 ppi)</sp-button
    >
  </div>
</div>

<style>
  .editor {
    display: grid;
    grid-template-rows: auto 1fr auto;
    height: 100%;
  }
  .toolbar {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px;
    border-bottom: 1px solid #e5e5e5;
  }
  .toolbar .sep {
    width: 1px;
    height: 28px;
    background: #e5e5e5;
  }
  .stage {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 8px;
    overflow: auto;
  }
  canvas {
    background: #fff;
    box-shadow: 0 0 0 1px #e5e5e5;
  }
</style>
