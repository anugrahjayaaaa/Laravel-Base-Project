@props(['bulkRoute', 'canSoft' => true, 'canForce' => true])

@if ($canSoft || $canForce)
<form id="bulk-form" method="POST" action="{{ $bulkRoute }}" class="mb-3">
    @csrf
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <select name="action" class="form-select form-select-sm" style="width:auto" required>
            <option value="" disabled selected>{{ ui('bulk_action') }}</option>
            @if ($canSoft)<option value="soft">{{ ui('soft_delete') }}</option>@endif
            @if ($canForce)<option value="force">{{ ui('permanently_delete') }}</option>@endif
        </select>
        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('{{ ui('confirm_bulk_delete') }}')">
            {{ ui('apply') }}
        </button>
        <span class="text-muted small" id="bulk-count"></span>
    </div>
</form>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('bulk-form');
    if (!form) return;
    const selectAll = document.getElementById('bulk-select-all');
    const boxes = () => form.querySelectorAll('input[name="ids[]"]');
    const count = document.getElementById('bulk-count');

    const sync = () => {
        const checked = form.querySelectorAll('input[name="ids[]"]:checked').length;
        if (count) count.textContent = checked ? `(${checked} {{ ui('selected') }})` : '';
        const all = boxes();
        if (selectAll) selectAll.indeterminate = checked > 0 && checked < all.length;
    };

    if (selectAll) {
        selectAll.addEventListener('change', () => {
            boxes().forEach(b => b.checked = selectAll.checked);
            sync();
        });
    }
    boxes().forEach(b => b.addEventListener('change', sync));
    sync();
});
</script>
@endpush

