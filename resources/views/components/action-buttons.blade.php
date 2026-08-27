@props(['edit' => null, 'delete' => null, 'restore' => null, 'deleteDisabled' => false, 'restoreLabel' => 'Restore'])

<div class="btn-group" role="group" aria-label="Actions">
    @if ($edit)
        <a href="{{ $edit }}" class="btn btn-sm btn-light border rounded-2" title="Edit" aria-label="Edit" style="min-width:38px">
            <i class="bi bi-pencil"></i>
        </a>
    @endif
    @if ($restore)
        <a href="{{ $restore }}" class="btn btn-sm btn-light border text-success rounded-2" title="{{ $restoreLabel }}" aria-label="{{ $restoreLabel }}" style="min-width:38px">
            <i class="bi bi-arrow-counterclockwise"></i>
        </a>
    @endif
    @if ($delete && !$deleteDisabled)
        <button type="button" class="btn btn-sm btn-light border text-danger rounded-2" title="Delete" aria-label="Delete"
                style="min-width:38px"
                data-bs-toggle="modal" data-bs-target="#deleteModal" data-action="{{ $delete }}">
            <i class="bi bi-trash"></i>
        </button>
    @endif
</div>
