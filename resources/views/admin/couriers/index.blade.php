@extends('adminlte::page')

@section('title', 'Kuriri')

@section('content_header')
<h3>Kuriri</h3>
@stop

@section('content')
<div class="row">
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Svi kuriri</h3>
      </div>
      <div class="card-body">
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th></th>
              <th>Naziv</th>
              <th>Država</th>
            </tr>
          </thead>
          <tbody>
            @foreach($couriers as $courier)
            <tr>
              <td>{{ $courier->id }}</td>
              <td>{{ $courier->name }}</td>
              <td>{{ $courier->country->name }} ({{ $courier->country->short }})</td>
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
        <h3 class="card-title">Dodaj kurira</h3>
      </div>
      <div class="card-body">
        <form action="/admin/couriers/store" method="post">
          @csrf
          <div class="form-group">
            <label>Naziv</label>
            <input class="form-control" type="text" name="name" value="{{ old('name') }}">
          </div>
          <div class="form-group">
            <label>Država</label>
            <select class="custom-select" name="country_id">
                @foreach ($countries as $country)
                    <option value="{{ $country->id }}">{{ $country->id }} - {{ $country->name }} ({{ $country->short }})</option>    
                @endforeach
            </select>
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