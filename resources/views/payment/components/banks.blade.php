@if($bankList)
<div class="bank row px-3">
    <hr>
    @foreach($bankList as $bank)
    <div class="col-4">
        <a id="{{strtoupper($bank->code)}}"  role="button" class="bank-item" data-id="{{ $bank->bank_reg_id }}">
            <img class="p-2"src="{{ env('PGW_BILLING', '') . '/' . $bank->thumb_path }}" alt="{{ $bank->name }}">
        </a>
    </div>
    @endforeach
</div>
@endif
