@props(['bulkRoute', 'canSoft' => true, 'canForce' => true])

@if ($canSoft || $canForce)
<style>
    /* soft-deleted rows: subtle, readable — not the harsh table-secondary */
    tr.row-deleted td { background: rgba(220, 53, 69, 0.06); }
    tr.row-deleted td:first-child { box-shadow: inset 3px 0 0 #dc3545; }
</style>
<form id="bulk-form" method="POST" action="{{ $bulkRoute }}" class="mb-3">
    @csrf
    <div class="d-flex align-items-center justify-content-end gap-2 flex-wrap">
        <span class="text-muted small me-auto" id="bulk-count"></span>
        <select name="action" class="form-select form-select-sm" style="width:auto" required>
            <option value="" disabled selected>{{ ui('bulk_action') }}</option>
            @if ($canSoft)<option value="soft">{{ ui('soft_delete') }}</option>@endif
            @if ($canForce)<option value="force">{{ ui('permanently_delete') }}</option>@endif
        </select>
        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('{{ ui('confirm_bulk_delete') }}')">
            {{ ui('apply') }}
        </button>
    </div>
</form>
@endif

@push('scripts')
<script>
// Event delegation: immune to init-timing / element-not-found issues.
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('bulk-form');
    if (!form) return;
    const count = document.getElementById('bulk-count');
    const rows = () => document.querySelectorAll('input[name="ids[]"]');

    const sync = () => {
        const boxes = rows();
        const checked = [...boxes].filter(b => b.checked).length;
        if (count) count.textContent = checked ? `(${checked} {{ ui('selected') }})` : '';
        const all = document.getElementById('bulk-select-all');
        if (all) all.indeterminate = checked > 0 && checked < boxes.length;
    };

    // header checkbox -> toggle all rows
    document.addEventListener('change', function (e) {
        if (e.target && e.target.id === 'bulk-select-all') {
            rows().forEach(b => { b.checked = e.target.checked; });
            sync();
        }
    });
    // any row checkbox -> update count
    document.addEventListener('change', function (e) {
        if (e.target && e.target.name === 'ids[]') sync();
    });

    sync();
});
</script>
@endpush
