@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/jszip-2.5.0/dt-1.10.18/b-1.5.4/b-colvis-1.5.4/b-html5-1.5.4/b-print-1.5.4/r-2.2.2/datatables.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.css">
@endpush

@section('content')

    <div class="container-fluid">

        @if(session()->has('failures'))
            <div class="alert alert-dismissible alert-danger">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <h4 class="alert-heading">Error!</h4>
                @foreach(session('failures') as $failure)
                    <ul>
                        <li>Row Number: {{ $failure->row() }}</li>
                        <li>Attribute: {{ $failure->attribute() }}</li>
                        <li>
                            Errors:
                            <ul>
                                @foreach($failure->errors() as $errors)
                                    <li>
                                        {{ $errors }}
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    </ul>
                @endforeach
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h3>
                    Allowed Numbers
                    <button data-toggle="modal" data-target="#bulkModal" class="btn btn-warning float-right ml-2"><i class="fas fa-upload"></i> Bulk</button>
                    <a href="{{ route('numbers.create') }}" class="btn btn-success float-right"><i class="fas fa-plus-square"></i> Add</a>
                </h3>

            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTable" class="table table-bordered">
                        <thead>
                        <th>ID</th>
                        <th>Number</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Edit</th>
                        <th>Delete</th>
                        </thead>
                        <tbody>
                        {{--@forelse($numbers as $number)--}}
                            {{--<tr>--}}
                                {{--<td>{{ $number->id }}</td>--}}
                                {{--<td>{{ $number->number }}</td>--}}
                                {{--<td>--}}
                                    {{--<h5>--}}
                                        {{--{!! $number->allowed ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>' !!}--}}
                                    {{--</h5>--}}
                                {{--</td>--}}
                                {{--<td>{{ \Carbon\Carbon::parse($number->created_at)->diffForHumans() }}</td>--}}
                                {{--<td>--}}
                                    {{--<a class="btn btn-dark" href="{{ route('numbers.edit', $number->id) }}">Edit</a>--}}
                                    {{--<form style="display: inline;" method="post" action="{{ route('numbers.destroy', $number->id) }}">--}}
                                        {{--{!! csrf_field() !!}--}}
                                        {{--{!! method_field('delete') !!}--}}
                                        {{--<button type="submit" class="btn btn-danger">Delete</button>--}}
                                    {{--</form>--}}
                                {{--</td>--}}
                            {{--</tr>--}}
                        {{--@empty--}}
                            {{--<tr>--}}
                                {{--<td colspan="5" class="text-center font-weight-bold">No records found.</td>--}}
                            {{--</tr>--}}
                        {{--@endforelse--}}
                        <tr>
                            <td colspan="6">No data found.</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                {{--{!! $numbers->links() !!}--}}
            </div>
        </div>
    </div>

    <div class="modal fade" id="bulkModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Bulk Import</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="post" action="{{ route('bulk.upload') }}" enctype="multipart/form-data">
                    {!! csrf_field() !!}
                    <div class="modal-body">
                        <div class="form-group">
                            <div class="form-group row">
                                <label for="file" class="col-sm-3 col-form-label">Upload File</label>
                                <div class="col-sm-9">
                                    <input id="file" name="file" type="file" class="form-control-file">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Submit</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script type="text/javascript" src="https://cdn.datatables.net/v/bs4/jszip-2.5.0/dt-1.10.18/b-1.5.4/b-colvis-1.5.4/b-html5-1.5.4/b-print-1.5.4/r-2.2.2/datatables.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
    <script>
        const dataUrl = "{{ route('get.numbers') }}";
        const token = "{{ csrf_token() }}";
        {{--const field = "{!! method_field('delete') !!}";--}}

        let table = $("#dataTable").DataTable({
            processing: true,
            serverSide: true,
            responsive: false,
            paging: true,
            ajax: {
                url: dataUrl,
                type: "get",
                data: {
                    _token: "{!! csrf_token() !!}"
                }
            },
            dom: 'Bfrtip',
            buttons: [
                'copy', 'excel', 'pdf'
            ],
            columns: [
                {data: 'id', name: 'id'},
                {data: 'number', name: 'number'},
                {data: 'allowed', name: 'allowed', fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
                    sData ? $(nTd).html("<span class=\"badge badge-success\">Active</span>") : $(nTd).html("<span class=\"badge badge-danger\">Inactive</span>");
                }},
                {data: 'created_at', name: 'created_at'},
                {data: null, fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
                        $(nTd).html("<a class=\"btn btn-dark\" href=\"numbers/" + sData.id + "/edit\">Edit</a>");
                    }},
                {data: null, fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
                        $(nTd).html('<form style="display: inline;" method="post" action=\"numbers/' + sData.id +'"><input type="hidden" name="_token" value="N9VUXJ5p53GVxSdyFYmIhz9WhIRpLjhJVpA8MkeB"><input type="hidden" name="_method" value="delete"><button type="submit" class="btn btn-danger">Delete</button></form>');
                    }}
            ]
        });
    </script>
@endpush


