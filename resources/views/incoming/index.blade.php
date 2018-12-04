@extends('layouts.app')

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
                    <table class="table table-bordered">
                        <thead>
                        <th>ID</th>
                        <th>Number</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th colspan="2">Actions</th>
                        </thead>
                        <tbody>
                        @forelse($numbers as $number)
                            <tr>
                                <td>{{ $number->id }}</td>
                                <td>{{ $number->number }}</td>
                                <td>
                                    <h5>
                                        {!! $number->allowed ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>' !!}
                                    </h5>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($number->created_at)->diffForHumans() }}</td>
                                <td>
                                    <a class="btn btn-dark" href="{{ route('numbers.edit', $number->id) }}">Edit</a>
                                    <form style="display: inline;" method="post" action="{{ route('numbers.destroy', $number->id) }}">
                                        {!! csrf_field() !!}
                                        {!! method_field('delete') !!}
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center font-weight-bold">No records found.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                {!! $numbers->links() !!}
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


