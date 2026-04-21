<div wire:init="loadData">
    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- New Token Display --}}
    @if($plainTextToken)
        <div class="alert alert-warning">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <strong><i class="fas fa-key me-1"></i> Your New API Token</strong>
                <button wire:click="dismissToken" class="btn-close btn-close-sm" type="button"></button>
            </div>
            <p class="small mb-2">Copy this token now. It won't be shown again.</p>
            <div class="input-group">
                <input type="text" class="form-control form-control-sm font-monospace" value="{{ $plainTextToken }}" readonly id="tokenInput">
                <button class="btn btn-outline-secondary btn-sm" type="button" onclick="navigator.clipboard.writeText(document.getElementById('tokenInput').value).then(() => this.innerHTML = '<i class=\'fas fa-check\'></i> Copied')">
                    <i class="fas fa-copy"></i> Copy
                </button>
            </div>
        </div>
    @endif

    {{-- Create Token Form --}}
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0"><i class="fas fa-plus-circle me-1"></i> Create API Token</h6>
        </div>
        <div class="card-body">
            <form wire:submit="createToken">
                <div class="input-group">
                    <input type="text" wire:model="tokenName" class="form-control form-control-sm @error('tokenName') is-invalid @enderror"
                        placeholder="Token name (e.g., My App)">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-key me-1"></i> Create Token
                    </button>
                </div>
                @error('tokenName')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </form>
        </div>
    </div>

    {{-- Loading --}}
    <div wire:loading class="text-center py-2">
        <div class="spinner-border spinner-border-sm text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    @if(!$readyToLoad)
    <div class="p-3">
        <x-skeleton type="table-row" :count="8" />
    </div>
    @else

    {{-- Existing Tokens --}}
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0"><i class="fas fa-list me-1"></i> Your API Tokens</h6>
        </div>
        <div class="card-body p-0">
            @if($tokens->isEmpty())
                <div class="text-center text-muted py-4">
                    <i class="fas fa-key fa-2x mb-2 d-block opacity-50"></i>
                    <p class="mb-0">No API tokens created yet.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Created</th>
                                <th>Last Used</th>
                                <th style="width: 80px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tokens as $token)
                                <tr>
                                    <td>
                                        <span class="fw-medium">{{ $token->name }}</span>
                                    </td>
                                    <td>
                                        <small>{{ $token->created_at->format('M d, Y') }}</small>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $token->last_used_at ? $token->last_used_at->diffForHumans() : 'Never' }}
                                        </small>
                                    </td>
                                    <td>
                                        <button wire:click="revokeToken({{ $token->id }})" wire:confirm="Revoke this token? Any applications using it will lose access."
                                            class="btn btn-outline-danger btn-sm" title="Revoke">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
    @endif
</div>
