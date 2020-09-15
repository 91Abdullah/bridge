@extends('layouts.app')

@section('content')

    <div class="container-fluid">
	
		@if(session()->has('success') || session()->has('error'))
			
		<div class="alert alert-{{ session()->has('sccuess') ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
			<strong>@if(session()->has('success')) Success! @else Error! @endif</strong> @if(session()->has('success')) {{ session('success') }} @else {{ session('danger') }} @endif
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
			
		@endif

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
                            <th>Branch Name</th>
                            <th>Code</th>
                            <th colspan="2">Actions</th>
                        </thead>
                        <tbody>
                            @forelse($codes as $code)
                                <tr>
                                    <td>{{ $code->id }}</td>
                                    <td>{{ $code->branch_name }}</td>
                                    <td>{{ $code->code }}</td>
                                    <td>
                                        <a class="btn btn-dark" href="{{ route('pinCodes.edit', ['id' => $code->id]) }}">Edit</a>
                                        <form style="display:inline;" method="post" action="{{ route('pinCodes.destroy', ['id' => $code->id]) }}">
											@csrf
											@method('delete')
											<button type="submit" class="btn btn-danger">Delete</button>
										</form>
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
