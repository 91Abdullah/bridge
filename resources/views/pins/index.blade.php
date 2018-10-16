@extends('layouts.app')

@section('content')

    <div class="container-fluid">

        <div class="card">
            <div class="card-header">
                <h3>
                    System PIN Codes
                    <a href="{{ route('pinCodes.create') }}" class="btn btn-success float-right"><i class="fas fa-plus-square"></i> Create</a>
                </h3>

            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <th>Id</th>
                            <th>Code</th>
                            <th colspan="2">Actions</th>
                        </thead>
                        <tbody>
                            @forelse($codes as $code)
                                <tr>
                                    <td>{{ $code->id }}</td>
                                    <td>{{ $code->code }}</td>
                                    <td>
                                        <a class="btn btn-dark" href="{{ route('pinCodes.edit', ['id' => $code->id]) }}">Edit</a>
                                        <a href="{{ route('pinCodes.destroy', ['id' => $code->id]) }}" class="btn btn-danger">Delete</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center font-weight-bold">No records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

@endsection
