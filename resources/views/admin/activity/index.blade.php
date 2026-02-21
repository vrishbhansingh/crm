@extends('admin.layout.app_layout')

@section('content')


    
    <div class="content-wrapper">
    <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center" style="background-color:#e2e2e2;">
    <h4 class="card-title">Lead Activity List</h4>
    <button type="button" class="btn btn-warning" style="padding: 9px 17px; border-radius: 2px;background-color: revert;" data-toggle="modal" data-target="#exampleModal-4" data-whatever="@mdo" title="Add User">
        Add Activity&nbsp;<i class="fa fa-cube"></i>
    </button>
</div>


        <div class="card-body">
          <div class="row">
            <div class="col-12">
              <div class="table-responsive">
                <table id="order_list" class="table table-striped table-bordered table-hover" style="width:100%">
                  <thead>
                    <tr>
                        <th>#</th>
                        <th>Lead Name</th>
                        <th>User Id</th>
                        <th>Type</th>
                        <th>Follow Date</th>
                        <th>Remarks</th>
                        <th>Action</th>
                      
                    </tr>
                  </thead>
                  <tbody>
                    
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
    
<div class="modal fade" id="exampleModal-4" tabindex="-1" role="dialog" aria-labelledby="ModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document"> <!-- Default medium size -->
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ModalLabel" style="font-size: 19px;">Add Lead Activity</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      
      <form class="form-sample add_activity" id="add_activity" method="post">
        <div class="modal-body">
          <div class="row">
            <!-- Left Column -->
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Choose Lead</label>
                <select name="lead_id" class="form-control" style="border-radius: 5px; height: 50px; font-size: 16px;">
                  <option value="">-- Select Lead --</option>
                  @foreach($leads as $lead)
                    <option value="{{ $lead->id }}">{{ $lead->name }}</option>
                  @endforeach
                </select>
              </div>
              
              <div class="mb-3">
            <label class="form-label">Type</label>
            <select name="type" class="form-control" style="border-radius: 5px; height: 50px; font-size: 16px;">
              <option value="">-- Select Type --</option>
              <option value="Note">Note</option>
                <option value="Call">Call</option>
                <option value="Email">Email</option>
                <option value="Meeting">Meeting</option>
                <option value="Task">Task</option>
            </select>
          </div>
			  
            </div>
            
            <!-- Right Column -->
            <div class="col-md-6">
             <div class="mb-3">
                <label class="form-label">Assign To</label>
                <select name="user_id" class="form-control" style="border-radius: 5px; height: 50px; font-size: 16px;">
                  <option value="">-- Select User --</option>
                  @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                  @endforeach
                </select>
              </div>
              
              
                <div class="mb-3">
                <label class="form-label">Follow Up Date</label>
                <input type="date" name="date" 
                       class="form-control" style="border-radius: 5px;">
              </div>
             
          
            </div>
			
			<div class="col-md-12 mb-3">
                <label class="form-label">Message</label>
                <textarea name="message" placeholder="Enter Message..." class="form-control" rows="3"></textarea>
              </div>
            
          </div>
        </div>
        
        <div class="modal-footer">
          <button type="submit" class="btn btn-success add-button">Submit</button>
          <button type="button" class="btn btn-light cancel-button" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>


    
<div class="modal fade" id="editProduct" tabindex="-1" role="dialog" aria-labelledby="ModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document"> <!-- Default medium size -->
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ModalLabel" style="font-size: 19px;">Update Task</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form class="form-sample update_task" id="update_task" method="post">
        <div class="modal-body">
            <input type="hidden" name="id" id="task_id">
          <div class="row">
            <!-- Left Column -->
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Choose Lead</label>
                <select name="lead_id" id="ulead_id" class="form-control" style="border-radius: 5px; height: 50px; font-size: 16px;">
                  <option value="">-- Select Lead --</option>
                  @foreach($leads as $lead)
                    <option value="{{ $lead->id }}">{{ $lead->name }}</option>
                  @endforeach
                </select>
              </div>
              
              <div class="mb-3">
            <label class="form-label">Task Type</label>
            <select name="task_type" id="utask_type" class="form-control" style="border-radius: 5px; height: 50px; font-size: 16px;">
              <option value="">-- Select Type --</option>
                <option value="Call">Call</option>
                <option value="Meeting">Meeting</option>
                <option value="Follow-up">Follow-up</option>
                <option value="Reminder">Reminder</option>
            </select>
          </div>
			  
              
               <div class="mb-3">
                <label class="form-label">Due Date</label>
                <input type="date" name="due_date" id="due_date"
                       class="form-control due_date" style="border-radius: 5px;">
              </div>
              
			
			 
            </div>
            
            <!-- Right Column -->
            <div class="col-md-6">
             <div class="mb-3">
                <label class="form-label">Assign To</label>
                <select name="user_id" id="uuser_id" class="form-control" style="border-radius: 5px; height: 50px; font-size: 16px;">
                  <option value="">-- Select User --</option>
                  @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                  @endforeach
                </select>
              </div>
              
              
                <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" placeholder="Enter Title..." name="title" id="utitle"
                       class="form-control" style="border-radius: 5px;">
              </div>
             
             <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" id="status" class="form-control" style="border-radius: 5px; height: 50px; font-size: 16px;">
              <option value="">-- Select Status --</option>
                <option value="Pending">Pending</option>
                <option value="Completed">Completed</option>
                <option value="Overdue">Overdue</option>
            </select>
          </div>
		
            </div>
			
			<div class="col-md-12 mb-3">
                <label class="form-label">Message</label>
                <textarea name="message" id="umessage" placeholder="Enter Message..." class="form-control" rows="3"></textarea>
              </div>
        
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success add-button">Update</button>
          <button type="button" class="btn btn-light cancel-button" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>




