<div class="modal fade" id="forceDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Permanently delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">This will permanently delete the item and cannot be undone. Continue?</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="forceDeleteModalForm" method="POST">@csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete permanently</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('show.bs.modal', function (e) {
    if (e.target.id === 'forceDeleteModal') {
        const btn = e.relatedTarget;
        const action = btn.getAttribute('data-action');
        if (action) document.getElementById('forceDeleteModalForm').setAttribute('action', action);
    }
});
</script>
