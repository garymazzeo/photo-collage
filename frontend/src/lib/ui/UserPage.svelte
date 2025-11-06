<script lang="ts">
  import '@spectrum-web-components/textfield/sp-textfield.js';
  import '@spectrum-web-components/button/sp-button.js';
  import '@spectrum-web-components/toast/sp-toast.js';
  import '@spectrum-web-components/alert-dialog/sp-alert-dialog.js';
  import { me, updateName, changePassword } from '$lib/api/client';
  
  let name = '';
  let email = '';
  let current = '';
  let next = '';
  let nextConfirm = '';
  
  // Validation states
  let nameError = '';
  let currentError = '';
  let nextError = '';
  let nextConfirmError = '';
  
  // Toast management
  let toastMessage = '';
  let toastVariant: 'positive' | 'negative' = 'positive';
  let showToast = false;
  
  function showMessage(text: string, variant: 'positive' | 'negative' = 'positive') {
    toastMessage = text;
    toastVariant = variant;
    showToast = true;
    setTimeout(() => { showToast = false; }, 3000);
  }
  
  async function load() {
    const info = await me();
    if (info) { 
      email = info.email; 
      name = (info as any).name || ''; 
    }
  }
  load();
  
  function validateName(): boolean {
    nameError = '';
    if (name.trim().length === 0) {
      nameError = 'Name cannot be empty';
      return false;
    }
    if (name.trim().length > 191) {
      nameError = 'Name is too long (max 191 characters)';
      return false;
    }
    return true;
  }
  
  function validatePassword(): boolean {
    currentError = '';
    nextError = '';
    nextConfirmError = '';
    
    if (current.length === 0) {
      currentError = 'Current password is required';
      return false;
    }
    
    if (next.length < 8) {
      nextError = 'Password must be at least 8 characters';
      return false;
    }
    
    if (next !== nextConfirm) {
      nextConfirmError = 'Passwords do not match';
      return false;
    }
    
    return true;
  }
  
  async function saveName() {
    if (!validateName()) {
      showMessage('Please fix the errors', 'negative');
      return;
    }
    try { 
      await updateName(name.trim());
      showMessage('Name updated successfully', 'positive');
    } catch (e: any) {
      showMessage('Failed to update name: ' + (e.message || 'Unknown error'), 'negative');
    }
  }
  
  async function savePassword() {
    if (!validatePassword()) {
      showMessage('Please fix the errors', 'negative');
      return;
    }
    const ok = await changePassword(current, next);
    if (ok) { 
      showMessage('Password changed successfully', 'positive');
      current = next = nextConfirm = '';
      currentError = nextError = nextConfirmError = '';
    } else { 
      currentError = 'Current password is incorrect';
      showMessage('Current password is incorrect', 'negative');
    }
  }
</script>

<div class="userpage">
  <h2>Account</h2>
  <div class="row">
    <label>Email</label>
    <div>{email}</div>
  </div>
  
  <div class="row">
    <label>Name</label>
    <div class="field-group">
      <sp-textfield 
        value={name} 
        on:input={(e)=> { name=e.target.value; nameError = ''; }}
        invalid={nameError !== ''}
        error-message={nameError || undefined}
      />
    </div>
  </div>
  <div>
    <sp-button variant="primary" on:click={saveName}>Save Name</sp-button>
  </div>

  <h3>Change Password</h3>
  <div class="row">
    <label>Current</label>
    <div class="field-group">
      <sp-textfield 
        type="password" 
        value={current} 
        on:input={(e)=> { current=e.target.value; currentError = ''; }}
        invalid={currentError !== ''}
        error-message={currentError || undefined}
      />
    </div>
  </div>
  <div class="row">
    <label>New</label>
    <div class="field-group">
      <sp-textfield 
        type="password" 
        value={next} 
        on:input={(e)=> { next=e.target.value; nextError = nextConfirmError = ''; }}
        invalid={nextError !== ''}
        error-message={nextError || undefined}
      />
    </div>
  </div>
  <div class="row">
    <label>Confirm</label>
    <div class="field-group">
      <sp-textfield 
        type="password" 
        value={nextConfirm} 
        on:input={(e)=> { nextConfirm=e.target.value; nextConfirmError = ''; }}
        invalid={nextConfirmError !== ''}
        error-message={nextConfirmError || undefined}
      />
    </div>
  </div>
  <div>
    <sp-button on:click={savePassword}>Update Password</sp-button>
  </div>
</div>

{#if showToast}
  <sp-toast open variant={toastVariant} placement="top" style="position: fixed; top: 16px; right: 16px; z-index: 10000;">
    {toastMessage}
  </sp-toast>
{/if}

<style>
  .userpage { padding: 16px; max-width: 560px; }
  .row { display: grid; grid-template-columns: 120px 1fr; align-items: start; gap: 12px; margin: 12px 0; }
  .field-group { width: 100%; }
  h2 { margin: 0 0 12px; }
  h3 { margin: 24px 0 12px; }
  label { font-size: 14px; font-weight: 500; padding-top: 8px; }
</style>
