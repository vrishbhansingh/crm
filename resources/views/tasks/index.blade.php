<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"><title>Tasks & Reminders</title>
    <link rel="stylesheet" href="{{ asset('vendors/feather/feather.css') }}"><link rel="stylesheet" href="{{ asset('vendors/ti-icons/css/themify-icons.css') }}"><link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}"><link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        /* Same modernization pattern as the rest of this pass: bigger, roomier cards. */
        .crm-page-header{background:#fff;padding:26px 28px;border-radius:16px;box-shadow:0 8px 24px rgba(15,23,42,.06);margin-bottom:24px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px}
        .crm-page-header h3{margin:0 0 6px;font-weight:700;font-size:22px;color:#111827}.crm-page-header p{margin:0;color:#6b7280;font-size:14px}
        .crm-page-header .btn{border-radius:10px;padding:10px 20px;font-weight:600}
        .task-shell { background:#fff; border-radius:16px; box-shadow:0 8px 24px rgba(15,23,42,.06); overflow:hidden; }
        .task-row { display:grid; grid-template-columns:32px minmax(220px,1fr) 135px 155px 130px 100px; gap:14px; align-items:center; padding:18px 22px; border-bottom:1px solid #edf2f7; font-size:14.5px; }
        .task-row:hover { background:#f8fbff; } .task-title { font-weight:600;color:#1f2937;font-size:15px}.task-meta{font-size:13px;color:#6b7280}.priority{font-size:12px;font-weight:700;text-transform:uppercase}.overdue{color:#dc2626}.completed .task-title{text-decoration:line-through;color:#9ca3af}
        .filter-grid { display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px }
        @media(max-width:900px){.task-row{grid-template-columns:30px 1fr}.task-cell-secondary{grid-column:2}}
    </style>
</head>
<body><div class="container-scroller">@include('include.header')<div class="container-fluid page-body-wrapper">@include('include.sidebar')<div class="main-panel"><div class="content-wrapper">
    <div class="crm-page-header"><div><h3>Tasks & Reminders</h3><p>Keep every follow-up and commitment visible.</p></div>@can('tasks.create')<button class="btn btn-primary" id="newTaskBtn"><i class="fa fa-plus"></i> New Task</button>@endcan</div>
    <div class="card mb-3" style="border-radius:16px;box-shadow:0 8px 24px rgba(15,23,42,.06);border:none"><div class="card-body filter-grid">
        <select id="filterStatus" class="form-control"><option value="">All statuses</option><option value="todo">To do</option><option value="in_progress">In progress</option><option value="completed">Completed</option><option value="cancelled">Cancelled</option></select>
        <select id="filterPriority" class="form-control"><option value="">All priorities</option><option value="urgent">Urgent</option><option value="high">High</option><option value="medium">Medium</option><option value="low">Low</option></select>
        <select id="filterDue" class="form-control"><option value="">Any due date</option><option value="today">Due today</option><option value="overdue">Overdue</option><option value="upcoming">Upcoming</option></select>
        <select id="filterAssignee" class="form-control"><option value="">All assignees</option>@foreach($users as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach</select>
    </div></div>
    <div class="task-shell" id="taskList"><div class="p-4 text-center text-muted">Loading tasks…</div></div>
</div>@include('include.footer')</div></div></div>

<div class="modal fade" id="taskModal"><div class="modal-dialog modal-lg"><form class="modal-content" id="taskForm"><div class="modal-header"><h5 id="taskModalTitle">New Task</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body"><input type="hidden" id="taskId"><div class="form-row">
    <div class="form-group col-md-8"><label>Title</label><input class="form-control" name="title" id="taskTitle" maxlength="255" required></div><div class="form-group col-md-4"><label>Assignee</label><select class="form-control" name="assigned_to" id="taskAssignee"><option value="">Unassigned</option>@foreach($users as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach</select></div>
    <div class="form-group col-md-12"><label>Description</label><textarea class="form-control" name="description" id="taskDescription" rows="3"></textarea></div>
    <div class="form-group col-md-3"><label>Priority</label><select class="form-control" name="priority" id="taskPriority"><option value="medium">Medium</option><option value="low">Low</option><option value="high">High</option><option value="urgent">Urgent</option></select></div>
    <div class="form-group col-md-3"><label>Status</label><select class="form-control" name="status" id="taskStatus"><option value="todo">To do</option><option value="in_progress">In progress</option><option value="completed">Completed</option><option value="cancelled">Cancelled</option></select></div>
    <div class="form-group col-md-3"><label>Due</label><input class="form-control" type="datetime-local" name="due_at" id="taskDue"></div><div class="form-group col-md-3"><label>Remind at</label><input class="form-control" type="datetime-local" name="remind_at" id="taskRemind"></div>
    <div class="form-group col-md-4"><label>Link type</label><select class="form-control" name="related_type" id="taskRelatedType"><option value="">No link</option><option value="lead">Lead</option><option value="deal">Deal</option><option value="company">Company</option><option value="contact">Contact</option><option value="order">Order</option></select></div><div class="form-group col-md-8"><label>Linked record</label><select class="form-control" name="related_id" id="taskRelatedId" disabled><option value="">Select a type first</option></select></div>
</div><div class="alert alert-danger d-none" id="taskError"></div></div><div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button><button class="btn btn-primary">Save Task</button></div></form></div></div>
<script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
<script>
(() => {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    let tasks = [];
    const canEdit = @json(auth()->user()->can('tasks.edit'));
    const canDelete = @json(auth()->user()->can('tasks.delete'));
    const esc = value => $('<div>').text(value ?? '').html();
    const localDate = value => value ? new Date(value).toLocaleString() : 'No due date';
    const inputDate = value => value ? new Date(value).toISOString().slice(0, 16) : '';

    function loadTasks() {
        const params = new URLSearchParams({status:$('#filterStatus').val(),priority:$('#filterPriority').val(),due:$('#filterDue').val(),assigned_to:$('#filterAssignee').val()});
        $.get(`{{ route('tasks.data') }}?${params}`, response => { tasks = response.data; render(); });
    }
    function render() {
        if (!tasks.length) { $('#taskList').html('<div class="p-5 text-center text-muted"><i class="fa fa-check-circle-o fa-2x mb-2"></i><br>No tasks match these filters.</div>'); return; }
        $('#taskList').html(tasks.map(t => `<div class="task-row ${t.status === 'completed' ? 'completed' : ''}" data-id="${t.id}">
            <div>${canEdit && t.status !== 'completed' ? `<button class="btn btn-sm btn-link completeTask" title="Complete"><i class="fa fa-circle-o"></i></button>` : '<i class="fa fa-check-circle text-success"></i>'}</div>
            <div><div class="task-title">${esc(t.title)}</div><div class="task-meta">${esc(t.description || '')}${t.related_label ? ` · ${esc(t.related_type)}: ${esc(t.related_label)}` : ''}</div></div>
            <div class="task-cell-secondary"><span class="priority text-${t.priority === 'urgent' ? 'danger' : (t.priority === 'high' ? 'warning' : 'primary')}">${esc(t.priority)}</span></div>
            <div class="task-cell-secondary ${t.is_overdue ? 'overdue' : ''}"><i class="fa fa-clock-o"></i> ${esc(localDate(t.due_at))}</div>
            <div class="task-cell-secondary">${esc(t.assignee?.name || 'Unassigned')}</div>
            <div class="task-cell-secondary">${canEdit ? '<button class="btn btn-sm btn-outline-primary editTask"><i class="fa fa-pencil"></i></button>' : ''} ${canDelete ? '<button class="btn btn-sm btn-outline-danger deleteTask"><i class="fa fa-trash"></i></button>' : ''}</div>
        </div>`).join(''));
    }
    async function loadRelated(type, selected = '') {
        const select = $('#taskRelatedId');
        if (!type) { select.prop('disabled', true).html('<option value="">Select a type first</option>'); return; }
        const response = await $.get(`{{ url('/tasks/related-options') }}/${type}`);
        select.prop('disabled', false).html('<option value="">Choose record</option>' + response.data.map(r => `<option value="${r.id}">${esc(r.label)}</option>`).join('')).val(String(selected || ''));
    }
    function openTask(task = null) {
        $('#taskForm')[0].reset(); $('#taskError').addClass('d-none'); $('#taskId').val(task?.id || ''); $('#taskModalTitle').text(task ? 'Edit Task' : 'New Task');
        if (task) { $('#taskTitle').val(task.title); $('#taskDescription').val(task.description); $('#taskAssignee').val(task.assigned_to || ''); $('#taskPriority').val(task.priority); $('#taskStatus').val(task.status); $('#taskDue').val(inputDate(task.due_at)); $('#taskRemind').val(inputDate(task.remind_at)); $('#taskRelatedType').val(task.related_type || ''); loadRelated(task.related_type, task.related_id); } else { loadRelated(''); }
        $('#taskModal').modal('show');
    }
    $('#newTaskBtn').on('click', () => openTask());
    $('#taskRelatedType').on('change', function(){ loadRelated(this.value); });
    $('.filter-grid select').on('change', loadTasks);
    $(document).on('click', '.editTask', function(){ openTask(tasks.find(t => t.id === Number($(this).closest('.task-row').data('id')))); });
    $(document).on('click', '.completeTask', function(){ $.ajax({url:`{{ url('/tasks') }}/${$(this).closest('.task-row').data('id')}/complete`,method:'POST',headers:{'X-CSRF-TOKEN':csrf}}).done(loadTasks); });
    $(document).on('click', '.deleteTask', function(){ if(confirm('Delete this task?')) $.ajax({url:`{{ url('/tasks') }}/${$(this).closest('.task-row').data('id')}`,method:'DELETE',headers:{'X-CSRF-TOKEN':csrf}}).done(loadTasks); });
    $('#taskForm').on('submit', function(e){ e.preventDefault(); const id=$('#taskId').val(); const data=Object.fromEntries(new FormData(this)); if(!data.related_type) delete data.related_id; if(id) data._method='PUT'; $.ajax({url:id ? `{{ url('/tasks') }}/${id}` : `{{ route('tasks.store') }}`,method:'POST',headers:{'X-CSRF-TOKEN':csrf},data}).done(() => {$('#taskModal').modal('hide');loadTasks();}).fail(xhr => $('#taskError').removeClass('d-none').text(xhr.responseJSON?.message || 'Unable to save task.')); });
    loadTasks();
})();
</script></body></html>
