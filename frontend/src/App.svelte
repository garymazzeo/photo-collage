<script lang="ts">
  import '@spectrum-web-components/theme/sp-theme.js';
  import '@spectrum-web-components/theme/theme-light.js';
  import '@spectrum-web-components/theme/scale-medium.js';
  import '@spectrum-web-components/button/sp-button.js';
  import '@spectrum-web-components/textfield/sp-textfield.js';
  import '@spectrum-web-components/dialog/sp-dialog.js';
  import '@spectrum-web-components/progress-bar/sp-progress-bar.js';
  import '@spectrum-web-components/toast/sp-toast.js';

  let ready = true;
  import Editor from '$lib/canvas/Editor.svelte';
  import { project } from '$lib/store/project';
  import { get } from 'svelte/store';
  import { login, register, logout, saveProject, me } from '$lib/api/client';
  import '@spectrum-web-components/textfield/sp-textfield.js';
  import '@spectrum-web-components/button/sp-button.js';
  import UserPage from '$lib/ui/UserPage.svelte';
  let email = '';
  let password = '';
  let loggedIn = false;
  let userEmail: string | null = null;
  let userName: string | null = null;
  let view: 'editor' | 'account' = 'editor';
  let mode: 'login' | 'register' = 'login';
  let regName = '';
  let showForgot = false;
  let forgotEmail = '';
  let showReset = false;
  let resetToken = '';
  let resetNew = '';
  async function doLogin() {
    loggedIn = await login(email, password);
    if (loggedIn) {
      const info = await me();
      userEmail = info?.email ?? null;
      userName = (info as any)?.name || null;
    }
  }
  async function doRegister() {
    const ok = await register(email, password, regName as any);
    if (ok) {
      loggedIn = await login(email, password);
      const info = await me();
      userEmail = info?.email ?? null;
      userName = (info as any)?.name || null;
    }
  }
  async function doLogout() {
    await logout();
    loggedIn = false;
    userEmail = null;
    userName = null;
  }

  // Forgot/reset password
  import { forgotPassword, resetPassword } from '$lib/api/client';
  let devToken: string | null = null;
  let forgotEmailError = '';
  let resetTokenError = '';
  let resetNewError = '';
  let toastMessage = '';
  let toastVariant: 'positive' | 'negative' = 'positive';
  let showToast = false;
  
  function showMessage(text: string, variant: 'positive' | 'negative' = 'positive') {
    toastMessage = text;
    toastVariant = variant;
    showToast = true;
    setTimeout(() => { showToast = false; }, 4000);
  }
  
  async function requestReset() {
    forgotEmailError = '';
    if (!forgotEmail || !forgotEmail.includes('@')) {
      forgotEmailError = 'Please enter a valid email address';
      showMessage('Please enter a valid email address', 'negative');
      return;
    }
    try {
      const res = await forgotPassword(forgotEmail);
      if ((res as any).ok) {
        devToken = (res as any).token || null;
        showForgot = false;
        showReset = true;
        showMessage('Reset email sent! Check your inbox.', 'positive');
      } else {
        showMessage('Failed to send reset email. Please try again.', 'negative');
      }
    } catch (e: any) {
      showMessage('Error: ' + (e.message || 'Failed to send email'), 'negative');
    }
  }
  
  async function doReset() {
    resetTokenError = '';
    resetNewError = '';
    
    if (!resetToken || resetToken.length < 10) {
      resetTokenError = 'Invalid reset token';
      showMessage('Please enter a valid reset token', 'negative');
      return;
    }
    
    if (!resetNew || resetNew.length < 8) {
      resetNewError = 'Password must be at least 8 characters';
      showMessage('Password must be at least 8 characters', 'negative');
      return;
    }
    
    try {
      const ok = await resetPassword(resetToken, resetNew);
      if (ok) {
        showMessage('Password reset successfully! You can now log in.', 'positive');
        showReset = false;
        mode = 'login';
        resetToken = resetNew = '';
      } else {
        showMessage('Invalid or expired reset token', 'negative');
      }
    } catch (e: any) {
      showMessage('Error: ' + (e.message || 'Failed to reset password'), 'negative');
    }
  }

  // On load, check session
  if (typeof window !== 'undefined') {
    me().then(info => { if (info) { loggedIn = true; userEmail = info.email; userName = (info as any).name || null; } });
  }
  let lastSavedId: number | null = null;
  function onCanvasJSON(e: any) {
    const p = get(project);
    const payload = { title: p.title, width_px: Math.round(p.widthIn * p.ppi), height_px: Math.round(p.heightIn * p.ppi), canvas_json: e.detail.json, id: lastSavedId ?? undefined };
    saveProject(payload).then(id => { lastSavedId = id; }).catch(() => {});
  }

  if (typeof window !== 'undefined') {
    window.addEventListener('canvas-json', onCanvasJSON);
  }
