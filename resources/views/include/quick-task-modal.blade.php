{{-- Reusable "quick add task" modal — include on any detail page that has a
     related CRM record (lead/deal/company/contact/order) and call
     openQuickTask(type, id, label) from a button to open it pre-linked. --}}
@can('tasks.create')
<div class="modal fade" id="quickTaskModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form class="modal-content" id="quickTaskForm">
            <div class="modal-header">
                <h5 class="modal-title mb-0">Add Task <small class="text-muted" id="quickTaskContext"></small></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="related_type" id="quickTaskRelatedType">
                <input type="hidden" name="related_id" id="quickTaskRelatedId">
                <input type="hidden" name="status" value="todo">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" class="form-control" name="title" id="quickTaskTitle" maxlength="255" required>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Assignee</label>
                        <select class="form-control" name="assigned_to" id="quickTaskAssignee">
                            <option value="">Unassigned</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Priority</label>
                        <select class="form-control" name="priority" id="quickTaskPriority">
                            <option value="medium" selected>Medium</option>
                            <option value="low">Low</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Due date</label>
                    <input type="datetime-local" class="form-control" name="due_at" id="quickTaskDue">
                </div>
                <div class="alert alert-danger d-none" id="quickTaskError"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Save Task</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    var usersLoaded = false;
    var csrf = document.querySelector('meta[name="csrf-token"]').content;

    window.openQuickTask = function (type, id, label) {
        var form = document.getElementById('quickTaskForm');
        form.reset();
        document.getElementById('quickTaskRelatedType').value = type;
        document.getElementById('quickTaskRelatedId').value = id;
        document.getElementById('quickTaskContext').textContent = label ? '— ' + label : '';
        document.getElementById('quickTaskError').classList.add('d-none');

        if (!usersLoaded) {
            usersLoaded = true;
            $.get('{{ route('tasks.assignable_users') }}', function (data) {
                var select = document.getElementById('quickTaskAssignee');
                (data.users || []).forEach(function (u) {
                    var opt = document.createElement('option');
                    opt.value = u.id;
                    opt.textContent = u.name;
                    select.appendChild(opt);
                });
            });
        }

        $('#quickTaskModal').modal('show');
    };

    $('#quickTaskForm').on('submit', function (e) {
        e.preventDefault();
        var errorBox = document.getElementById('quickTaskError');
        errorBox.classList.add('d-none');

        $.ajax({
            url: '{{ route('tasks.store') }}',
            type: 'POST',
            data: $(this).serialize(),
            headers: { 'X-CSRF-TOKEN': csrf },
        }).done(function () {
            $('#quickTaskModal').modal('hide');
            if (window.toastr) toastr.success('Task created');
        }).fail(function (xhr) {
            var msg = xhr.responseJSON?.message || Object.values(xhr.responseJSON?.errors || {})[0]?.[0] || 'Could not create task.';
            errorBox.textContent = msg;
            errorBox.classList.remove('d-none');
        });
    });
})();
</script>
@endcan
