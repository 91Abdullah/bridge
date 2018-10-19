@extends('layouts.app')

@section('content')

    <div class="container-fluid">

        <div class="row">
            <div class="col-lg-6 offset-3">
                <div class="card">
                    <div class="card-header">
                        <h3>Create PIN Code</h3>
                    </div>
                    <div class="card-body">

                        <form method="post" class="" action="{{ route('pinCodes.store') }}">
                            {!! csrf_field() !!}
                            <div class="form-group">
                                <label for="branch_name">Branch Name</label>
                                <input name="branch_name" id="branch_name" type="text" class="form-control {{ $errors->has('branch_name') ? 'is-invalid' : '' }}" required>
                                <small class="form-text text-muted">Enter branch name.</small>
                                <div class="invalid-feedback">
                                    {{ $errors->first('branch_name') }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="code">Code</label>
                                <input name="code" id="code" type="text" class="form-control {{ $errors->has('code') ? 'is-invalid' : '' }}" required>
                                <small class="form-text text-muted">Enter PIN code of max 10 digits and min 4 digits.</small>
                                <div class="invalid-feedback">
                                    {{ $errors->first('code') }}
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </form>

                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection
