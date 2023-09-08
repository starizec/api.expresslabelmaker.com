@extends('adminlte::page')

@section('content')
<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
              <h3 class="card-title">Korisnici</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
              <table class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>Naziv</th>
                  <th>OIB</th>
                  <th>Poštanski broj</th>
                  <th>Mjesto</th>
                  <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($companies as $company)
                    <tr>
                    <td><a href="/admin/tvrtke/show/{{ $company->id }}">{{ $company->company_name }}</a></td>
                    <td>{{ $company->vat_number }}</td>
                    <td>{{ $company->postal_code }}</td>
                    <td>{{ $company->town }}</td>
                    <td><a href="/admin/tvrtke/edit/{{ $company->id }}"><i class="fa fa-edit"></i></a></td>
                    </tr>
                @endforeach
                </tbody>
              </table>
            </div>
            <!-- /.card-body -->
          </div>
          {{ $companies->links() }}
    </div>
</div>
@endsection