@extends('layouts.zns')

@section('title', 'Xác thực khách hàng')

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
                {{ session('success') }}  Hãy bấm vào <a href="https://assessment.icanconnect.vn/">đây</a> để chuyển hướng sang trang ICANCONNECT
            </div>
        </div>
    @endif
@stop
