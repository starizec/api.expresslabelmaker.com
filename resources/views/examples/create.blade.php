@extends('adminlte::page')

@section('content')
<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">#Card naziv#</h3>
            </div>
            <div class="card-body">

                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form action="#Routa#" method="post">
                    @csrf
                    <div class="form-group">
                        <label>#Label naziv#</label>
                        <input class="form-control" type="text" name="#polje_nazov#" value="{{ old('company_name') }}">
                    </div>

                    <div class="card-footer">
                        <input class="btn btn-primary" type="submit" value="Spremi">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection