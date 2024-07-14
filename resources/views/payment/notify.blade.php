@extends('layouts.payment')

@section('title', 'Phương thức thanh toán')

@section('content')
    @if (session('warning'))
        <div class="alert alert-warning fs-4 p-4 text-center w-100" role="alert">
            <div>
                {{ session('warning') }}
            </div>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger fs-4 p-4 text-center w-100" role="alert">
            <div>
                {{ session('error') }}
            </div>
        </div>
    @endif
    @if (session('success'))
        <div class="alert alert-success fs-4 p-4 text-center w-100" role="alert">
            <div>
                {{ session('success') }}
            </div>
        </div>
    @endif
@stop
