<?php

// dd(@$student->studentAckLogs->fn_hi_status == 1 || empty(@$student->studentAckLogs));
?>
@extends('convodataverification.student.pages.layout.layout')

@section('content')
    @if ($inputInfo['STATUS'] == 'TXN_SUCCESS')
        <div class="row">
            <div class="col-xs-12 col-md-6 col-md-offset-3 text-center">
                <h2>Transaction Successful</h2>
                <h5>Your transaction has completed successfully and details are provided below.</h5>
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
                    <tr>
                        <th>Date</th>
                        <td class="text-left">
                            {{ date('d-m-Y h:i A', strtotime($inputInfo['TXNDATE'])) }}
                        </td>
                    </tr> 
                    
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
                        View Details To Approve PDF
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
    @else
        <div class="row">
            <div class="col-xs-12 col-md-6 col-md-offset-3 text-center">
                <h2>Transaction Failed</h2>
                <h5>Your transaction has failed and details are provided below.</h5>
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
                        <th>Date</th>
                        <td class="text-left">{{ date('d-m-Y') }}</td>
                    </tr>
                    <tr>
                        <th>Mode</th>
                        <td class="text-left">{{@$inputInfo['PAYMENTMODE'] }}</td>
                    </tr>
                    <tr>
                        <th>Amount</th>
                        <td class="text-left"><i class="fa fa-rupee"></i>{{@$inputInfo['TXNAMOUNT'] }}</td>
                    </tr>
                </table>
                <p class="text-center">You can close this tab and return to previous page to try again.</p>
                <p class="text-center">
                    <a href="{{url('/convo_student/dashboard')}}"   class="btn btn-theme" style="color:#fff">
                        View Details
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
    @endif
@endsection
