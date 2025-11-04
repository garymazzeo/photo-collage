import { writable } from 'svelte/store';

export type ProjectState = {
  id: string;
  title: string;
  widthIn: number;
  heightIn: number;
  ppi: number;
};

export const project = writable<ProjectState>({
  id: crypto.randomUUID(),
  title: 'Untitled',
  widthIn: 13,
  heightIn: 10,
  ppi: 300,
});


