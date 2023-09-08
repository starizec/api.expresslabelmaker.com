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
                    <input type="hidden" value="{{ $company->id }}" name="company_id">
                    <div class="form-group">
                        <label>Naziv</label>
                        <input type="text" name="company_name" value="{{ $company->company_name }}" class="form-control"
                            placeholder="Naziv">
                    </div>

            </div>
            <div class="card-footer">
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Izmjeni</button>
                </div>
            </div>
            </form>
        </div>
    </div>
</div>

@endsection