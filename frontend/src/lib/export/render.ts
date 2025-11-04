import { fabric } from 'fabric';

export async function exportHighResPNG(editCanvas: fabric.Canvas, widthIn: number, heightIn: number, ppi: number): Promise<Blob> {
  const widthPx = Math.round(widthIn * ppi);
  const heightPx = Math.round(heightIn * ppi);
  const exportCanvas = new fabric.StaticCanvas(undefined, { backgroundColor: editCanvas.backgroundColor as string });
  exportCanvas.setWidth(widthPx);
  exportCanvas.setHeight(heightPx);
  const json = editCanvas.toJSON();
  // Scale factor between edit and export
  const scaleX = widthPx / editCanvas.getWidth();
  const scaleY = heightPx / editCanvas.getHeight();
  await new Promise<void>((resolve) => {
    exportCanvas.loadFromJSON(json, () => {
      exportCanvas.getObjects().forEach((obj) => {
        obj.scaleX = (obj.scaleX ?? 1) * scaleX;
        obj.scaleY = (obj.scaleY ?? 1) * scaleY;
        obj.left = (obj.left ?? 0) * scaleX;
        obj.top = (obj.top ?? 0) * scaleY;
        if ('fontSize' in obj && typeof (obj as any).fontSize === 'number') {
          (obj as any).fontSize = (obj as any).fontSize * Math.min(scaleX, scaleY);
        }
        obj.setCoords();
      });
      exportCanvas.requestRenderAll();
      resolve();
    });
  });
  const dataURL = exportCanvas.toDataURL({ format: 'png' });
  const res = await fetch(dataURL);
  return await res.blob();
}