@endsection
@section('extra_js')
<script>
$(function(){

  $('#load').hide();
  $.fn.tableload = function (e) {
    
    $('#order_list').dataTable(
        {
            "destroy": true,
            "processing": true,
            pageLength: 10,
            "serverSide": true,
            "scrollX": true,
            'checkboxes': {
                'selectRow': true
            },
            "ajax": {
                url: "{{ route('admin.get_activity_list') }}",
                
                dataFilter: function (data) {
                    var json = jQuery.parseJSON(data);
                    json.recordsTotal = json.recordsTotal;
                    json.recordsFiltered = json.recordsFiltered;
                    json.data = json.data;
                    return JSON.stringify(json); // return JSON string
                }
            },

            "order": [[0, 'desc']],
            "columns": [
               {"targets": 0, "name": "id", 'searchable': false,  'orderable': true},
                {"targets": 1, "name": "image", 'searchable': false,  'orderable': false},
                {"targets": 2, "name": "name", 'searchable': false, 'orderable': false},
                {"targets": 3, "name": "email", 'searchable': false, 'orderable': true},
                {"targets": 4, "name": "phone", 'searchable': false, 'orderable': true},
                {"targets": 5, "name": "role", 'searchable': false, 'orderable': true},
                {"targets": 6, "name": "action", 'searchable': false, 'orderable': true},
                
            ]
        });
}

$.fn.tableload();

$(document).ready(function () {
    $('body').on('click', '.view_slip', function () {
        $('#myModal').modal('show');
        var id = $(this).attr('rel');
        $('#slip_id').val(id);
    });
});

$('#add_activity').on('submit', function(e){
            e.preventDefault();
      
            var fd = new FormData(this);
                fd.append('_token','{{csrf_token()}}');
                $.ajax({
                    url: "{{ route('admin.save_activity') }}",
                    type: "POST",
                    data: fd,
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    beforeSend: function () {
                     $('#load').show();
                        $('#fa-spinner').show();
                    },
                    success: function (result) {
                        if(result.status){
                            toast.success(result.msg);
                            $('#add_activity')[0].reset();
                            $('#exampleModal-4').modal('toggle');
                            $('#order_list').DataTable().ajax.reload(null, false);
                        }else{
                            toast.error(result.msg);
                            
                        }
                    },
                    complete: function () {
                     $('#load').hide();
                        
                    },
                    error: function (jqXHR, exception) {
                        console.log(jqXHR.responseText);
                        $('#load').hide();
                    }
                });
              });
              
$('body').on("click", ".del-activity", function (e) {
					var id = $(this).data('id')
					let fd = new FormData();
					fd.append('id', id);
					fd.append('_token', '{{ csrf_token() }}');
					$.confirm({
						title: 'Confirm!',
						content: 'Sure you want to delete this Lead Activity?',
						buttons: {
							yes: function () {
								$.ajax({
									url: "{{ route('admin.del_activity') }}/",
									type: 'POST',
									data: fd,
									dataType: "JSON",
									contentType: false,
									processData: false,
								})
									.done(function (result) {
										if (result.status) {
											toast.success(result.msg);
											$('#order_list').DataTable().ajax.reload(null, false);

										} else {
											toast.error(result.msg);
										}
									})
									.fail(function (jqXHR, exception) {
										console.log(jqXHR.responseText);
									})


							},
							no: function () {
							},
						}
					})
				})
              
              
    $("body").on("click", ".edit_task",  function () {
    var id = $(this).data('id');
    var lead_id = $(this).data('lead_id');
    var user_id = $(this).data('user_id');
    var task_type = $(this).data('task_type');
    var due_date = $(this).data('due_date');
    var title = $(this).data('title');
    var message = $(this).data('message');
    var status = $(this).data('status');
                        
    $('#task_id').val(id);
    $('#ulead_id').val(lead_id);
    $('#uuser_id').val(user_id);
    $('#utask_type').val(task_type);
    $('.due_date').val(due_date);
    $('#utitle').val(title);
    $('#umessage').val(message);
    $('#status').val(status);
    $('#editProduct').modal('toggle');
    });                                              


$('#update_task').on('submit', function(e){
            e.preventDefault();
      
            var fd = new FormData(this);
                fd.append('_token','{{csrf_token()}}');
                $.ajax({
                    url: "{{ route('admin.update_task') }}",
                    type: "POST",
                    data: fd,
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    beforeSend: function () {
                     $('#load').show();
                        $('#fa-spinner').show();
                    },
                    success: function (result) {
                        if(result.status){
                            toast.success(result.msg);
                            $('#update_task')[0].reset();
                            $('#editProduct').modal('toggle');
                            $('#order_list').DataTable().ajax.reload(null, false);
                        }else{
                            toast.error(result.msg);
                            
                        }
                    },
                    complete: function () {
                     $('#load').hide();
                        
                    },
                    error: function (jqXHR, exception) {
                        console.log(jqXHR.responseText);
                        $('#load').hide();
                    }
                });
              });





});

</script>
@endsection