export async function getCsrf(): Promise<string> {
  const res = await fetch('/api/auth.php?action=csrf', { credentials: 'include' });
  const data = await res.json();
  return data.token as string;
}

export async function login(email: string, password: string): Promise<boolean> {
  const token = await getCsrf();
  const res = await fetch('/api/auth.php?action=login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': token },
    credentials: 'include',
    body: JSON.stringify({ email, password }),
  });
  const data = await res.json();
  return !!data.ok;
}

export async function saveProject(p: { title: string; width_px: number; height_px: number; canvas_json: any; id?: number; }): Promise<number> {
  const token = await getCsrf();
  const isUpdate = typeof p.id === 'number';
  const res = await fetch(`/api/projects.php?action=${isUpdate ? 'update' : 'create'}`, {
    method: isUpdate ? 'PUT' : 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': token },
    credentials: 'include',
    body: JSON.stringify(p),
  });
  const data = await res.json();
  if (!data.ok) throw new Error('Save failed');
  return data.id ?? p.id ?? 0;
}


