@props([
    'autocompleteEmail' => 'off',
    'autocompletePassword' => 'new-password',
])

{{-- Hidden honeypot fields to trick browser autofill --}}
<input type="text" name="fake_email" style="display:none !important; position:absolute; left:-9999px;" tabindex="-1" autocomplete="username">
<input type="password" name="fake_password" style="display:none !important; position:absolute; left:-9999px;" tabindex="-1" autocomplete="new-password">

<div class="input">
    <input
        type="email"
        id="login_email"
        name="email"
        placeholder=" "
        value="{{ old('email') }}"
        required
        autofocus
        autocomplete="{{ $autocompleteEmail }}"
        data-lpignore="true"
        data-form-type="other">
    <label for="login_email">EMAIL</label>
</div>

<div class="input">
    <input
        type="password"
        id="login_password"
        name="password"
        placeholder=" "
        required
        autocomplete="{{ $autocompletePassword }}"
        data-lpignore="true"
        data-form-type="other">
    <label for="login_password">PASSWORD</label>

    <button
        type="button"
        class="toggle pw-toggle"
        data-target="login_password"
        aria-label="Toggle password visibility"
        aria-pressed="false">
        <i class="fa fa-eye" aria-hidden="true"></i>
    </button>
</div>
