declare module 'parallax-js' {
  export interface ParallaxOptions {
    relativeInput?: boolean;
    clipRelativeInput?: boolean;
    hoverOnly?: boolean;
    inputElement?: HTMLElement | null;
    calibrateX?: boolean;
    calibrateY?: boolean;
    invertX?: boolean;
    invertY?: boolean;
    limitX?: number | false;
    limitY?: number | false;
    scalarX?: number;
    scalarY?: number;
    frictionX?: number;
    frictionY?: number;
    originX?: number;
    originY?: number;
    pointerEvents?: boolean;
    precision?: number;
  }

  export default class Parallax {
    constructor(element: HTMLElement, options?: ParallaxOptions);
    enable(): void;
    disable(): void;
    destroy(): void;
    friction(x: number, y: number): void;
    scalar(x: number, y: number): void;
    limit(x: number | false, y: number | false): void;
    origin(x: number, y: number): void;
  }
}
