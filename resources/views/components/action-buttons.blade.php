@props(['edit' => null, 'delete' => null, 'restore' => null, 'deleteDisabled' => false, 'restoreLabel' => 'Restore'])

<div class="btn-group" role="group" aria-label="Actions">
    @if ($edit)
        <a href="{{ $edit }}" class="btn btn-sm btn-light border" title="Edit" aria-label="Edit">
            <i class="bi bi-pencil"></i>
        </a>
    @endif
    @if ($restore)
        <a href="{{ $restore }}" class="btn btn-sm btn-light border text-success" title="{{ $restoreLabel }}" aria-label="{{ $restoreLabel }}">
            <i class="bi bi-arrow-counterclockwise"></i>
        </a>
    @endif
    @if ($delete && !$deleteDisabled)
        <button type="button" class="btn btn-sm btn-light border text-danger" title="Delete" aria-label="Delete"
                data-bs-toggle="modal" data-bs-target="#deleteModal" data-action="{{ $delete }}">
            <i class="bi bi-trash"></i>
        </button>
    @endif
</div>
