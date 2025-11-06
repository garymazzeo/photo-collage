import { Canvas, Image, FabricObject } from 'fabric';

export type EditorCanvas = Canvas;

export function createEditorCanvas(element: HTMLCanvasElement, width: number, height: number): EditorCanvas {
  const canvas = new Canvas(element, {
    preserveObjectStacking: true,
    backgroundColor: '#ffffff',
  });
  canvas.setWidth(width);
  canvas.setHeight(height);
  // Enable rotate/scale handles
  FabricObject.prototype.transparentCorners = false;
  FabricObject.prototype.cornerColor = '#4e7df2';
  FabricObject.prototype.cornerStyle = 'circle';
  return canvas;
}

export async function addImageFromFile(canvas: EditorCanvas, file: File) {
  const url = URL.createObjectURL(file);
  return new Promise<void>((resolve, reject) => {
    Image.fromURL(url, img => {
      img.set({ left: canvas.getWidth() / 2, top: canvas.getHeight() / 2, originX: 'center', originY: 'center' });
      canvas.add(img);
      canvas.setActiveObject(img);
      canvas.requestRenderAll();
      resolve();
    }, { crossOrigin: 'anonymous' });
  });
}


