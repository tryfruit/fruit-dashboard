@extends('meta.base-user')

  @section('pageTitle')
    Manage dashboards
  @stop

  @section('pageStylesheet')
  @stop

  @section('pageContent')

  <div class="container">

    <h1 class="text-center text-white drop-shadow">
      Manage dashboards
    </h1>

    <div class="row">
      <div class="col-md-10 col-md-offset-1">
        <div class="panel panel-default panel-transparent">
          <div class="panel-body">
            <div class="row">

              @foreach (Auth::user()->dashboards as $dashboard)
                <div class="col-sm-6 col-md-4">
                  <div class="thumbnail">
                    <img src="{{ Auth::user()->background->url }}" alt="{{ $dashboard->name }}" />
                    <div class="caption text-center">
                      <h5>{{ $dashboard->name }}</h5>
                      <p>
                        <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#rename-dashboard-modal" data-dashboard-name="{{ $dashboard->name }}" data-dashboard-id="{{ $dashboard->id }}" >Edit name</button>
                        <button class="btn btn-sm btn-danger delete-dashboard" onclick="deleteDashboard({{ $dashboard->id }});">Delete</button>
                      </p>
                    </div> <!-- /.caption -->
                  </div> <!-- /.thumbnail -->
                </div> <!-- /.col-sm-6 -->
              @endforeach
              
              <div class="col-sm-6 col-md-4">
                <div class="add-new text-center no-underline clickable" onclick="$('#add-dashboard-modal').modal('show');">
                  Add new...
                </div> <!-- /.add-new -->  
              </div> <!-- /.col-sm-6 -->

              <!-- Add new dashboard modal -->
              <div class="modal fade" id="add-dashboard-modal" tabindex="-1" role="dialog" aria-labelledby="add-dashboard-label">
                <div class="modal-dialog" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                      <h4 class="modal-title" id="add-dashboard-label">Add new dashboard</h4>
                    </div>
                    <form id="add-dashboard-form" class="form-horizontal">
                      <div class="modal-body">
                          <div id="name-input-group" class="form-group">
                            <label for="new-dashboard" class="col-sm-5 control-label">Choose a name</label>
                            <div class="col-sm-7">
                              <input id="name-input" type="text" class="form-control" />
                            </div> <!-- /.col-sm-7 -->
                          </div> <!-- /.form-group -->
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
              <!-- /Add new dashboard modal -->

              <!-- Rename dashboard modal -->
              <div class="modal fade" id="rename-dashboard-modal" tabindex="-1" role="dialog" aria-labelledby="rename-dashboard-label">
                <div class="modal-dialog" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                      <h4 class="modal-title" id="rename-dashboard-label">Rename dashboard</h4>
                    </div>
                    <form id="rename-dashboard-form" class="form-horizontal">
                      <div class="modal-body">
                          <div id="rename-input-group" class="form-group">
                            <label for="new-dashboard" class="col-sm-5 control-label">Rename the dashboard</label>
                            <div class="col-sm-7">
                              <input id="rename-input" type="text" class="form-control" />
                            </div> <!-- /.col-sm-7 -->
                          </div> <!-- /.form-group -->
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Rename</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
              <!-- /Rename dashboard modal -->
              
            </div> <!-- /.row -->
          </div> <!-- /.panel-body -->
        </div> <!-- /.panel -->
      </div> <!-- /.col-md-10 -->
    </div> <!-- /.row -->

  </div>
  @stop

  @section('pageScripts')
  
  <script type="text/javascript">

  // Delete dashboard.
  function deleteDashboard(dashboardId) {
    $.ajax({
      type: "POST",
      url: "{{ route('dashboard.delete', 'dashboard_id') }}".replace('dashboard_id', dashboardId)
     }).done(function() {
      location.reload();
     });
  }

  // Rename dashboard.
  function renameDashboard(dashboardId, dashboardName) {
    $.ajax({
      type: "post",
      data: {'dashboard_name': dashboardName},
      url: "{{ route('dashboard.rename', 'dashboard_id') }}".replace('dashboard_id', dashboardId)
     }).done(function () {
       location.reload();
     });
  }


  $(document).ready(function () {
    // If Add New Dashboard modal is shown, focus into input field.
    $('#add-dashboard-modal').on('shown.bs.modal', function () {
      $('#name-input').focus()
    });

    // If Add New Dashboard modal is submitted, validate data and create new dashboard then reload page.
    $('#add-dashboard-form').submit(function() {
      var newDashboardName = $('#name-input').val();

      if (newDashboardName.length > 0) {
        $.ajax({
          type: "post",
          data: {'dashboard_name': newDashboardName},
          url: "{{ route('dashboard.create') }}"
         }).done(function () {
          $('#name-input-group').removeClass('has-error');
           location.reload();
         });
        return
      } else {  
        $('#name-input-group').addClass('has-error');
        event.preventDefault();
      }
      
    });

    // If Rename Dashboard modal is shown, focus into input field and change value.
    $('#rename-dashboard-modal').on('show.bs.modal', function (event) {
      var button = $(event.relatedTarget);
      console.log(button);
      var name = button.data('dashboard-name');
      var id = button.data('dashboard-id');

      console.log(name);
      console.log(id);

      $('#rename-input').val(name);
      $('#rename-input').focus();
      $('#rename-input').data('id', id);

    });

    // If Rename Dashboard modal is submitted, validate data and rename dashboard.
    $('#rename-dashboard-form').submit(function() {
      var newDashboardName = $('#rename-input').val();
      var id = $('#rename-input').data('id');

      if (newDashboardName.length > 0) {
        $('#rename-input-group').removeClass('has-error');
        renameDashboard(id, newDashboardName);
        return
      } else {  
        $('#rename-input-group').addClass('has-error');
        event.preventDefault();
      }
      
    });

  });

  </script>
  @append
