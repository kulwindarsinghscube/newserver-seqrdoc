
    
<div class="panel panel-info">
    <div class="panel-heading">
        <b>Student Information</b>
    </div>
    <div class="panel-body">
        <?php
            $student_name = $student_compact['student_name'];
            $qr_key = $student_compact['key'];
        ?>
        @foreach($paymentList as $key => $value)
            @if($value['pg_name'] == 'Paytm')
                <?php $png = 'paytm_btn.png'; ?>
                <?php $url = route('payment.gateway.paytm'); ?>
                <?php $btn_name = "PayTm"; ?>
                <?php $form_name = "paytmForm"; ?> 
           
            @endif
            @if($value['pg_name'] == 'PayUmoney')
                <?php $png = 'payu_btn.png'; ?>
                <?php $url = url('webapp/payuGiz/'.$qr_key.'/'.$value['amount'].'/'.$student_name); ?>
                <?php $btn_name = 'Pay U Money'; ?>
                <?php $form_name = "payUmoneyForm"; ?>
            @endif
           <!--  @if($value['pg_name'] == 'PayuGiz')
                <?php $png = 'payu_btn.png'; ?>
                <?php $url = "payment-gateway/payuGiz"; ?>
                <?php $btn_name = "Pay U Giz"; ?>"
                <?php $form_name = "payUGizForm"; ?>
            @endif -->

            <div class="row">
                <div class="col-xs-12 text-center">
                    <span class="alert alert-success" style="border-left: 4px solid;border-right: 4px solid;">Make payment of <b>{{ $value['amount'] }} <i class="fa fa-rupee"></i></b> to view hidden datas
                    </span>
                    <a href="{{$url}}" class="payment-url">
                        <img src="<?= \Config::get('constant.payment_image') ?>/{{$png}}" style="display:inline">
                    </a>
                    <!-- <form role="form" action="{{$url}}" method="get" name="{{$form_name}}" class="{{$form_name}}"> -->
                        <!-- <input type="hidden" name="_token" value="{{ csrf_token() }}"> -->
                        <!-- <input type="text" name="student_name" value="{{$student_name}}">
                        <input type="hidden" name="key" value="{{$qr_key}}">
                        <input type="hidden" name="amount" value="{{$value['amount']}}"> -->
                        <!-- <input type="submit" value="{{$btn_name}}" id="{{$form_name}}"> -->
                    <!-- </form> -->
                </div>
            </div>
        @endforeach
    </div>
</div>


