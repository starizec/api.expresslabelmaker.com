@extends('adminlte::page')

@section('title', 'Države')

@section('content_header')
<h3>Države</h3>
@stop

@section('content')
<div class="row">
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Sve države</h3>
      </div>
      <div class="card-body">
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th></th>
              <th>Naziv</th>
              <th>Kratki naziv</th>
            </tr>
          </thead>
          <tbody>
            @foreach($countries as $country)
            <tr>
              <td>{{ $country->id }}</td>
              <td>{{ $country->name }}</td>
              <td>{{ $country->short }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Dodaj državu</h3>
      </div>
      <div class="card-body">
        <form action="/admin/countries/store" method="post">
          @csrf
          <div class="form-group">
            <label>Naziv</label>
            <input class="form-control" type="text" name="name" value="{{ old('name') }}">
          </div>
          <div class="form-group">
            <label>Kratki naziv</label>
            <input class="form-control" type="text" name="short" value="{{ old('short') }}">
          </div>
      </div>
      <div class="card-footer">
        <input class="btn btn-primary" type="submit" value="Spremi">
        </form>
      </div>
    </div>
  </div>
</div>
@endsection