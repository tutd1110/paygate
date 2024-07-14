@extends('layouts.payment')

@section('title', 'Phương thức thanh toán')

@section('content')
    @include('payment.components.transfer', ['contact' => $contact])
    <div class="row">
        <input id="merchant_code" value="{{!empty($_GET['merchant_code']) ? strtoupper($_GET['merchant_code']) : ''}}" hidden>
        <div class="col-lg-6 payment-methods_main" style="position: relative">
            <div class="title-choose-payment">
                <h2>Lựa chọn phương thức để tiếp tục thanh toán</h2>
            </div>
            @if($order->coupon_code)
            <div class="img-discount"><img src="/assets/payment/img/Frame 828154 (3).png" alt="">
                <p>Mã giảm giá 500k cho đơn hàng <br>đầu tiên thanh toán trực tuyến</p>
            </div>
            @endif
            <div class="__list-payment-methods pb-5">
                <div class="merchant-group row">
                @foreach($merchantList as $key => $merchant)
                            @if($merchant->type == 'transfer')
                                @if(count($bankList))
                                    @if(count($bankList) > 1)
                                        <div class="col-xl-4 col-6">
                                            <a id="{{strtoupper($merchant->code)}}" class="bank-transfer" data-merchant-id="{{ $merchant->merchant_reg_id }}" data-bs-toggle="collapse" href="#bank-list-{{ $key }}" role="button" aria-expanded="false" aria-controls="bank-list-{{ $key }}">
                                                <img src="{{ env('PGW_BILLING', '') . '/' . $merchant->thumb_path }}" alt="{{ $merchant->name }}">
                                            </a>
                                        </div>
                                    @else
                                        <div id="{{strtoupper($merchant->code)}}"  class="col-xl-4 col-6 merchant-item" data-merchant-id="{{ $merchant->merchant_reg_id }}" data-bank-id="{{ $bankList[0]->bank_reg_id }}">
                                            <a role="button">
                                                <img src="{{ env('PGW_BILLING', '') . '/' . $merchant->thumb_path }}" alt="{{ $merchant->name }}">
                                            </a>
                                        </div>
                                    @endif
                                @endif
                            @else
                                <div id="{{strtoupper($merchant->code)}}"  class="col-xl-4 col-6 merchant-item" data-merchant-id="{{ $merchant->merchant_reg_id }}" data-bank-id="0">
                                    <a role="button">
                                        <img src="{{ env('PGW_BILLING', '') . '/' . $merchant->thumb_path }}" alt="{{ $merchant->name }}">
                                    </a>
                                </div>
                            @endif
                    <div class="collapse" id="bank-list-{{ $key }}">
                        @include('payment.components.banks', ['bankList' => $bankList])
                    </div>
                @endforeach
                </div>
                    <hr>
                    <div class="row flex" style="position: absolute;bottom: 0px;right: 0px">
                        <div class="col-11">
                            <p  id="link-cancel-order" data-value="{{$_GET['bill']}}">Huỷ đơn hàng
                            </p>
                        </div>
                        <div class="col-1"></div>
                    </div>
            </div>

        </div>
        <div class="col-lg-2"></div>
        <div class="col-lg-4 invoice">
            <input type="hidden" id="order-id" value="{{ $order->id }}">
            <input type="hidden" id="vpc_MerchTxnRef" value="">
            <div class="content-billing px-4 py-5">
                <div class="title-content-billing">
                    <h2>Hoá đơn thanh toán</h2>
                </div>
                <div class="title-info"><img src="/assets/payment/img/user.png" alt=""><span>Thông tin người mua </span>
                </div>
                <div class="info-buyer">
                    <p>Họ và tên : <span>{{ $contact->full_name }}</span></p>
                    <p>Số điện thoại : {{ $contact->phone }}</p>
                </div>
                <hr>
                <div class="title-info"><img src="/assets/payment/img/shopping-cart.png"
                                             alt=""><span>Thông tin đơn hàng</span>
                </div>
                <div class="info-order">
                    <p>Sản phẩm đang có : <span style="color: #1D96EE; font-weight: 700;">{{ number_format(count($orderItems),0, ',', '.') }}</span>
                    </p>
                    <ul>
                        @foreach($orderItems as $item)
                            <li class="row" >
                                <div class="col-8" style="word-wrap:break-word">{!! $item->product_name ?? "" !!} </div>
                                <div class="col-4" style="text-align: right">{{($item->quantity > 1) ? number_format(intval($item->quantity),0, ',', '.').' x ' : ''}}{{number_format(intval($item->price),0, ',', '.')}} đ</div>
                            </li>
                        @endforeach
                    </ul>
                    <hr>
                </div>
                <div class="title-info"><img src="/assets/payment/img/receipt.png" alt=""><span>Thông tin thanh toán</span>
                </div>
                <div class="info-pay">
                    @if(!empty($order->coupon_code))
                    <p>Mã giảm giá đã áp dụng :
                        <span>{{ $order->coupon_code?:'Không' }}</span></p>
                    @endif
                    <div>
                        <div class="payment flex">
                            <div class="payment-study">Học phí</div>
                            <div class="total-price">{{ number_format(($order->amount + $order->discount),0, ',', '.')}} đ</div>
                        </div>
                        <div class="discount flex">
                            <div class="__discount">Giảm giá</div>
                            <div class="total-discount" style="color: #499D4E;">-{{ number_format($order->discount,0, ',', '.') }}đ
                            </div>
                        </div>
                        @if(!empty($detailDiscount))
                            @foreach( $detailDiscount as $key => $detailDiscount)
                                <div class="payment flex">
                                    <div class="payment-study" style="font-size: 12px;margin-left: 1vw;">{{$key}}</div>
                                    <div class="total-price" style="font-size: 12px;color: #499D4E;">{{ number_format(intval($detailDiscount),0, ',', '.')}} đ</div>
                                </div>
                            @endforeach

                        @endif
                    </div>
                    <hr>
                    <div class="last-price flex">
                        <div class="last-total-price">Tổng cộng</div>
                        <div class="__last-total-price">{{ number_format($order->amount,0, ',', '.') }} đ</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="loadMerchant" tabindex="-1" role="dialog" aria-labelledby="loadMerchant">
        <div class="modal-dialog modal-dialog-centered modal-sm" style="width: 100px">
            <div class="modal-content">
                <div class="modal-body text-center" >
                    <div class="spinner-border text-primary" role="status" style="width: 3rem;height: 3rem"><span class="visually-hidden">Loading...</span></div>
                </div>
            </div>
        </div>
    </div>
@stop

