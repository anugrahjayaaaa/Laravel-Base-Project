<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ ui('confirm_delete') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">{{ ui('delete_confirm_body') }}</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ ui('cancel') }}</button>
                <form id="deleteModalForm" method="POST">@csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger">{{ ui('delete') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('show.bs.modal', function (e) {
    if (e.target.id === 'deleteModal') {
        const btn = e.relatedTarget;
        const action = btn.getAttribute('data-action');
        if (action) document.getElementById('deleteModalForm').setAttribute('action', action);
    }
});
</script>
