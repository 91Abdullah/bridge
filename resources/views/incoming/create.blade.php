@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/bootstrap-switch.min.css') }}">
@endpush

@section('content')

    <div class="container-fluid">

        <div class="row">
            <div class="col-lg-6 offset-3">
                <div class="card">
                    <div class="card-header">
                        <h3>Add Incoming Number</h3>
                    </div>
                    <div class="card-body">

                        <form method="post" class="" action="{{ route('numbers.store') }}">
                            {!! csrf_field() !!}
                            <div class="form-group">
                                <label for="number">Number</label>
                                <input value="{{ old('number') }}" name="number" id="number" type="text" class="form-control {{ $errors->has('number') ? 'is-invalid' : '' }}" required>
                                <small class="form-text text-muted">Add number to authenticate</small>
                                <div class="invalid-feedback">
                                    {{ $errors->first('number') }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="allowed">Status</label>
                                <div>
                                    <input value="{{ old('allowed') }}" name="allowed" id="allowed" type="checkbox" class="{{ $errors->has('allowed') ? 'is-invalid' : '' }}" checked>
                                </div>
                                <small class="form-text text-muted">Activate or Deactivate</small>
                                <div class="invalid-feedback">
                                    {{ $errors->first('allowed') }}
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

@push('scripts')
    <script src="{{ asset('js/bootstrap-switch.min.js') }}"></script>
    <script type="text/javascript">
        $("[name='allowed']").bootstrapSwitch();
    </script>

@endpush
