<div class="modal fade" id="featureToggleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm feature change</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="featureToggleBody">Are you sure you want to change this feature?</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="featureToggleForm" method="POST">@csrf
                    <input type="hidden" name="enabled" id="featureToggleEnabled" value="1">
                    <button type="submit" class="btn btn-primary" id="featureToggleSubmit">Confirm</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('show.bs.modal', function (e) {
    if (e.target.id === 'featureToggleModal') {
        const btn = e.relatedTarget;
        document.getElementById('featureToggleForm').setAttribute('action', btn.getAttribute('data-action'));
        document.getElementById('featureToggleEnabled').value = btn.getAttribute('data-enabled');
        const next = btn.getAttribute('data-enabled') === '1' ? 'enable' : 'disable';
        document.getElementById('featureToggleBody').textContent = 'Are you sure you want to ' + next + ' this feature?';
        document.getElementById('featureToggleSubmit').textContent = next === 'enable' ? 'Enable' : 'Disable';
    }
});
</script>
