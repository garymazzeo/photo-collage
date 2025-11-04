import localforage from 'localforage';

export const draftsStore = localforage.createInstance({ name: 'photo-collage', storeName: 'drafts' });
export const assetsStore = localforage.createInstance({ name: 'photo-collage', storeName: 'assets' });

export type DraftProject = {
  id: string;
  title: string;
  widthPx: number;
  heightPx: number;
  canvasJSON: any;
  updatedAt: number;
  assetIds: string[];
};

export async function saveDraft(draft: DraftProject) {
  await draftsStore.setItem(draft.id, draft);
}

export async function loadDraft(id: string): Promise<DraftProject | null> {
  const d = await draftsStore.getItem<DraftProject>(id);
  return d ?? null;
}

export async function listDrafts(): Promise<DraftProject[]> {
  const all: DraftProject[] = [];
  await draftsStore.iterate<DraftProject, void>((value) => { if (value) all.push(value); });
  return all.sort((a, b) => b.updatedAt - a.updatedAt);
}

export async function putAsset(id: string, blob: Blob) { await assetsStore.setItem(id, blob); }
export async function getAsset(id: string) { return assetsStore.getItem<Blob>(id); }


