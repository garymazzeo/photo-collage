declare module 'fabric' {
  export class Canvas {
    constructor(element: HTMLCanvasElement | string, options?: any);
    setWidth(width: number): void;
    setHeight(height: number): void;
    getWidth(): number;
    getHeight(): number;
    add(...objects: any[]): void;
    remove(object: any): void;
    getActiveObject(): any;
    setActiveObject(object: any): void;
    bringForward(object: any): void;
    sendBackwards(object: any): void;
    toJSON(): any;
    requestRenderAll(): void;
    loadFromJSON(json: string, callback: () => void): void;
    getObjects(): any[];
    backgroundColor: string | any;
  }

  export class StaticCanvas {
    constructor(element: HTMLCanvasElement | string | undefined, options?: any);
    setWidth(width: number): void;
    setHeight(height: number): void;
    loadFromJSON(json: string, callback: () => void): void;
    getObjects(): any[];
    toDataURL(options?: { format?: string }): string;
    requestRenderAll(): void;
    backgroundColor: string | any;
  }

  export class Image {
    static fromURL(url: string, callback: (img: Image) => void, options?: any): void;
    set(options: any): void;
    width?: number;
    height?: number;
    scaleX?: number;
    scaleY?: number;
    moveTo(index: number): void;
  }

  export class Textbox {
    constructor(text: string, options?: any);
    fontSize?: number;
    fill?: string;
    left?: number;
    top?: number;
    originX?: string;
    originY?: string;
    editable?: boolean;
  }

  export class Rect {
    constructor(options?: any);
  }

  export class FabricObject {
    transparentCorners: boolean;
    cornerColor: string;
    cornerStyle: string;
    scaleX?: number;
    scaleY?: number;
    left?: number;
    top?: number;
    fontSize?: number;
    setCoords(): void;
  }
  
  export namespace FabricObject {
    export const prototype: FabricObject;
  }
}