</script>

<sp-theme color="light" scale="medium">
  <div class="app-shell">
    <header class="topbar">
      <h1>Photo Collage</h1>
      <div class="actions">
        {#if !loggedIn}
          <sp-textfield placeholder="Email" type="email" value={email} on:input={(e:any)=> email=e.target.value} />
          <sp-textfield placeholder="Password" type="password" value={password} on:input={(e:any)=> password=e.target.value} />
          {#if mode === 'login'}
            <sp-button variant="primary" on:click={doLogin}>Login</sp-button>
            <sp-button on:click={()=> mode='register'}>Register</sp-button>
            <sp-button on:click={()=> { showForgot = true; forgotEmail = email; }}>Forgot password?</sp-button>
          {:else}
            <sp-textfield placeholder="Name (optional)" value={regName} on:input={(e:any)=> regName=e.target.value} />
            <sp-button variant="primary" on:click={doRegister}>Create Account</sp-button>
            <sp-button on:click={()=> mode='login'}>Back to Login</sp-button>
          {/if}
        {:else}
          <span class="chip" on:click={()=> view = 'account'}>{userName || userEmail}</span>
          <sp-button on:click={doLogout}>Logout</sp-button>
        {/if}
      </div>
    </header>
    <main>
      {#if view === 'editor'}
        <Editor />
      {:else}
        <UserPage />
      {/if}
    </main>

    {#if showForgot}
      <div class="modal"><div class="dialog">
        <h3>Forgot password</h3>
        <p>Enter your account email to receive a reset link.</p>
        <sp-textfield 
          placeholder="Email" 
          type="email" 
          value={forgotEmail} 
          on:input={(e:any)=> { forgotEmail=e.target.value; forgotEmailError = ''; }}
          invalid={forgotEmailError !== ''}
          error-message={forgotEmailError || undefined}
        />
        <div class="actions">
          <sp-button on:click={()=> { showForgot=false; forgotEmailError = ''; }}>Cancel</sp-button>
          <sp-button variant="primary" on:click={requestReset}>Send reset</sp-button>
        </div>
      </div></div>
    {/if}

    {#if showReset}
      <div class="modal"><div class="dialog">
        <h3>Reset password</h3>
        {#if devToken}<p style="font-size: 12px; color: #666; background: #f5f5f5; padding: 8px; border-radius: 4px; margin-bottom: 12px;">Dev token: <code style="font-family: monospace;">{devToken}</code></p>{/if}
        <sp-textfield 
          placeholder="Reset token" 
          value={resetToken} 
          on:input={(e:any)=> { resetToken=e.target.value; resetTokenError = ''; }}
          invalid={resetTokenError !== ''}
          error-message={resetTokenError || undefined}
        />
        <sp-textfield 
          placeholder="New password (min 8 characters)" 
          type="password" 
          value={resetNew} 
          on:input={(e:any)=> { resetNew=e.target.value; resetNewError = ''; }}
          invalid={resetNewError !== ''}
          error-message={resetNewError || undefined}
        />
        <div class="actions">
          <sp-button on:click={()=> { showReset=false; resetTokenError = resetNewError = ''; }}>Cancel</sp-button>
          <sp-button variant="primary" on:click={doReset}>Reset</sp-button>
        </div>
      </div></div>
    {/if}
    
    {#if showToast}
      <sp-toast open variant={toastVariant} placement="top" style="position: fixed; top: 16px; right: 16px; z-index: 10001;">
        {toastMessage}
      </sp-toast>
    {/if}
  </div>
</sp-theme>

<style>
  .app-shell { display: flex; flex-direction: column; height: 100dvh; }
  .topbar { display: flex; align-items: center; justify-content: space-between; padding: 8px 12px; border-bottom: 1px solid #e5e5e5; }
  main { flex: 1; display: flex; }
  .placeholder { margin: auto; opacity: 0.7; }
  h1 { font-size: 18px; margin: 0; }
  .actions sp-button + sp-button { margin-left: 8px; }
  .chip { padding: 4px 8px; background: #f2f2f2; border-radius: 999px; margin-right: 8px; font-size: 12px; }
  .modal { position: fixed; inset: 0; background: rgba(0,0,0,.3); display: flex; align-items: center; justify-content: center; z-index: 1000; }
  .dialog { background: #fff; padding: 16px; border-radius: 8px; width: 360px; box-shadow: 0 6px 30px rgba(0,0,0,.2); }
  .dialog h3 { margin: 0 0 8px; font-size: 16px; }
  .dialog p { margin: 0 0 12px; font-size: 14px; }
  .actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 12px; }
  :global(body) { margin: 0; font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans", "Apple Color Emoji", "Segoe UI Emoji"; }
</style>


