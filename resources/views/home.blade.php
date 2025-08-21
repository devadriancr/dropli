@extends('adminlte::page')

@section('title', 'Dashboard - Producción')

@section('content')
    <div class="container-fluid">
        <div class="row p-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <livewire:production-record.paint-production-chart />
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
