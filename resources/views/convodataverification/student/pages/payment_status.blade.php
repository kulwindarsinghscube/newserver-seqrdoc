<?php

// dd(@$student->studentAckLogs->fn_hi_status == 1 || empty(@$student->studentAckLogs));
?>
@extends('convodataverification.student.pages.layout.layout')

@section('content')
   
        <div class="row">
        
            <div class="col-xs-12 col-md-6 col-md-offset-3 text-center">
                @if ($inputInfo['STATUS'] == 'TXN_SUCCESS')
                    <h2>Transaction Successful</h2>
                    <h5>Your transaction has completed successfully and details are provided below.</h5>
                @elseif($inputInfo['STATUS'] == 'TXN_FAILURE')
                    <h2>Transaction Failed</h2>
                    <h5>Your transaction has failed and details are provided below.</h5>
                @else
                    <h2>Transaction Pending</h2>
                    <h5>Your transaction is currently being processed.</h5>
                    <p>Your transaction is under review and is expected to be completed within 24 to 48 hours. We are working diligently to ensure everything is processed smoothly.</p>
                
                @endif
                <table class='table table-bordered table-hover'>
                    <tr>
                        <th>Order ID</th>
                        <td class="text-left">{{@$inputInfo['ORDERID'] }}</td>
                    </tr>
                    <tr>
                        <th>Transaction ID</th>
                        <td class="text-left">{{@$inputInfo['TXNID'] }}</td>
                    </tr>
                    <tr>
                        <th>Bank Transaction ID</th>
                        <td class="text-left">{{@$inputInfo['BANKTXNID'] }}</td>
                    </tr>
                    @if(@$inputInfo['TXNDATE'])
                    <tr>
                        <th>Date</th>
                        <td class="text-left">
                            {{ date('d-m-Y h:i A', strtotime($inputInfo['TXNDATE'])) }}
                        </td>
                    </tr> 
                    @endif
                    <tr>
                        <th>Mode</th>
                        <td class="text-left">{{@$inputInfo['PAYMENTMODE'] }}</td>
                    </tr>
                    <tr>
                        <th>Amount</th>
                        <td class="text-left"><i class="fa fa-rupee"></i> {{@$inputInfo['TXNAMOUNT'] }}</td>
                    </tr>
                </table>
                <p class="text-center">
                    <a href="{{url('/convo_student/dashboard')}}"   class="btn btn-theme" style="color:#fff">
                        @if ($inputInfo['STATUS'] == 'TXN_SUCCESS')
                            View Details To Approve PDF
                        @else
                            View Details
                        @endif
                    </a>
                    <a onclick="window.print();" class="btn btn-theme" style="color:#fff">
                        Print
                    </a>
                </p>
            </div>
        </div>

        @section('script')
            <script></script>
        @endsection
    
@endsection
